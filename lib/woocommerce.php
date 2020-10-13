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
				add_filter( 'woocommerce_shipping_settings', array( $this, 'add_options' ), 10, 1 );

				add_action( 'woocommerce_settings_wcsdt_transit_estimates_end', array( $this, 'show_transit_estimates' ) );

				add_action( 'woocommerce_settings_save_shipping', array( $this, 'save_options' ) );
			}
		}

		public function add_options( $settings ) {

			$new_settings = array(
				array(
					'type'  => 'title',
					'title' => __( 'Shipping delivery times', 'wpsso-wc-shipping-delivery-time' ),
				),
				array(
					'id'       => 'wcsdt_checkout_format',
					'type'     => 'radio',
					'title'    => __( 'Checkout delivery estimates', 'wpsso-wc-shipping-delivery-time' ),
					'options'  => array(
						'none'  => __( 'Do not show delivery estimates', 'wpsso-wc-shipping-delivery-time' ),
						'days'  => __( 'Show delivery estimates in days', 'wpsso-wc-shipping-delivery-time' ),
					),
					'default'  => 'none',
					'desc_tip' => true,
					'desc'     => __( 'Optionally show delivery estimates at checkout.', 'wpsso-wc-shipping-delivery-time' ),
				),
				array(
					'id'   => 'wcsdt_transit_estimates',
					'type' => 'sectionend',
				),
			);
	
			return array_merge( $settings, $new_settings );
		}

		public function show_transit_estimates() {

			echo '<tr valign="top">' . "\n";
			echo '<th scope="row" class="titledesc">' . esc_html( 'Shipping transit estimates', 'woocommerce-shipping-estimate' ) . '</th>' . "\n";
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

					$this->show_zone_methods( $zone_label_transl, $zone_id, $zone_methods );
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

				$this->show_zone_methods( $zone_label_transl, $world_zone_id, $world_zone_methods );
			}

			echo '</table><!-- .wc_shipping.widefat.wp-list-table -->' . "\n";
			echo '</td><!-- .forminp -->' . "\n";
			echo '</tr>' . "\n";
		}

		private function show_zone_methods( $label_transl, $zone_id, $shipping_methods ) {

			$transit_estimates = get_option( 'wcsdt_transit_estimates', array() );

			echo '<thead><tr style="background:#e9e9e9;">' . "\n";

			echo '<th colspan="4" style="text-align:center; border:1px solid #e1e1e1;">' . $label_transl . '</th>' . "\n";

			echo '</tr><tr>' . "\n";

			echo '<th class="shipping-method" style="padding-left:2% !important;">' . esc_html( 'Shipping method',
				'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";

			echo '<th class="shipping-class">' . esc_html( 'Shipping rate', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";

			echo '<th class="transit-min-days">' . esc_html( 'Minimum days', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The minimum estimated transit time. Can be left blank.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</th>' . "\n";

			echo '<th class="transit-max-days">' . esc_html( 'Maximum days', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The maximum estimated transit time. Can be left blank.',
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

				$opt_key      = 'z' . $zone_id . '-m' . $method_inst_id;
				$opt_key_min  = $opt_key . '-min_days';
				$opt_key_max  = $opt_key . '-max_days';
				$min_days_val = isset( $transit_estimates[ $opt_key_min ] ) ? $transit_estimates[ $opt_key_min ] : '';
				$max_days_val = isset( $transit_estimates[ $opt_key_max ] ) ? $transit_estimates[ $opt_key_max ] : '';

				echo '<tr>' . "\n"; 

				echo '<td class="method-name">' . $method_name . '</td>' . "\n";

				echo '<td class="rate-type">' . $rate_type . '</td>' . "\n";

				echo '<td class="transit-min-days">';
				echo '<input type="number" step="1" min="0" name="wcsdt_transit_estimates[' . $opt_key_min . ']" value="' . $min_days_val . '"/>';
				echo '</td>' . "\n";

				echo '<td class="transit-max-days">';
				echo '<input type="number" step="1" min="0" name="wcsdt_transit_estimates[' . $opt_key_max . ']" value="' . $max_days_val . '"/>';
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

			$transit_estimates = array();

			$transit_opts = isset( $_POST[ 'wcsdt_transit_estimates' ] ) ? $_POST[ 'wcsdt_transit_estimates' ] : array();

			if ( is_array( $transit_opts ) ) {	// Jusr in case.
				
				foreach ( $transit_opts as $opt_key => $value ) {
				
					if ( empty( $value ) ) {
						
						continue;
					}

					$opt_key = SucomUtil::sanitize_key( $opt_key );	// Just in case.

					$transit_estimates[ $opt_key ] = absint( $value );

					if ( preg_match( '/^(.*)-min_days$/', $opt_key, $matches ) ) {

						$opt_key_max = $matches[ 1 ] . '-max_days';

						if ( empty( $transit_estimates[ $opt_key_max ] ) ) {

							$transit_estimates[ $opt_key_max ] = $transit_estimates[ $opt_key ];
						}

					} elseif ( preg_match( '/^(.*)-max_days$/', $opt_key, $matches ) ) {

						$opt_key_min = $matches[ 1 ] . '-min_days';

						if ( empty( $transit_estimates[ $opt_key_min ] ) ) {

							$transit_estimates[ $opt_key_min ] = 1;
						}
					}
				}

				$_POST[ 'wcsdt_transit_estimates' ] = $transit_estimates;
			}
		
			update_option( 'wcsdt_transit_estimates', $transit_estimates, $autoload = true );
		}
	}
}
