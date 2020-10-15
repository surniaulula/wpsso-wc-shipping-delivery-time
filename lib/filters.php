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
				'wc_shipping_delivery_time' => 4,
			) );
		}

		public function filter_wc_shipping_delivery_time( $delivery_time_opts, $zone_id, $method_inst_id, $shipping_class_id ) {

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
			) as $opt_pre => $opt_key ) {

				foreach ( array( 'min_days', 'max_days' ) as $opt_suffix ) {

					if ( ! empty( $opts[ $opt_pre ][ $opt_key . '-' . $opt_suffix ] ) ) {

						$delivery_time_opts[ $opt_pre . '_' . $opt_suffix ] = $opts[ $opt_pre ][ $opt_key . '-' . $opt_suffix ];
					}
				}

				if ( isset( $delivery_time_opts[ $opt_pre . '_min_days' ] ) && isset( $delivery_time_opts[ $opt_pre . '_max_days' ] ) &&
					$delivery_time_opts[ $opt_pre . '_min_days' ] === $delivery_time_opts[ $opt_pre . '_max_days' ] ) {

					$delivery_time_opts[ $opt_pre . '_days' ] = $delivery_time_opts[ $opt_pre . '_min_days' ];

					unset( $delivery_time_opts[ $opt_pre . '_min_days' ], $delivery_time_opts[ $opt_pre . '_max_days' ] );
				}
			}

			return $delivery_time_opts;
		}
	}
}
