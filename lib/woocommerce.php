<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2020 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoWcsdtWooCommerce' ) ) {

	class WpssoWcsdtWooCommerce {

		private $p;

		public function __construct( &$plugin ) {

			static $do_once = null;

			if ( true === $do_once ) {

				return;	// Stop here.
			}

			$do_once = true;

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( 'yes' === get_option( 'wcsdt_show_handling_times', $default = 'yes' ) ) {

				add_action( 'woocommerce_after_shipping_rate', array( $this, 'show_handling_time_label' ), 1000, 2 );
			}

			if ( 'yes' === get_option( 'wcsdt_show_transit_times', $default = 'yes' ) ) {

				add_filter( 'woocommerce_cart_shipping_method_full_label', array( $this, 'add_transit_time_label' ), 1000, 2 );
			}

			if ( is_admin() ) {

				add_filter( 'plugin_action_links', array( $this, 'add_plugin_action_links' ), 200, 4 );
				add_filter( 'woocommerce_shipping_settings', array( $this, 'add_options' ), 10, 1 );
				add_action( 'woocommerce_settings_wcsdt_options_end', array( $this, 'show_options_stylesheet' ), 10 );
				add_action( 'woocommerce_settings_wcsdt_options_end', array( $this, 'show_handling_time' ), 20 );
				add_action( 'woocommerce_settings_wcsdt_options_end', array( $this, 'show_transit_time' ), 30 );
				add_action( 'woocommerce_settings_save_shipping', array( $this, 'save_options' ) );
			}
		}

		public function add_plugin_action_links( $action_links, $plugin_base, $plugin_data, $context ) {

			if ( WPSSOWCSDT_PLUGINBASE === $plugin_base ) {

				$admin_url    = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=options' );
				$label_transl = esc_html__( 'WooCommerce Shipping Options', 'wpsso-wc-shipping-delivery-time' );

				$action_links[] = '<a href="' . $admin_url . '">' . $label_transl . '</a>';
			}

			return $action_links;
		}

		public function show_handling_time_label( $method_obj, $pkg_index ) {

			static $packages         = null;
			static $handling_times   = null;
			static $method_count     = 0;

			if ( null === $packages ) {

				$packages       = WC()->shipping()->get_packages();
				$handling_times = get_option( 'wcsdt_handling_time', array() );
			}

			if ( empty( $packages[ $pkg_index ][ 'rates' ] ) ) {	// Shipping methods.

				return;
			}

			$package       = $packages[ $pkg_index ];
			$total_methods = count( $package[ 'rates' ] );
			$method_count++;

			if ( $method_count !== $total_methods ) {	// Check for last shipping method.

				return;
			}

			$pkg_min_val   = '';
			$pkg_max_val   = '';
			$pkg_unit_code = '';

			/**
			 * Determine the $pkg_min_val and $pkg_max_val values.
			 */
			foreach ( $package[ 'contents' ] as $item_id => $values ) {

					$product           = $values[ 'data' ];
					$shipping_class_id = $product->get_shipping_class_id();	// 0 or a selected product "Shipping class".

					$opt_key_pre  = 'c' . $shipping_class_id;
					$opt_key_min  = $opt_key_pre . '_minimum';
					$opt_key_max  = $opt_key_pre . '_maximum';
					$opt_key_unit = $opt_key_pre . '_unit_code';

					$min_val   = isset( $handling_times[ $opt_key_min ] ) ? $handling_times[ $opt_key_min ] : '';
					$max_val   = isset( $handling_times[ $opt_key_max ] ) ? $handling_times[ $opt_key_max ] : '';
					$unit_code = isset( $handling_times[ $opt_key_unit ] ) ? $handling_times[ $opt_key_unit ] : '';

					list( $pkg_min_val, $pkg_max_val, $pkg_unit_code ) = $this->get_package_times( $min_val, $max_val, $unit_code );
			}

			if ( empty( $pkg_min_val ) && empty( $pkg_max_val ) ) {	// Nothing to show.

				return;
			}

			$times_label = $this->get_times_label( $pkg_min_val, $pkg_max_val, $pkg_unit_code );

			// tranlators: Shipping handling and packaging time under the shipping methods.
			$handling_label = '<label><small>' . __( 'Add %s for handling &amp; packaging.', 'wpsso-wc-shipping-delivery-time' ) . '</small></label>';
			$handling_label = apply_filters( 'wpsso_wcsdt_shipping_handling_time_label', $handling_label, $times_label );
			$handling_label = sprintf( $handling_label, $times_label );

			echo $handling_label;
		}

		/**
		 * $method is a WC_Shipping_Rate object.
		 */
		public function add_transit_time_label( $method_label, $method_obj ) {

			if ( empty( $method_obj->instance_id ) ) {

				return $method_label;
			}

			static $transit_times = null;

			if ( null === $transit_times ) {

				$transit_times = get_option( 'wcsdt_transit_time', array() );
			}

			$method_inst_id = $method_obj->get_instance_id();

			$opt_key_pre  = 'm' . $method_inst_id;
			$opt_key_min  = $opt_key_pre . '_minimum';
			$opt_key_max  = $opt_key_pre . '_maximum';
			$opt_key_unit = $opt_key_pre . '_unit_code';

			$min_val   = isset( $transit_times[ $opt_key_min ] ) ? $transit_times[ $opt_key_min ] : '';
			$max_val   = isset( $transit_times[ $opt_key_max ] ) ? $transit_times[ $opt_key_max ] : '';
			$unit_code = isset( $transit_times[ $opt_key_unit ] ) ? $transit_times[ $opt_key_unit ] : '';

			if ( empty( $min_val ) && empty( $max_val ) ) {	// Nothing to do.

				return $method_label;
			}

			$times_label = $this->get_times_label( $min_val, $max_val, $unit_code );

			// translators: Shipping transit time in the shipping method label.
			$transit_label = ' ' . __( '(%s)', 'wpsso-wc-shipping-delivery-time' );
			$transit_label = apply_filters( 'wpsso_wcsdt_shipping_transit_time_label', $transit_label, $times_label );
			$transit_label = sprintf( $transit_label, $times_label );

			return $method_label . $transit_label;
		}

		public function add_options( $settings ) {

			/**
			 * See woocommerce/includes/admin/settings/class-wc-settings-shipping.php for examples.
			 */
			$new_settings = array(
				array(
					'title' => __( 'Shipping delivery times', 'wpsso-wc-shipping-delivery-time' ),
					'id'    => 'wcsdt_title',
					'type'  => 'title',
				),
				array(
					'title'         => __( 'Show delivery estimates', 'wpsso-wc-shipping-delivery-time' ),
					'desc'          => __( 'Show shipping handling and packaging time under the shipping methods.', 'wpsso-wc-shipping-delivery-time' ),
					'id'            => 'wcsdt_show_handling_times',
					'type'          => 'checkbox',
					'default'       => 'yes',
					'checkboxgroup' => 'start',
				),
				array(
					'desc'          => __( 'Show shipping transit time in the shipping method label.', 'wpsso-wc-shipping-delivery-time' ),
					'id'            => 'wcsdt_show_transit_times',
					'type'          => 'checkbox',
					'default'       => 'yes',
					'checkboxgroup' => 'end',
				),
				array(
					'id'   => 'wcsdt_options',
					'type' => 'sectionend',
				),
			);

			return array_merge( $settings, $new_settings );
		}

		public function show_options_stylesheet() {

			?><style type="text/css">
				.woocommerce table .shipping-class,
				.woocommerce table .shipping-method {
					width:23%;
				}
				.woocommerce table .class-description,
				.woocommerce table .shipping-rate {
					width:28%;
				}
				.woocommerce table .minimum-time,
				.woocommerce table .maximum-time {
					width:17%;
				}
				.woocommerce table .unit-of-time {
					width:15%;
				}
				.woocommerce table.form-table table td.minimum-time input[type="number"],
				.woocommerce table.form-table table td.maximum-time input[type="number"] {
					width:8em;
				}
				.woocommerce table.form-table table td.unit-of-time select {
					width:6em;
				}
			</style><?php
		}

		public function show_handling_time() {

			echo '<tr valign="top">' . "\n";
			echo '<th scope="row" class="titledesc"><label>' . esc_html__( 'Shipping handling times', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The typical delay between the receipt of an order and the goods leaving the warehouse.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</label></th>' . "\n";
			echo '<td class="forminp">' . "\n";

			$this->show_handling_time_table();

			echo '</td><!-- .forminp -->' . "\n";
			echo '</tr>' . "\n";
		}

		private function show_handling_time_table() {

			$handling_times       = get_option( 'wcsdt_handling_time', array() );
			$shipping_classes     = WC()->shipping()->get_shipping_classes();
			$classes_admin_url    = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' );
			$classes_label_transl = '<a href="' . $classes_admin_url . '">' . esc_html__( 'Shipping classes', 'wpsso-wc-shipping-delivery-time' ) . '</a>';

			echo '<table class="wc_shipping widefat wp-list-table" cellspacing="0">' . "\n";

			echo '<thead>' . "\n";
			echo '<tr style="background:#e9e9e9;">' . "\n";
			echo '<th colspan="5" style="text-align:center; border:1px solid #e1e1e1;">' . $classes_label_transl . '</th>' . "\n";
			echo '</tr>' . "\n";

			echo '<tr>' . "\n";

			echo '<th class="shipping-class">' . esc_html__( 'Shipping class', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";

			echo '<th class="class-description">' . esc_html__( 'Class description', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";

			echo '<th class="minimum-time">' . esc_html__( 'Minimum time', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The estimated minimum handling and packaging time. Can be left blank.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</th>' . "\n";

			echo '<th class="maximum-time">' . esc_html__( 'Maximum time', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The estimated maximum handling and packaging time. Can be left blank.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</th>' . "\n";

			echo '<th class="unit-of-time">' . esc_html__( 'Unit of time', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";

			echo '</tr>' . "\n";
			echo '</thead>' . "\n";
			echo '<tbody>' . "\n";

			foreach ( $shipping_classes as $shipping_class_obj ) {

				$shipping_class_id   = $shipping_class_obj->term_id;
				$shipping_class_name = $shipping_class_obj->name;
				$shipping_class_desc = $shipping_class_obj->description;

				$this->show_handling_time_table_rows( $handling_times, $shipping_class_id, $shipping_class_name, $shipping_class_desc );

			}

			$shipping_class_id   = 0;
			// translators: Please ignore - translation uses a different text domain.
			$shipping_class_name = __( 'No shipping class', 'woocommerce' );
			$shipping_class_desc = __( 'Products without a shipping class', 'wpsso-wc-shipping-delivery-time' ) ;

			$this->show_handling_time_table_rows( $handling_times, $shipping_class_id, $shipping_class_name, $shipping_class_desc );

			echo '</tbody>' . "\n";
			echo '</table><!-- .wc_shipping.widefat.wp-list-table -->' . "\n";
		}

		private function show_handling_time_table_rows( $handling_times, $shipping_class_id, $shipping_class_name, $shipping_class_desc ) {

			$opt_key_pre  = 'c' . $shipping_class_id;
			$opt_key_min  = $opt_key_pre . '_minimum';
			$opt_key_max  = $opt_key_pre . '_maximum';
			$opt_key_unit = $opt_key_pre . '_unit_code';

			$min_val   = isset( $handling_times[ $opt_key_min ] ) ? $handling_times[ $opt_key_min ] : '';
			$max_val   = isset( $handling_times[ $opt_key_max ] ) ? $handling_times[ $opt_key_max ] : '';
			$unit_code = isset( $handling_times[ $opt_key_unit ] ) ? $handling_times[ $opt_key_unit ] : 'DAY';

			echo '<tr>' . "\n"; 

			echo '<td class="shipping-class">' . $shipping_class_name . '</td>' . "\n";

			echo '<td class="class-description">' . $shipping_class_desc . '</td>' . "\n";

			echo '<td class="minimum-time">';
			echo '<input type="number" step="0.5" min="0" name="wcsdt_handling_time[' . $opt_key_min . ']" value="' . $min_val . '"/>';
			echo '</td>' . "\n";

			echo '<td class="maximum-time">';
			echo '<input type="number" step="0.5" min="0" name="wcsdt_handling_time[' . $opt_key_max . ']" value="' . $max_val . '"/>';
			echo '</td>' . "\n";

			echo '<td class="unit-of-time">' . "\n";
			$this->show_unit_select( 'wcsdt_handling_time', $opt_key_unit, $unit_code );
			echo '</td>' . "\n";

			echo '</tr>' . "\n"; 
		}

		public function show_transit_time() {

			echo '<tr valign="top">' . "\n";
			echo '<th scope="row" class="titledesc"><label>' . esc_html__( 'Shipping transit times', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The typical delay between the goods leaving the warehouse and reaching the customer.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</label></th>' . "\n";
			echo '<td class="forminp">' . "\n";

			$this->show_transit_time_table();

			echo '</td><!-- .forminp -->' . "\n";
			echo '</tr>' . "\n";
		}

		private function show_transit_time_table() {

			echo '<table class="wc_shipping widefat wp-list-table" cellspacing="0">' . "\n";

			$transit_times  = get_option( 'wcsdt_transit_time', array() );
			$shipping_zones = WC_Shipping_Zones::get_zones( $context = 'admin' );	// Since WC v2.6.0.

			foreach ( $shipping_zones as $zone_id => $zone ) {

				$zone_obj          = WC_Shipping_Zones::get_zone( $zone_id );	// Since WC v2.6.0.
				$zone_methods      = $zone_obj->get_shipping_methods( $enabled_only = true, $context = 'admin' );
				$zone_name         = $zone_obj->get_zone_name( $context = 'admin' );
				$zone_admin_url    = admin_url( 'admin.php?page=wc-settings&tab=shipping&zone_id=' . $zone_id );
				$zone_label_transl = '<a href="' . $zone_admin_url . '">' . esc_html( sprintf( __( '%s shipping zone',
					'wpsso-wc-shipping-delivery-time' ), $zone_name ) ) . '</a>';

				$this->show_transit_time_table_rows( $transit_times, $zone_label_transl, $zone_id, $zone_methods );
			}

			/**
			 * Locations not covered by your other zones.
			 */
			$world_zone_id      = 0;
			$world_zone_obj     = WC_Shipping_Zones::get_zone( $world_zone_id );	// Locations not covered by your other zones.
			$world_zone_methods = $world_zone_obj->get_shipping_methods();

			// translators: Please ignore - translation uses a different text domain.
			$zone_name         = $world_zone_obj->get_zone_name( $context = 'admin' );
			$zone_admin_url    = admin_url( 'admin.php?page=wc-settings&tab=shipping&zone_id=' . $world_zone_id );
			$zone_label_transl = '<a href="' . $zone_admin_url . '">' . esc_html( $zone_name ) . '</a> ';

			$this->show_transit_time_table_rows( $transit_times, $zone_label_transl, $world_zone_id, $world_zone_methods );

			echo '</table><!-- .wc_shipping.widefat.wp-list-table -->' . "\n";
		}

		private function show_transit_time_table_rows( $transit_times, $zone_label_transl, $zone_id, $shipping_methods ) {

			echo '<thead>' . "\n";
			echo '<tr style="background:#e9e9e9;">' . "\n";
			echo '<th colspan="5" style="text-align:center; border:1px solid #e1e1e1;">' . $zone_label_transl . '</th>' . "\n";
			echo '</tr>' . "\n";

			echo '<tr>' . "\n";

			echo '<th class="shipping-method">' . esc_html__( 'Shipping method', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";

			echo '<th class="shipping-rate">' . esc_html__( 'Shipping rate', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";

			echo '<th class="minimum-time">' . esc_html__( 'Minimum time', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The estimated minimum transit time. Can be left blank.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</th>' . "\n";

			echo '<th class="maximum-time">' . esc_html__( 'Maximum time', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The estimated maximum transit time. Can be left blank.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</th>' . "\n";

			echo '<th class="unit-of-time">' . esc_html__( 'Unit of time', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";

			echo '</tr>' . "\n";
			echo '</thead>' . "\n";
			echo '<tbody>' . "\n";

			if ( empty( $shipping_methods ) ) {

				echo '<tr>';
				echo '<td colspan="5" style="text-align:center;">' . 
					// translators: Please ignore - translation uses a different text domain.
					__( 'No shipping methods offered to this zone.', 'woocommerce' ) . '</td>' . "\n";
				echo '</tr>' . "\n";

			} else {

				foreach ( $shipping_methods as $method_inst_id => $method_obj ) {
	
					$method_url     = admin_url( 'admin.php?page=wc-settings&tab=shipping&instance_id=' . $method_inst_id );
					$method_rate_id = $method_obj->get_rate_id();
					$method_name    = $method_obj->get_title();
					$method_data    = $method_obj->instance_settings;
	
					$rate_ids  = explode( ':', $method_rate_id );
					$rate_type = reset( $rate_ids );
	
					if ( 'local_pickup' === $rate_type ) {	// Pickup is not a shipping method.
	
						continue;
					}
	
					$opt_key_pre  = 'm' . $method_inst_id;
					$opt_key_min  = $opt_key_pre . '_minimum';
					$opt_key_max  = $opt_key_pre . '_maximum';
					$opt_key_unit = $opt_key_pre . '_unit_code';
	
					$min_val   = isset( $transit_times[ $opt_key_min ] ) ? $transit_times[ $opt_key_min ] : '';
					$max_val   = isset( $transit_times[ $opt_key_max ] ) ? $transit_times[ $opt_key_max ] : '';
					$unit_code = isset( $transit_times[ $opt_key_unit ] ) ? $transit_times[ $opt_key_unit ] : 'DAY';

					echo '<tr>' . "\n"; 
	
					echo '<td class="shipping-method">' . $method_name . '</td>' . "\n";
	
					echo '<td class="shipping-rate">' . $rate_type . '</td>' . "\n";
	
					echo '<td class="minimum-time">';
					echo '<input type="number" step="0.5" min="0" name="wcsdt_transit_time[' . $opt_key_min . ']" value="' . $min_val . '"/>';
					echo '</td>' . "\n";
	
					echo '<td class="maximum-time">';
					echo '<input type="number" step="0.5" min="0" name="wcsdt_transit_time[' . $opt_key_max . ']" value="' . $max_val . '"/>';
					echo '</td>' . "\n";
	
					echo '<td class="unit-of-time">' . "\n";
					$this->show_unit_select( 'wcsdt_transit_time', $opt_key_unit, $unit_code );
					echo '</td>' . "\n";
	
					echo '</tr>' . "\n"; 
				}
			}
	
			echo '</tbody>' . "\n";
		}

		/**
		 * Action called by WC_Admin_Settings->save() in woocommerce/includes/admin/class-wc-admin-settings.php.
		 */
		public function save_options() {

			global $current_section;

			if ( 'options' !== $current_section ) {	// Just in case.

				return;
			}

			foreach ( array(
				'wcsdt_handling_time',
				'wcsdt_transit_time',
			) as $options_name ) {

				$save_opts = array();

				$post_opts = isset( $_POST[ $options_name ] ) ? $_POST[ $options_name ] : array();

				if ( is_array( $post_opts ) ) {	// Just in case.

					foreach ( $post_opts as $opt_key => $val ) {

						if ( '' !== $val ) {	// Allow for 0.

							$opt_key = SucomUtil::sanitize_key( $opt_key );	// Just in case.

							$save_opts[ $opt_key ] = $val;
						}
					}

					$_POST[ $options_name ] = $save_opts;
				}

				update_option( $options_name, $save_opts, $autoload = true );
			}
		}

		private function get_times_label( $min_val, $max_val, $unit_code ) {

			/**
			 * UN/CEFACT Common Code (3 characters).
			 */
			switch ( $unit_code ) {

				case 'HUR':

					$times_transl = array(
						'equal'   => _n( '%1$s hour', '%1$s hours', $min_val, 'wpsso-wc-shipping-delivery-time' ),
						'min_max' => __( '%1$s - %2$s hours', 'wpsso-wc-shipping-delivery-time' ),
						'min'     => _n( '%1$s or more hours', '%1$s or more hours', $min_val, 'wpsso-wc-shipping-delivery-time' ),
						'max'     => _n( 'up to %2$s hour', 'up to %2$s hours', $max_val, 'wpsso-wc-shipping-delivery-time' ),
					);

					break;

				case 'DAY':
				default:

					$times_transl = array(
						'equal'   => _n( '%1$s day', '%1$s days', $min_val, 'wpsso-wc-shipping-delivery-time' ),
						'min_max' => __( '%1$s - %2$s days', 'wpsso-wc-shipping-delivery-time' ),
						'min'     => _n( '%1$s or more days', '%1$s or more days', $min_val, 'wpsso-wc-shipping-delivery-time' ),
						'max'     => _n( 'up to %2$s day', 'up to %2$s days', $max_val, 'wpsso-wc-shipping-delivery-time' ),
					);

					break;
			}

			if ( ! empty( $min_val ) ) {

				if ( ! empty( $max_val ) ) {

					if ( $min_val === $max_val ) {

						$times_label = $times_transl[ 'equal' ];

					} else {

						$times_label = $times_transl[ 'min_max' ];
					}

				} else {

					$times_label = $times_transl[ 'min' ];
				}

			} else {

				$times_label = $times_transl[ 'max' ];
			}

			$times_label = apply_filters( 'wpsso_wcsdt_shipping_times_label', $times_label, $min_val, $max_val, $unit_code );
			$times_label = sprintf( $times_label, $min_val, $max_val );

			return $times_label;
		}

		private function get_package_times( $min_val, $max_val, $unit_code ) {

			static $max_val_allowed  = true;
			static $pkg_min_val_secs = '';
			static $pkg_max_val_secs = '';
			static $pkg_unit_code    = '';

			if ( ! empty( $unit_code ) ) {	// A unit code is required.

				$min_val_secs = $this->unit_to_seconds( $min_val, $unit_code );	// Returns empty string or seconds.
				$max_val_secs = $this->unit_to_seconds( $max_val, $unit_code );	// Returns empty string or seconds.

				if ( empty( $pkg_unit_code ) || $this->is_unit_code_greater( $unit_code, $pkg_unit_code ) ) {

					$pkg_unit_code = $unit_code;
				}

				if ( '' !== $min_val_secs ) {	// Allow for 0.

					if ( empty( $max_val_secs ) ) {	// An empty string or 0 (maximum should not be 0).

						$max_val_allowed = false;

						$pkg_max_val_secs = '';	// Just in case.
					}

					if ( '' === $pkg_min_val_secs || $min_val_secs > $pkg_min_val_secs ) {	// Allow for 0.

						$pkg_min_val_secs = $min_val_secs;

						if ( '' !== $pkg_max_val_secs ) {	// Increase from 0 (maximum should not be 0).

							if ( $pkg_min_val_secs > $pkg_max_val_secs ) {

								$pkg_max_val_secs = $pkg_min_val_secs;
							}
						}
					}
				}

				if ( $max_val_allowed ) {

					if ( ! empty( $max_val_secs ) ) {	// Not an empty string or 0 (maximum should not be 0).

						if ( empty( $pkg_max_val_secs ) || $max_val_secs > $pkg_max_val_secs ) {

							$pkg_max_val_secs = $max_val_secs;
						}
					}
				}
			}

			$pkg_min_val = $this->seconds_to_unit( $pkg_min_val_secs, $pkg_unit_code );	// Returns empty string or number as units.
			$pkg_max_val = $this->seconds_to_unit( $pkg_max_val_secs, $pkg_unit_code );	// Returns empty string or number as units.

			return array( $pkg_min_val, $pkg_max_val, $pkg_unit_code );
		}

		/**
		 * Is unit code $a greater than unit code $b?
		 */
		private function is_unit_code_greater( $a, $b ) {

			$a_secs = $this->unit_to_seconds( 1, $a );
			$b_secs = $this->unit_to_seconds( 1, $b );

			return $a_secs > $b_secs ? true : false;
		}

		private function unit_to_seconds( $val, $unit_code ) {

			if ( empty( $val ) ) {	// Nothing to do - empty string or 0.

				return $val;
			}

			/**
			 * UN/CEFACT Common Code (3 characters).
			 */
			switch ( $unit_code ) {

				case 'HUR':

					return $val * HOUR_IN_SECONDS;

				case 'DAY':

					return $val * DAY_IN_SECONDS;
			}

			return '';
		}

		private function seconds_to_unit( $val, $unit_code ) {

			if ( empty( $val ) ) {	// Nothing to do - empty string or 0.

				return $val;
			}

			/**
			 * UN/CEFACT Common Code (3 characters).
			 */
			switch ( $unit_code ) {

				case 'HUR':

					return $val / HOUR_IN_SECONDS;

				case 'DAY':

					return $val / DAY_IN_SECONDS;
			}

			return '';
		}

		private function show_unit_select( $opt_name, $opt_key, $unit_code ) {

			echo '<select name="'. $opt_name . '[' . $opt_key . ']">' . "\n";

			$this->show_unit_options( $unit_code );

			echo '</select>' . "\n";
		}

		private function show_unit_options( $unit_code ) {

			foreach ( array(
				'HUR' => __( 'Hours', 'wpsso-wc-shipping-delivery-time' ),
				'DAY' => __( 'Days', 'wpsso-wc-shipping-delivery-time' ),
			) as $value => $label ) {

				echo '<option value="' . $value . '"';

				selected( $value, $unit_code, $echo = true );

				echo '>' . $label . '</option>' . "\n";
			}
		}
	}
}
