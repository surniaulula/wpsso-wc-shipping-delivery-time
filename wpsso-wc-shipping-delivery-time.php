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
 * Description: Shipping delivery time estimates for WooCommerce shipping zones, methods, and classes.
 * Requires PHP: 7.0
 * Requires At Least: 4.5
 * Tested Up To: 5.7
 * WC Tested Up To: 5.1.0
 * Version: 2.2.1
 *
 * Version Numbering: {major}.{minor}.{bugfix}[-{stage}.{level}]
 *
 *      {major}         Major structural code changes / re-writes or incompatible API changes.
 *      {minor}         New functionality was added or improved in a backwards-compatible manner.
 *      {bugfix}        Backwards-compatible bug fixes or small improvements.
 *      {stage}.{level} Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).
 *
 * Copyright 2020-2021 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoAddOn' ) ) {

	require_once dirname( __FILE__ ) . '/lib/abstracts/add-on.php';	// WpssoAddOn class.
}

if ( ! class_exists( 'WpssoWcsdt' ) ) {

	class WpssoWcsdt extends WpssoAddOn {

		public $filters;	// WpssoWcsdtFilters class object.
		public $wc;		// WpssoWcsdtWooCommerce class object.

		protected $p;	// Wpsso class object.

		private static $instance = null;	// WpssoWcsdt class object.

		public function __construct() {

			parent::__construct( __FILE__, __CLASS__ );
		}

		public static function &get_instance() {

			if ( null === self::$instance ) {

				self::$instance = new self;
			}

			return self::$instance;
		}

		public function init_textdomain() {

			load_plugin_textdomain( 'wpsso-wc-shipping-delivery-time', false, 'wpsso-wc-shipping-delivery-time/languages/' );
		}

		/**
		 * $is_admin, $doing_ajax, and $doing_cron available since WPSSO Core v8.8.0.
		 */
		public function init_objects( $is_admin = false, $doing_ajax = false, $doing_cron = false ) {

			$this->p =& Wpsso::get_instance();

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( $this->get_missing_requirements() ) {	// Returns false or an array of missing requirements.

				return;	// Stop here.
			}

			$this->filters = new WpssoWcsdtFilters( $this->p, $this );
			$this->wc      = new WpssoWcsdtWooCommerce( $this->p, $this );
		}
	}

	WpssoWcsdt::get_instance();	// Self-instantiate.
}
