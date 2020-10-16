<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2020 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoWcsdtFilters' ) ) {

	class WpssoWcsdtFilters {

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

			$this->p->util->add_plugin_filters( $this, array( 
				'wc_shipping_delivery_time' => 5,
			) );
		}

		public function filter_wc_shipping_delivery_time( $delivery_time_opts, $zone_id, $method_inst_id, $shipping_class_id, $parent_url ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			static $opts = null;

			if ( null === $opts ) {

				$opts[ 'handling' ] = get_option( 'wcsdt_handling_time', array() );
				$opts[ 'transit' ]  = get_option( 'wcsdt_transit_time', array() );
			}

			foreach ( array(
				'handling' => 'c' . $shipping_class_id,
				'transit'  => 'm' . $method_inst_id,
			) as $opts_id => $opt_key_pre ) {

				$delivery_time_opts[ $opts_id . '_rel' ] = $parent_url;

				foreach ( SucomUtil::preg_grep_keys( '/^' . $opt_key_pre . '_/', $opts[ $opts_id ] ) as $opt_key => $val ) {

					if ( '' !== $val ) {	// Allow for 0.

						/**
						 * Create and delivery time option key name from the handling / transit options key prefix.
						 *
						 * Example: 'c136_minimum' to 'handling_minimum'.
						 */
						$time_key = str_replace( $opt_key_pre, $opts_id, $opt_key );

						$delivery_time_opts[ $time_key ] = $val;

						/**
						 * Add the name and unit text for the unit codes we use.
						 */
						if ( false !== strpos( $time_key, '_unit_code' ) ) {

							switch ( $val ) {

								case 'HUR':

									 $delivery_time_opts[ $opts_id . '_unit_text' ] = 'h';
									 $delivery_time_opts[ $opts_id . '_name' ]      = 'Hours';

									 break;

								case 'DAY':

									 $delivery_time_opts[ $opts_id . '_unit_text' ] = 'd';
									 $delivery_time_opts[ $opts_id . '_name' ]      = 'Days';

									 break;
							}
						}
					}
				}
			}

			return $delivery_time_opts;
		}
	}
}
