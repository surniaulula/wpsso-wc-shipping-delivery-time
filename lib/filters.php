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

			static $handling_times = null;
			static $transit_times  = null;
		
			if ( null === $transit_times ) {

				$handling_times = get_option( 'wcsdt_transit_time', array() );
				$transit_times  = get_option( 'wcsdt_transit_time', array() );
			}

			foreach ( array( 'min_days', 'max_days' ) as $opt_suffix ) {

				if ( ! empty( $transit_times[ 'm' . $method_inst_id . '-' . $opt_suffix ] ) ) {

					$delivery_time_opts[ 'transit_' . $opt_suffix ] = $transit_times[ 'm' . $method_inst_id . '-' . $opt_suffix ];
				}
			}

			if ( isset( $delivery_time_opts[ 'transit_min_days' ] ) && isset( $delivery_time_opts[ 'transit_max_days' ] ) &&
				$delivery_time_opts[ 'transit_min_days' ] === $delivery_time_opts[ 'transit_max_days' ] ) {

				$delivery_time_opts[ 'transit_days' ] = $delivery_time_opts[ 'transit_min_days' ];

				unset( $delivery_time_opts[ 'transit_min_days' ], $delivery_time_opts[ 'transit_max_days' ] );
			}

			return $delivery_time_opts;
		}
	}
}
