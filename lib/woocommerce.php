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
		private $handling_time_step = '0.5';
		private $handling_time_prec = '1';	// Floating point precision.
		private $transit_time_step  = '1';
		private $transit_time_prec  = '0';

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
				add_action( 'woocommerce_settings_wcsdt_options_end', array( $this, 'show_handling_time' ), 10 );
				add_action( 'woocommerce_settings_wcsdt_options_end', array( $this, 'show_transit_time' ), 20 );
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

			if ( empty( $packages[ $pkg_index ][ 'rates' ] ) ) {

				return;
			}

			$package       = $packages[ $pkg_index ];
			$total_methods = count( $package[ 'rates' ] );
			$method_count++;

			if ( $method_count !== $total_methods ) {	// Check for last shipping method.

				return;
			}

			$max_days_allowed = true;
			$pkg_min_days     = '';
			$pkg_max_days     = '';

			/**
			 * Determine the $pkg_min_days and $pkg_max_days values.
			 */
			foreach ( $package[ 'contents' ] as $item_id => $values ) {
			
					$product           = $values[ 'data' ];
					$shipping_class_id = $product->get_shipping_class_id();	// 0 or a selected product "Shipping class".
					$opt_key           = 'c' . $shipping_class_id;
					$opt_key_min       = $opt_key . '-min_days';
					$opt_key_max       = $opt_key . '-max_days';
					$min_days          = isset( $handling_times[ $opt_key_min ] ) ? $handling_times[ $opt_key_min ] : '';
					$max_days          = isset( $handling_times[ $opt_key_max ] ) ? $handling_times[ $opt_key_max ] : '';

					if ( ! empty( $min_days ) ) {

						if ( empty( $max_days ) ) {

							$max_days_allowed = false;
							$pkg_max_days     = '';	// Just in case.
						}

						if ( empty( $pkg_min_days ) || $min_days > $pkg_min_days ) {

							$pkg_min_days = $min_days;

							if ( ! empty( $pkg_max_days ) ) {
							
								if ( $pkg_min_days > $pkg_max_days ) {
									
									$pkg_max_days = $pkg_min_days;
								}
							}
						}
					}

					if ( $max_days_allowed ) {

						if ( ! empty( $max_days ) ) {
					
							if ( empty( $pkg_max_days ) || $max_days > $pkg_max_days ) {

								$pkg_max_days = $max_days;
							}
						}
					}
			}
	
			if ( empty( $pkg_min_days ) && empty( $pkg_max_days ) ) {	// Nothing to show.

				return;
			}

			$days_label     = $this->get_days_label( $pkg_min_days, $pkg_max_days );
			$handling_label = '<label><small>Add %s for handling &amp; packaging.</small></label>';
			$handling_label = apply_filters( 'wpsso_wcsdt_shipping_handling_time_label', $handling_label, $days_label );
			$handling_label = sprintf( $handling_label, $days_label );

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
			$opt_key        = 'm' . $method_inst_id;
			$opt_key_min    = $opt_key . '-min_days';
			$opt_key_max    = $opt_key . '-max_days';
			$min_days       = isset( $transit_times[ $opt_key_min ] ) ? $transit_times[ $opt_key_min ] : '';
			$max_days       = isset( $transit_times[ $opt_key_max ] ) ? $transit_times[ $opt_key_max ] : '';

			if ( empty( $min_days ) && empty( $max_days ) ) {	// Nothing to do.

				return $method_label;
			}

			$days_label    = $this->get_days_label( $min_days, $max_days );
			$transit_label = ' (%s)';
			$transit_label = apply_filters( 'wpsso_wcsdt_shipping_transit_time_label', $transit_label, $days_label );
			$transit_label = sprintf( $transit_label, $days_label );

			return $method_label . $transit_label;
		}

		private function get_days_label( $min_days, $max_days ) {

			if ( ! empty( $min_days ) ) {

				if ( ! empty( $max_days ) ) {

					if ( $min_days === $max_days ) {

						$days_label =  _n( '%1$s day', '%1$s days', $min_days, 'wpsso-wc-shipping-delivery-time' );

					} else {

						$days_label = __( '%1$s - %2$s days', 'wpsso-wc-shipping-delivery-time' );
					}

				} else {

					$days_label = __( '%1$s or more days', 'wpsso-wc-shipping-delivery-time' );
				}

			} else {

				$days_label = _n( 'up to %2$s day', 'up to %2$s days', $max_days, 'woocommerce-shipping-estimate' );
			}

			$days_label = apply_filters( 'wpsso_wcsdt_shipping_days_label', $days_label, $min_days, $max_days );
			$days_label = sprintf( $days_label, $min_days, $max_days );

			return $days_label;
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

		public function show_handling_time() {

			echo '<tr valign="top">' . "\n";
			echo '<th scope="row" class="titledesc"><label>' . esc_html__( 'Shipping handling times', 'woocommerce-shipping-estimate' ) .
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
			echo '<th colspan="4" style="text-align:center; border:1px solid #e1e1e1;">' . $classes_label_transl . '</th>' . "\n";
			echo '</tr>' . "\n";

			echo '<tr>' . "\n";

			echo '<th class="shipping-class" style="padding-left:2% !important;">' .
				esc_html__( 'Shipping class', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";

			echo '<th class="shipping-class-desc">' . esc_html__( 'Class description', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";

			echo '<th class="handling-min-days">' . esc_html__( 'Minimum days', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The estimated minimum handling and packaging time in days. Can be left blank.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</th>' . "\n";

			echo '<th class="handling-max-days">' . esc_html__( 'Maximum days', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The estimated maximum handling and packaging time in days. Can be left blank.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</th>' . "\n";

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

			$opt_key     = 'c' . $shipping_class_id;
			$opt_key_min = $opt_key . '-min_days';
			$opt_key_max = $opt_key . '-max_days';
			$min_days    = isset( $handling_times[ $opt_key_min ] ) ? $handling_times[ $opt_key_min ] : '';
			$max_days    = isset( $handling_times[ $opt_key_max ] ) ? $handling_times[ $opt_key_max ] : '';

			echo '<tr>' . "\n"; 

			echo '<td class="shipping-class">' . $shipping_class_name . '</td>' . "\n";

			echo '<td class="shipping-class-desc">' . $shipping_class_desc . '</td>' . "\n";

			echo '<td class="handling-min-days">';
			echo '<input type="number" step="' . $this->handling_time_step . '" min="0" ' .
				'name="wcsdt_handling_time[' . $opt_key_min . ']" value="' . $min_days . '"/>';
			echo '</td>' . "\n";

			echo '<td class="handling-max-days">';
			echo '<input type="number" step="' . $this->handling_time_step . '" min="0" ' .
				'name="wcsdt_handling_time[' . $opt_key_max . ']" value="' . $max_days . '"/>';
			echo '</td>' . "\n";

			echo '</tr>' . "\n"; 
		}

		public function show_transit_time() {

			echo '<tr valign="top">' . "\n";
			echo '<th scope="row" class="titledesc"><label>' . esc_html__( 'Shipping transit times', 'woocommerce-shipping-estimate' ) .
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

			if ( ! empty( $shipping_zones ) ) {

				foreach ( $shipping_zones as $zone_id => $zone ) {

					$zone_obj          = WC_Shipping_Zones::get_zone( $zone_id );	// Since WC v2.6.0.
					$zone_methods      = $zone_obj->get_shipping_methods( $enabled_only = true, $context = 'admin' );
					$zone_name         = $zone_obj->get_zone_name( $context = 'admin' );
					$zone_admin_url    = admin_url( 'admin.php?page=wc-settings&tab=shipping&zone_id=' . $zone_id );
					$zone_label_transl = '<a href="' . $zone_admin_url . '">' . esc_html( sprintf( __( '%s shipping zone',
						'wpsso-wc-shipping-delivery-time' ), $zone_name ) ) . '</a>';

					$this->show_transit_time_table_rows( $transit_times, $zone_label_transl, $zone_id, $zone_methods );
				}
			}

			/**
			 * Locations not covered by your other zones.
			 */
			$world_zone_id      = 0;
			$world_zone_obj     = WC_Shipping_Zones::get_zone( $world_zone_id );	// Locations not covered by your other zones.
			$world_zone_methods = $world_zone_obj->get_shipping_methods();

			if ( ! empty( $world_zone_methods ) ) {

				// translators: Please ignore - translation uses a different text domain.
				$zone_name         = $world_zone_obj->get_zone_name( $context = 'admin' );
				$zone_admin_url    = admin_url( 'admin.php?page=wc-settings&tab=shipping&zone_id=' . $world_zone_id );
				$zone_label_transl = '<a href="' . $zone_admin_url . '">' . esc_html( $zone_name ) . '</a> ';

				$this->show_transit_time_table_rows( $transit_times, $zone_label_transl, $world_zone_id, $world_zone_methods );
			}

			echo '</table><!-- .wc_shipping.widefat.wp-list-table -->' . "\n";
		}

		private function show_transit_time_table_rows( $transit_times, $zone_label_transl, $zone_id, $shipping_methods ) {

			echo '<thead>' . "\n";
			echo '<tr style="background:#e9e9e9;">' . "\n";
			echo '<th colspan="4" style="text-align:center; border:1px solid #e1e1e1;">' . $zone_label_transl . '</th>' . "\n";
			echo '</tr>' . "\n";
			
			echo '<tr>' . "\n";

			echo '<th class="shipping-method" style="padding-left:2% !important;">' .
				esc_html__( 'Shipping method', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";

			echo '<th class="shipping-rate">' . esc_html__( 'Shipping rate', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";

			echo '<th class="transit-min-days">' . esc_html__( 'Minimum days', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The estimated minimum transit time in days. Can be left blank.', 'wpsso-wc-shipping-delivery-time' ) ) . '</th>' . "\n";

			echo '<th class="transit-max-days">' . esc_html__( 'Maximum days', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The estimated maximum transit time in days. Can be left blank.', 'wpsso-wc-shipping-delivery-time' ) ) . '</th>' . "\n";

			echo '</tr>' . "\n";
			echo '</thead>' . "\n";
			echo '<tbody>' . "\n";

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

				$opt_key     = 'm' . $method_inst_id;
				$opt_key_min = $opt_key . '-min_days';
				$opt_key_max = $opt_key . '-max_days';
				$min_days    = isset( $transit_times[ $opt_key_min ] ) ? $transit_times[ $opt_key_min ] : '';
				$max_days    = isset( $transit_times[ $opt_key_max ] ) ? $transit_times[ $opt_key_max ] : '';

				echo '<tr>' . "\n"; 

				echo '<td class="shipping-method">' . $method_name . '</td>' . "\n";

				echo '<td class="shipping-rate">' . $rate_type . '</td>' . "\n";

				echo '<td class="transit-min-days">';
				echo '<input type="number" step="' . $this->transit_time_step . '" min="0" ' .
					'name="wcsdt_transit_time[' . $opt_key_min . ']" value="' . $min_days . '"/>';
				echo '</td>' . "\n";

				echo '<td class="transit-max-days">';
				echo '<input type="number" step="' . $this->transit_time_step . '" min="0" ' .
					'name="wcsdt_transit_time[' . $opt_key_max . ']" value="' . $max_days . '"/>';
				echo '</td>' . "\n";

				echo '</tr>' . "\n"; 
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
				'wcsdt_handling_time' => $this->handling_time_prec,
				'wcsdt_transit_time'  => $this->transit_time_prec,
			) as $options_name => $val_prec ) {

				$save_opts = array();
	
				$post_opts = isset( $_POST[ $options_name ] ) ? $_POST[ $options_name ] : array();
	
				if ( is_array( $post_opts ) ) {	// Just in case.
					
					foreach ( $post_opts as $opt_key => $opt_val ) {
					
						if ( empty( $opt_val ) ) {
							
							continue;
						}
	
						$opt_key = SucomUtil::sanitize_key( $opt_key );	// Just in case.
	
						if ( $val_prec ) {

							$opt_val = sprintf( '%.' . $val_prec . 'f', $opt_val );
							$opt_val = preg_replace( '/^(.*\..*?)0+$/', '$1', $opt_val );
							$opt_val = rtrim( $opt_val, '.' );

						} else {

							$opt_val = intval( $opt_val );
						}

						$save_opts[ $opt_key ] = $opt_val;
					}
	
					$_POST[ $options_name ] = $save_opts;
				}
		
				update_option( $options_name, $save_opts, $autoload = true );
			}
		}
	}
}
