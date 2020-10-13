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
				'wc_shipping_delivery_time' => 3,
			) );
		}

		public function filter_wc_shipping_delivery_time( $delivery_time_opts, $zone_id, $method_inst_id ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			static $wcsdt_transit = null;
		
			if ( null === $wcsdt_transit ) {

				$wcsdt_transit = get_option( 'wcsdt_transit', array() );
			}

			$world_zone_id     = 0;
			$have_transit_opts = false;
			$transit_opts      = array( 'z' . $zone_id . '-m' . $method_inst_id );

			if ( $zone_id !== $world_zone_id ) {

				$transit_opts[] = 'z' . $world_zone_id . '-m' . $method_inst_id;
			}

			foreach ( $transit_opts as $transit_prefix ) {

				foreach ( array( 'min_days', 'max_days' ) as $transit_suffix ) {

					if ( ! empty( $wcsdt_transit[ $transit_prefix . '-' . $transit_suffix ] ) ) {

						$have_transit_opts = true;

						$delivery_time_opts[ 'transit_' . $transit_suffix ] = $wcsdt_transit[ $transit_prefix . '-' . $transit_suffix ];
					}
				}

				if ( $have_transit_opts ) {

					if ( isset( $delivery_time_opts[ 'transit_min_days' ] ) && 
						isset( $delivery_time_opts[ 'transit_max_days' ] ) &&
							$delivery_time_opts[ 'transit_min_days' ] === $delivery_time_opts[ 'transit_max_days' ] ) {

						$delivery_time_opts[ 'transit_days' ] = $delivery_time_opts[ 'transit_min_days' ];

						unset( $delivery_time_opts[ 'transit_min_days' ], $delivery_time_opts[ 'transit_max_days' ] );
					}

					break;
				}
			}

			return $delivery_time_opts;
		}
	}
}
