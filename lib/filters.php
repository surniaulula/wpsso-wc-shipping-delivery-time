<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2020-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoWcsdtFilters' ) ) {

	class WpssoWcsdtFilters {

		private $p;	// Wpsso class object.
		private $a;	// WpssoWcsdt class object.

		/*
		 * Instantiated by WpssoWcsdt->init_objects().
		 */
		public function __construct( &$plugin, &$addon ) {

			static $do_once = null;

			if ( $do_once ) return;	// Stop here.

			$do_once = true;

			$this->p =& $plugin;
			$this->a =& $addon;

			$this->p->util->add_plugin_filters( $this, array(
				'og_add_mt_shipping_offers' => '__return_true',
				'wc_shipping_delivery_time' => 5,
			), $prio = -1000 );	// Run first.
		}

		/*
		 * Returns shipping department, handling, and transit options for $shipping_class_id and $method_inst_id.
		 *
		 * Array (
		 * 	[shipdept_rel] => http://adm.surniaulula.com/produit/a-variable-product/
		 * 	[shipdept_timezone] => America/Vancouver
		 * 	[shipdept_midday_close] => 12:00
		 * 	[shipdept_midday_open] => 13:00
		 * 	[shipdept_cutoff] => 16:00
		 * 	[shipdept_day_sunday_open] => none
		 * 	[shipdept_day_sunday_close] => none
		 * 	[shipdept_day_monday_open] => 09:00
		 * 	[shipdept_day_monday_close] => 17:00
		 * 	[shipdept_day_tuesday_open] => 09:00
		 * 	[shipdept_day_tuesday_close] => 17:00
		 * 	[shipdept_day_wednesday_open] => 09:00
		 * 	[shipdept_day_wednesday_close] => 17:00
		 * 	[shipdept_day_thursday_open] => 09:00
		 * 	[shipdept_day_thursday_close] => 17:00
		 * 	[shipdept_day_friday_open] => 09:00
		 * 	[shipdept_day_friday_close] => 17:00
		 * 	[shipdept_day_saturday_open] => none
		 * 	[shipdept_day_saturday_close] => none
		 * 	[shipdept_day_publicholidays_open] => 09:00
		 * 	[shipdept_day_publicholidays_close] => 12:00
		 *  	[handling_rel] => http://adm.surniaulula.com/produit/a-variable-product/
		 * 	[handling_maximum] => 1.5
		 * 	[handling_unit_code] => DAY
		 * 	[handling_unit_text] => d
		 * 	[handling_name] => Days
		 * 	[transit_rel] => http://adm.surniaulula.com/produit/a-variable-product/
		 * 	[transit_minimum] => 5
		 * 	[transit_maximum] => 7
		 * 	[transit_unit_code] => DAY
		 * 	[transit_unit_text] => d
		 * 	[transit_name] => Days
		 * )
		 */
		public function filter_wc_shipping_delivery_time( $sdt_opts, $zone_id, $method_inst_id, $shipping_class_id, $parent_url ) {

			if ( isset( $this->p->options[ 'wcsdt_combined_options' ] ) ) {	// Since WPSSO WCSDT v2.

				$opts =& $this->p->options;

			} else {	// Check for deprecated WPSSO WCSDT v1 options.

				static $opts = null;

				if ( null === $opts ) {

					$opts = array();

					foreach ( get_option( 'wcsdt_handling_time', array() ) as $key => $val ) {

						$opts[ 'wcsdt_handling_' . $key ] = $val;
					}

					foreach ( get_option( 'wcsdt_transit_time', array() ) as $key => $val ) {

						$opts[ 'wcsdt_transit_' . $key ] = $val;
					}
				}
			}

			$type_keys = array(
				'shipdept' => 'wcsdt_shipdept',
				'handling' => 'wcsdt_handling_c' . $shipping_class_id,
				'transit'  => 'wcsdt_transit_m' . $method_inst_id,
			);

			foreach ( $type_keys as $type_key => $opt_key_pre ) {

				$sdt_opts[ $type_key . '_rel' ] = $parent_url;

			 	/*
				 * Get handling options for the $shipping_class_id, or transit options for the $method_inst_id.
				 */
				$type_opts = SucomUtil::preg_grep_keys( '/^' . $opt_key_pre . '_/', $opts );

				foreach ( $type_opts as $opt_key => $val ) {

					if ( '' !== $val ) {	// Allow for 0.

						/*
						 * Create delivery time option key name from the handling / transit options key prefix.
						 *
						 * Example replace 'wcsdt_handling_c136_unit_code' to 'handling_unit_code'.
						 */
						$time_key = str_replace( $opt_key_pre, $type_key, $opt_key );

						/*
						 * Example $time_key = 'handling_minimum', 'handling_maximum', 'handling_unit_code', 
						 */
						$sdt_opts[ $time_key ] = $val;

						/*
						 * If this is a unit code, add its name and unit text.
						 */
						if ( false !== strpos( $time_key, '_unit_code' ) ) {

							switch ( $val ) {

								case 'HUR':

									/*
									 * Example $type_key = 'handling'
									 */
									 $sdt_opts[ $type_key . '_unit_text' ] = 'h';
									 $sdt_opts[ $type_key . '_name' ]      = 'Hours';

									 break;

								case 'DAY':

									 $sdt_opts[ $type_key . '_unit_text' ] = 'd';
									 $sdt_opts[ $type_key . '_name' ]      = 'Days';

									 break;
							}

						/*
						 * Example $time_key = 'handling_minimum', 'handling_maximum'.
						 */
						} elseif ( false !== strpos( $time_key, '_minimum' ) || false !== strpos( $time_key, '_maximum' ) ) {

							$sdt_opts[ $time_key ] = round( $sdt_opts[ $time_key ] );
						}
					}
				}
			}

			return $sdt_opts;
		}
	}
}
