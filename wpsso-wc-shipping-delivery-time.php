<?php
/*
 * Plugin Name: WPSSO Shipping Delivery Time for WooCommerce SEO
 * Plugin Slug: wpsso-wc-shipping-delivery-time
 * Text Domain: wpsso-wc-shipping-delivery-time
 * Domain Path: /languages
 * Plugin URI: https://wpsso.com/extend/plugins/wpsso-wc-shipping-delivery-time/
 * Assets URI: https://jsmoriss.github.io/wpsso-wc-shipping-delivery-time/assets/
 * Author: JS Morisset
 * Author URI: https://wpsso.com/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Description: Shipping delivery time estimates for WooCommerce shipping zones, methods, and classes.
 * Requires Plugins: wpsso, woocommerce
 * Requires PHP: 7.4.33
 * Requires At Least: 5.9
 * Tested Up To: 6.7.0
 * WC Tested Up To: 9.3.3
 * Version: 3.2.0
 *
 * Version Numbering: {major}.{minor}.{bugfix}[-{stage}.{level}]
 *
 *      {major}         Major structural code changes and/or incompatible API changes (ie. breaking changes).
 *      {minor}         New functionality was added or improved in a backwards-compatible manner.
 *      {bugfix}        Backwards-compatible bug fixes or small improvements.
 *      {stage}.{level} Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).
 *
 * Copyright 2020-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoAbstractAddOn' ) ) {

	require_once dirname( __FILE__ ) . '/lib/abstract/add-on.php';
}

if ( ! class_exists( 'WpssoWcsdt' ) ) {

	class WpssoWcsdt extends WpssoAbstractAddOn {

		public $wc;	// WpssoWcsdtWooCommerce class object.

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

		/*
		 * Called by Wpsso->set_objects() which runs at init priority 10.
		 */
		public function init_objects_preloader() {

			$this->p =& Wpsso::get_instance();

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( $this->get_missing_requirements() ) {	// Returns false or an array of missing requirements.

				return;	// Stop here.
			}

			new WpssoWcsdtFilters( $this->p, $this );

			$this->wc = new WpssoWcsdtWooCommerce( $this->p, $this );
		}
	}

	WpssoWcsdt::get_instance();	// Self-instantiate.
}
