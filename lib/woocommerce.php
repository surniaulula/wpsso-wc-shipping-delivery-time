<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2020-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoWcsdtWooCommerce' ) ) {

	class WpssoWcsdtWooCommerce {

		private $p;	// Wpsso class object.
		private $a;	// WpssoWcsdt class object.
		private $admin;	// WpssoWcsdtWooCommerceAdmin class object.

		/*
		 * Instantiated by WpssoWcsdt->init_objects().
		 */
		public function __construct( &$plugin, &$addon ) {

			static $do_once = null;

			if ( $do_once ) return;	// Stop here.

			$do_once = true;

			$this->p =& $plugin;
			$this->a =& $addon;

			if ( 'yes' === get_option( 'wcsdt_show_handling_times', $default = 'yes' ) ) {

				add_action( 'woocommerce_after_shipping_rate', array( $this, 'show_handling_time_label' ), 1000, 2 );
			}

			if ( 'yes' === get_option( 'wcsdt_show_transit_times', $default = 'yes' ) ) {

				add_filter( 'woocommerce_cart_shipping_method_full_label', array( $this, 'add_transit_time_label' ), 1000, 2 );
			}

			if ( is_admin() ) {

				require_once WPSSOWCSDT_PLUGINDIR . 'lib/woocommerce-admin.php';

				$this->admin = new WpssoWcsdtWooCommerceAdmin( $plugin, $addon );
			}
		}

		public function show_handling_time_label( $method_obj, $pkg_index ) {

			static $packages     = null;
			static $method_count = 0;

			if ( null === $packages ) {

				$packages = WC()->shipping()->get_packages();
			}

			if ( empty( $packages[ $pkg_index ][ 'rates' ] ) ) {	// Shipping methods.

				return;
			}

			$package       = $packages[ $pkg_index ];
			$total_methods = count( $package[ 'rates' ] );

			$method_count++;

			if ( $method_count < $total_methods ) {	// Wait for the last shipping method.

				return;
			}

			if ( isset( $this->p->options[ 'wcsdt_combined_options' ] ) ) {	// Since WPSSO WCSDT v2.0.0.

				$opts =& $this->p->options;

			} else {	// Check for deprecated WPSSO WCSDT v1 options.

				static $opts = null;

				if ( null === $opts ) {

					$opts = array();

					foreach ( get_option( 'wcsdt_handling_time', array() ) as $key => $val ) {

						$opts[ 'wcsdt_handling_' . $key ] = $val;
					}
				}
			}

			$pkg_min_val   = '';
			$pkg_max_val   = '';
			$pkg_unit_code = '';

			/*
			 * Determine the $pkg_min_val and $pkg_max_val values.
			 */
			foreach ( $package[ 'contents' ] as $item_id => $values ) {

					$product           = $values[ 'data' ];
					$shipping_class_id = $product->get_shipping_class_id();	// 0 or a selected product "Shipping class".

					$opt_key_pre  = 'wcsdt_handling_c' . $shipping_class_id;
					$opt_key_min  = $opt_key_pre . '_minimum';
					$opt_key_max  = $opt_key_pre . '_maximum';
					$opt_key_unit = $opt_key_pre . '_unit_code';

					$min_val   = isset( $opts[ $opt_key_min ] ) ? round( $opts[ $opt_key_min ] ) : '';
					$max_val   = isset( $opts[ $opt_key_max ] ) ? round( $opts[ $opt_key_max ] ) : '';
					$unit_code = isset( $opts[ $opt_key_unit ] ) ? $opts[ $opt_key_unit ] : '';

					list( $pkg_min_val, $pkg_max_val, $pkg_unit_code ) = $this->get_package_times( $min_val, $max_val, $unit_code );
			}

			if ( empty( $pkg_min_val ) && empty( $pkg_max_val ) ) {	// Nothing to show.

				return;
			}

			$times_label = $this->get_times_label( $pkg_min_val, $pkg_max_val, $pkg_unit_code );

			// tranlators: Shipping handling and packaging time under the shipping methods.
			$handling_label = '<span class="shipping-handling-time"><small>' .
				__( 'Add %s for handling &amp; packaging.', 'wpsso-wc-shipping-delivery-time' ) . '</small><span>';

			$handling_label = apply_filters( 'wpsso_wcsdt_shipping_handling_time_label', $handling_label, $times_label );

			$handling_label = ' ' . sprintf( $handling_label, $times_label );

			echo $handling_label;
		}

		/*
		 * $method is a WC_Shipping_Rate object.
		 */
		public function add_transit_time_label( $method_label, $method_obj ) {

			if ( empty( $method_obj->instance_id ) ) {

				return $method_label;
			}

			if ( isset( $this->p->options[ 'wcsdt_combined_options' ] ) ) {	// Since WPSSO WCSDT v2.0.0.

				$opts =& $this->p->options;

			} else {	// Check for deprecated WPSSO WCSDT v1 options.

				static $opts = null;

				if ( null === $opts ) {

					$opts = array();

					foreach ( get_option( 'wcsdt_transit_time', array() ) as $key => $val ) {

						$opts[ 'wcsdt_transit_' . $key ] = $val;
					}
				}
			}

			$method_inst_id = $method_obj->get_instance_id();

			$opt_key_pre  = 'wcsdt_transit_m' . $method_inst_id;
			$opt_key_min  = $opt_key_pre . '_minimum';
			$opt_key_max  = $opt_key_pre . '_maximum';
			$opt_key_unit = $opt_key_pre . '_unit_code';

			$min_val   = isset( $opts[ $opt_key_min ] ) ? round( $opts[ $opt_key_min ] ) : '';
			$max_val   = isset( $opts[ $opt_key_max ] ) ? round( $opts[ $opt_key_max ] ) : '';
			$unit_code = isset( $opts[ $opt_key_unit ] ) ? $opts[ $opt_key_unit ] : '';

			if ( empty( $min_val ) && empty( $max_val ) ) {	// Nothing to do.

				return $method_label;
			}

			$times_label = $this->get_times_label( $min_val, $max_val, $unit_code );

			// translators: Shipping transit time in the shipping method label.
			$transit_label = __( '(%s)', 'wpsso-wc-shipping-delivery-time' );

			$transit_label = apply_filters( 'wpsso_wcsdt_shipping_transit_time_label', $transit_label, $times_label );

			$transit_label = ' ' . sprintf( $transit_label, $times_label );

			return $method_label . $transit_label;
		}

		private function get_times_label( $min_val, $max_val, $unit_code ) {

			/*
			 * Format the $min_val and $max_val strings using the localized PHP decimal separators.
			 *
			 * See https://www.php.net/manual/en/function.setlocale.php.
			 * See https://woocommerce.github.io/code-reference/files/woocommerce-includes-wc-formatting-functions.html#source-view.353.
			 */
			$wp_locale  = SucomUtilWP::get_locale( 'current' );		// Get the current WordPress locale.
			$php_locale = setlocale( LC_NUMERIC, 0 );			// Get the current PHP locale.

			if ( $wp_locale !== $php_locale ) {				// Check if we're already using the WordPress locale.

				setlocale( LC_NUMERIC, $wp_locale );			// Set the PHP decimal separators based on the WordPress locale.
			}

			$min_val_transl = wc_format_localized_decimal( $min_val );	// Format $min_val using PHP decimal separators.
			$max_val_transl = wc_format_localized_decimal( $max_val );	// Format $max_val using PHP decimal separators.

			if ( $wp_locale !== $php_locale ) {				// Check if we need to restore the original PHP locale.

				setlocale( LC_NUMERIC, $php_locale );			// Restore the original PHP decimal separators.
			}

			/*
			 * See http://wiki.goodrelations-vocabulary.org/Documentation/UN/CEFACT_Common_Codes.
			 */
			switch ( $unit_code ) {

				case 'HUR':

					$times_transl = array(
						'equal'   => _n( '%1$s hour', '%1$s hours', $min_val_transl, 'wpsso-wc-shipping-delivery-time' ),
						'min_max' => __( '%1$s - %2$s hours', 'wpsso-wc-shipping-delivery-time' ),
						'min'     => _n( '%1$s or more hours', '%1$s or more hours', $min_val_transl, 'wpsso-wc-shipping-delivery-time' ),
						'max'     => _n( 'up to %2$s hour', 'up to %2$s hours', $max_val_transl, 'wpsso-wc-shipping-delivery-time' ),
					);

					break;

				case 'DAY':
				default:

					$times_transl = array(
						'equal'   => _n( '%1$s day', '%1$s days', $min_val_transl, 'wpsso-wc-shipping-delivery-time' ),
						'min_max' => __( '%1$s - %2$s days', 'wpsso-wc-shipping-delivery-time' ),
						'min'     => _n( '%1$s or more days', '%1$s or more days', $min_val_transl, 'wpsso-wc-shipping-delivery-time' ),
						'max'     => _n( 'up to %2$s day', 'up to %2$s days', $max_val_transl, 'wpsso-wc-shipping-delivery-time' ),
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

			$times_label = trim( sprintf( $times_label, $min_val, $max_val ) );

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

		/*
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

			/*
			 * See http://wiki.goodrelations-vocabulary.org/Documentation/UN/CEFACT_Common_Codes.
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

			/*
			 * See http://wiki.goodrelations-vocabulary.org/Documentation/UN/CEFACT_Common_Codes.
			 */
			switch ( $unit_code ) {

				case 'HUR':

					return $val / HOUR_IN_SECONDS;

				case 'DAY':

					return $val / DAY_IN_SECONDS;
			}

			return '';
		}
	}
}
