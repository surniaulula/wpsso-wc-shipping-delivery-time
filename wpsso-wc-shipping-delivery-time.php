<?php
/**
 * Plugin Name: WPSSO Shipping Delivery Time for WooCommerce
 * Plugin Slug: wpsso-wc-shipping-delivery-time
 * Text Domain: wpsso-wc-shipping-delivery-time
 * Domain Path: /languages
 * Plugin URI: https://wpsso.com/extend/plugins/wpsso-wc-shipping-delivery-time/
 * Assets URI: https://jsmoriss.github.io/wpsso-wc-shipping-delivery-time/assets/
 * Author: JS Morisset
 * Author URI: https://wpsso.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Description: Shipping Delivery Time Estimates for WooCommerce Shipping Zones, Methods, and Classes.
 * Requires PHP: 5.6
 * Requires At Least: 4.4
 * Tested Up To: 5.5.1
 * WC Tested Up To: 4.5.2
 * Version: 1.0.0-dev.4
 *
 * Version Numbering: {major}.{minor}.{bugfix}[-{stage}.{level}]
 *
 *      {major}         Major structural code changes / re-writes or incompatible API changes.
 *      {minor}         New functionality was added or improved in a backwards-compatible manner.
 *      {bugfix}        Backwards-compatible bug fixes or small improvements.
 *      {stage}.{level} Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).
 *
 * Copyright 2020 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoAddOn' ) ) {

	require_once dirname( __FILE__ ) . '/lib/abstracts/add-on.php';	// WpssoAddOn class.
}

if ( ! class_exists( 'WpssoWcsdt' ) ) {

	class WpssoWcsdt extends WpssoAddOn {

		public $filters;	// WpssoWcsdtFilters class.
		public $wc;		// WpssoWcsdtWooCommerce class.

		protected $p;

		private static $instance = null;

		public function __construct() {

			parent::__construct( __FILE__, __CLASS__ );
		}

		public static function &get_instance() {

			if ( null === self::$instance ) {

				self::$instance = new self;
			}

			return self::$instance;
		}

		public function init_textdomain( $debug_enabled = false ) {

			static $local_cache = null;

			if ( null === $local_cache || $debug_enabled ) {

				$local_cache = 'wpsso-wc-shipping-delivery-time';

				load_plugin_textdomain( 'wpsso-wc-shipping-delivery-time', false, 'wpsso-wc-shipping-delivery-time/languages/' );
			}

			return $local_cache;
		}

		public function init_objects() {

			$this->p =& Wpsso::get_instance();

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( $this->get_missing_requirements() ) {	// Returns false or an array of missing requirements.

				return;	// Stop here.
			}

			$this->filters = new WpssoWcsdtFilters( $this->p );
			$this->wc      = new WpssoWcsdtWooCommerce( $this->p );
		}
	}

	WpssoWcsdt::get_instance();	// Self-instantiate.
}
