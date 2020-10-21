<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2020 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! defined( 'WPSSOWCSDT_PLUGINDIR' ) ) {

	die( 'Do. Or do not. There is no try.' );
}

if ( ! class_exists( 'WpssoWcsdtWooCommerceAdmin' ) ) {

	class WpssoWcsdtWooCommerceAdmin {

		private $p;	// Wpsso object.

		/**
		 * Instantiated by WpssoWcsdtWooCommerce->__construct().
		 */
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

			add_filter( 'plugin_action_links', array( $this, 'add_plugin_action_links' ), 200, 4 );
			add_filter( 'woocommerce_get_sections_shipping', array( $this, 'add_sections' ), 10, 1 );
			add_filter( 'woocommerce_get_settings_shipping', array( $this, 'add_settings' ), 10, 2 );

			add_action( 'woocommerce_settings_wcsdt_options_end', array( $this, 'show_stylesheet' ), 10 );
			add_action( 'woocommerce_settings_wcsdt_options_end', array( $this, 'show_handling_time' ), 20 );
			add_action( 'woocommerce_settings_wcsdt_options_end', array( $this, 'show_transit_time' ), 30 );
			add_action( 'woocommerce_settings_save_shipping', array( $this, 'save_settings' ) );
		}

		public function add_plugin_action_links( $action_links, $plugin_base, $plugin_data, $context ) {

			if ( WPSSOWCSDT_PLUGINBASE === $plugin_base ) {

				$admin_url = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wcsdt' );

				// translators: Please ignore - translation uses a different text domain.
				$label_transl = esc_html__( 'Add-on Settings', 'wpsso' );

				$action_links[] = '<a href="' . $admin_url . '">' . $label_transl . '</a>';
			}

			return $action_links;
		}

		public function add_sections( $sections ) {

			SucomUtil::add_after_key( $sections, 'classes', 'wcsdt', __( 'Shipping delivery times', 'wpsso-wc-shipping-delivery-time' ) );

			return $sections;
		}

		public function add_settings( $settings, $current_section ) {

			if ( 'wcsdt' !== $current_section ) {

				return $settings;
			}

			/**
			 * See woocommerce/includes/admin/settings/class-wc-settings-shipping.php for examples.
			 */
			$settings = array(
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

			return $settings;
		}

		public function show_stylesheet() {

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

			$opts                 = get_option( 'wcsdt_handling_time', array() );
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

				$this->show_handling_time_table_rows( $opts, $shipping_class_id, $shipping_class_name, $shipping_class_desc );

			}

			$shipping_class_id   = 0;
			// translators: Please ignore - translation uses a different text domain.
			$shipping_class_name = __( 'No shipping class', 'woocommerce' );
			$shipping_class_desc = __( 'Products without a shipping class', 'wpsso-wc-shipping-delivery-time' ) ;

			$this->show_handling_time_table_rows( $opts, $shipping_class_id, $shipping_class_name, $shipping_class_desc );

			echo '</tbody>' . "\n";
			echo '</table><!-- .wc_shipping.widefat.wp-list-table -->' . "\n";
		}

		private function show_handling_time_table_rows( $opts, $shipping_class_id, $shipping_class_name, $shipping_class_desc ) {

			$opt_key_pre  = 'c' . $shipping_class_id;
			$opt_key_min  = $opt_key_pre . '_minimum';
			$opt_key_max  = $opt_key_pre . '_maximum';
			$opt_key_unit = $opt_key_pre . '_unit_code';

			$min_val   = isset( $opts[ $opt_key_min ] ) ? $opts[ $opt_key_min ] : '';
			$max_val   = isset( $opts[ $opt_key_max ] ) ? $opts[ $opt_key_max ] : '';
			$unit_code = isset( $opts[ $opt_key_unit ] ) ? $opts[ $opt_key_unit ] : 'DAY';

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

			$opts  = get_option( 'wcsdt_transit_time', array() );
			$zones = WC_Shipping_Zones::get_zones( $context = 'admin' );	// Since WC v2.6.0.

			foreach ( $zones as $zone_id => $zone ) {

				$zone_obj          = WC_Shipping_Zones::get_zone( $zone_id );	// Since WC v2.6.0.
				$zone_methods      = $zone_obj->get_shipping_methods( $enabled_only = true, $context = 'admin' );
				$zone_name         = $zone_obj->get_zone_name( $context = 'admin' );
				$zone_admin_url    = admin_url( 'admin.php?page=wc-settings&tab=shipping&zone_id=' . $zone_id );
				$zone_label_transl = '<a href="' . $zone_admin_url . '">' . esc_html( sprintf( __( '%s shipping zone',
					'wpsso-wc-shipping-delivery-time' ), $zone_name ) ) . '</a>';

				$this->show_transit_time_table_rows( $opts, $zone_label_transl, $zone_id, $zone_methods );
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

			$this->show_transit_time_table_rows( $opts, $zone_label_transl, $world_zone_id, $world_zone_methods );

			echo '</table><!-- .wc_shipping.widefat.wp-list-table -->' . "\n";
		}

		private function show_transit_time_table_rows( $opts, $zone_label_transl, $zone_id, $shipping_methods ) {

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

					$min_val   = isset( $opts[ $opt_key_min ] ) ? $opts[ $opt_key_min ] : '';
					$max_val   = isset( $opts[ $opt_key_max ] ) ? $opts[ $opt_key_max ] : '';
					$unit_code = isset( $opts[ $opt_key_unit ] ) ? $opts[ $opt_key_unit ] : 'DAY';

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
		public function save_settings() {

			global $current_section;

			if ( 'wcsdt' !== $current_section ) {	// Just in case.

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

		private function show_unit_select( $opt_name, $opt_key, $unit_code ) {

			echo '<select name="'. $opt_name . '[' . $opt_key . ']">' . "\n";

			foreach ( array(
				'HUR' => __( 'Hours', 'wpsso-wc-shipping-delivery-time' ),
				'DAY' => __( 'Days', 'wpsso-wc-shipping-delivery-time' ),
			) as $value => $label ) {

				echo '<option value="' . $value . '"';

				selected( $value, $unit_code, $echo = true );

				echo '>' . $label . '</option>' . "\n";
			}

			echo '</select>' . "\n";
		}
	}
}
