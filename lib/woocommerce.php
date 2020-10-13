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

			if ( is_admin() ) {

				/**
				 * Shipping options.
				 */
				add_filter( 'woocommerce_shipping_settings', array( $this, 'add_shipping_options' ), 10, 1 );

				add_action( 'woocommerce_settings_wcsdt_transit_end', array( $this, 'show_wcsdt_transit' ) );

				add_action( 'woocommerce_settings_save_shipping', array( $this, 'save_shipping_options' ) );
			}
		}

		public function add_shipping_options( $settings ) {

			$new_settings = array(
				array(
					'id'    => 'wcsdt_options',
					'type'  => 'title',
					'title' => __( 'Shipping delivery time estimates', 'wpsso-wc-shipping-delivery-time' ),
				),
				array(
					'id'       => 'wcsdt_show_format',
					'type'     => 'radio',
					'title'    => __( 'Show time estimates', 'wpsso-wc-shipping-delivery-time' ),
					'options'  => array(
						'none'  => __( 'Do not show time estimates', 'wpsso-wc-shipping-delivery-time' ),
						'days'  => __( 'Show time estimates in days', 'wpsso-wc-shipping-delivery-time' ),
					),
					'default'  => 'days',
					'desc_tip' => true,
					'desc'     => __( 'This changes the way shipping delivery time estimates are shown to customers.', 'wpsso-wc-shipping-delivery-time' ),
				),
				array(
					'id'   => 'wcsdt_transit',
					'type' => 'sectionend',
				),
			);
	
			return array_merge( $settings, $new_settings );
		}

		public function show_wcsdt_transit() {

			echo '<tr valign="top">' . "\n";
			echo '<th scope="row" class="titledesc">' . esc_html( 'In-transit estimates', 'woocommerce-shipping-estimate' ) . '</th>' . "\n";
			echo '<td class="forminp">' . "\n";
			echo '<table class="wc_shipping widefat wp-list-table" cellspacing="0">' . "\n";

			$shipping_zones = WC_Shipping_Zones::get_zones( $context = 'admin' );	// Since WC v2.6.0.

			if ( ! empty( $shipping_zones ) ) {

				foreach ( $shipping_zones as $zone_id => $zone ) {

					$zone_obj          = WC_Shipping_Zones::get_zone( $zone_id );	// Since WC v2.6.0.
					$zone_methods      = $zone_obj->get_shipping_methods( $enabled_only = true, $context = 'admin' );
					$zone_name         = $zone_obj->get_zone_name( $context = 'admin' );
					$zone_admin_url    = admin_url( 'admin.php?page=wc-settings&tab=shipping&zone_id=' . $zone_id );
					$zone_label_transl = '<a href="' . $zone_admin_url . '">' . esc_html( $zone_name ) . '</a> ' .
						esc_html( 'shipping zone', 'wpsso-wc-shipping-delivery-time' );

					$this->show_zone_method_rows( $zone_label_transl, $zone_id, $zone_methods );
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

				$this->show_zone_method_rows( $zone_label_transl, $world_zone_id, $world_zone_methods );
			}

			echo '</table><!-- .wc_shipping.widefat.wp-list-table -->' . "\n";
			echo '</td><!-- .forminp -->' . "\n";
			echo '</tr>' . "\n";
		}

		private function show_zone_method_rows( $label_transl, $zone_id, $shipping_methods ) {

			$wcsdt_transit = get_option( 'wcsdt_transit', array() );

			$shipping_classes = WC()->shipping()->get_shipping_classes();

			echo '<thead><tr style="background:#e9e9e9;">' . "\n";

			echo '<th colspan="4" style="text-align:center; border:1px solid #e1e1e1;">' . $label_transl . '</th>' . "\n";

			echo '</tr><tr>' . "\n";

			echo '<th class="shipping-method" style="padding-left:2% !important;">' . esc_html( 'Shipping method',
				'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";

			echo '<th class="shipping-class">' . esc_html( 'Shipping class', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";

			echo '<th class="transit-min-days">' . esc_html( 'Minimum days', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The minimum estimated in-transit time. Can be left blank.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</th>' . "\n";

			echo '<th class="transit-max-days">' . esc_html( 'Maximum days', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The maximum estimated in-transit time. Can be left blank.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</th>' . "\n";
			
			echo '</tr></thead>' . "\n";

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

				// translators: Please ignore - translation uses a different text domain.
				$active_class_names = array( 0 => __( 'No shipping class', 'woocommerce' ) );

				foreach ( $shipping_classes as $shipping_class_obj ) {

					$shipping_class_id = $shipping_class_obj->term_id;

					if ( isset( $method_data[ 'class_cost_' . $shipping_class_id ] ) ) {

						$active_class_names[ $shipping_class_id ] = $shipping_class_obj->name;
					}
				}

				$row_span = count( $active_class_names );	// At least 1.

				foreach ( $active_class_names as $shipping_class_id => $shipping_class_name ) {

					$opt_key      = 'z' . $zone_id . '-m' . $method_inst_id . '-c' . $shipping_class_id;
					$opt_key_min  = $opt_key . '-min_days';
					$opt_key_max  = $opt_key . '-max_days';
					$min_days_val = isset( $wcsdt_transit[ $opt_key_min ] ) ? $wcsdt_transit[ $opt_key_min ] : '';
					$max_days_val = isset( $wcsdt_transit[ $opt_key_max ] ) ? $wcsdt_transit[ $opt_key_max ] : '';

					echo '<tr>' . "\n"; 

					if ( 0 === $shipping_class_id ) {	// No shipping class.

						echo '<td style="padding-left:2%" class="shipping-method" rowspan="' . $row_span . '">' .
							'<a href="' . $method_url . '">' . $method_name . '</a></td>' . "\n";
					}

					echo '<td class="shipping-class">' . $shipping_class_name . '</td>' . "\n";

					echo '<td class="from-days">';
					echo '<input type="number" step="1" min="0" name="wcsdt_transit[' . $opt_key_min . ']" value="' . $min_days_val . '"/>';
					echo '</td>' . "\n";

					echo '<td class="to-days">';
					echo '<input type="number" step="1" min="0" name="wcsdt_transit[' . $opt_key_max . ']" value="' . $max_days_val . '"/>';
					echo '</td>' . "\n";

					echo '</tr>' . "\n"; 
				}
			}

			echo '</tbody>' . "\n";
		}

		/**
		 * Action called by WC_Admin_Settings->save() in woocommerce/includes/admin/class-wc-admin-settings.php.
		 */
		public function save_shipping_options() {

			global $current_section;

			if ( 'options' !== $current_section ) {	// Just in case.

				return;
			}

			$wcsdt_transit = array();

			$transit_opts = isset( $_POST[ 'wcsdt_transit' ] ) ? $_POST[ 'wcsdt_transit' ] : array();

			if ( is_array( $transit_opts ) ) {	// Jusr in case.
				
				foreach ( $transit_opts as $opt_key => $value ) {
				
					if ( empty( $value ) ) {
						
						continue;
					}

					$opt_key = SucomUtil::sanitize_key( $opt_key );	// Just in case.

					$wcsdt_transit[ $opt_key ] = absint( $value );

					if ( preg_match( '/^(.*)-min_days$/', $opt_key, $matches ) ) {

						$opt_key_max = $matches[ 1 ] . '-max_days';

						if ( empty( $wcsdt_transit[ $opt_key_max ] ) ) {

							$wcsdt_transit[ $opt_key_max ] = $wcsdt_transit[ $opt_key ];
						}

					} elseif ( preg_match( '/^(.*)-max_days$/', $opt_key, $matches ) ) {

						$opt_key_min = $matches[ 1 ] . '-min_days';

						if ( empty( $wcsdt_transit[ $opt_key_min ] ) ) {

							$wcsdt_transit[ $opt_key_min ] = 1;
						}
					}
				}

				$_POST[ 'wcsdt_transit' ] = $wcsdt_transit;
			}
		
			update_option( 'wcsdt_transit', $wcsdt_transit, $autoload = true );
		}
	}
}
