<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2020-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoWcsdtConfig' ) ) {

	class WpssoWcsdtConfig {

		public static $cf = array(
			'plugin' => array(
				'wpssowcsdt' => array(			// Plugin acronym.
					'version'     => '3.2.0',	// Plugin version.
					'opt_version' => '1',		// Increment when changing default option values.
					'short'       => 'WPSSO WCSDT',	// Short plugin name.
					'name'        => 'WPSSO Shipping Delivery Time for WooCommerce SEO',
					'desc'        => 'Shipping delivery time estimates for WooCommerce shipping zones, methods, and classes.',
					'slug'        => 'wpsso-wc-shipping-delivery-time',
					'base'        => 'wpsso-wc-shipping-delivery-time/wpsso-wc-shipping-delivery-time.php',
					'update_auth' => '',		// No premium version.
					'text_domain' => 'wpsso-wc-shipping-delivery-time',
					'domain_path' => '/languages',

					/*
					 * Required plugin and its version.
					 */
					'req' => array(
						'wpsso' => array(
							'name'          => 'WPSSO Core',
							'home'          => 'https://wordpress.org/plugins/wpsso/',
							'plugin_class'  => 'Wpsso',
							'version_const' => 'WPSSO_VERSION',
							'min_version'   => '18.10.0',
						),
						'woocommerce' => array(
							'name'          => 'WooCommerce',
							'home'          => 'https://wordpress.org/plugins/woocommerce/',
							'plugin_class'  => 'WooCommerce',
							'version_const' => 'WC_VERSION',
							'min_version'   => '6.0.0',
						),
					),

					/*
					 * URLs or relative paths to plugin banners and icons.
					 */
					'assets' => array(

						/*
						 * Icon image array keys are '1x' and '2x'.
						 */
						'icons' => array(
							'1x' => 'images/icon-128x128.png',
							'2x' => 'images/icon-256x256.png',
						),
					),

					/*
					 * Library files loaded and instantiated by WPSSO.
					 */
					'lib' => array(
						'submenu' => array(
							'wc-shipping' => 'WC Shipping',
						),
					),

					/*
					 * Declare compatibility with WooCommerce HPOS.
					 *
					 * See https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book.
					 */
					'wc_compat' => array(
						'custom_order_tables',
					),
				),
			),
		);

		public static function get_version( $add_slug = false ) {

			$info =& self::$cf[ 'plugin' ][ 'wpssowcsdt' ];

			return $add_slug ? $info[ 'slug' ] . '-' . $info[ 'version' ] : $info[ 'version' ];
		}

		public static function set_constants( $plugin_file ) {

			if ( defined( 'WPSSOWCSDT_VERSION' ) ) {	// Define constants only once.

				return;
			}

			$info =& self::$cf[ 'plugin' ][ 'wpssowcsdt' ];

			/*
			 * Define fixed constants.
			 */
			define( 'WPSSOWCSDT_FILEPATH', $plugin_file );
			define( 'WPSSOWCSDT_PLUGINBASE', $info[ 'base' ] );	// Example: wpsso-wc-shipping-delivery-time/wpsso-wc-shipping-delivery-time.php.
			define( 'WPSSOWCSDT_PLUGINDIR', trailingslashit( realpath( dirname( $plugin_file ) ) ) );
			define( 'WPSSOWCSDT_PLUGINSLUG', $info[ 'slug' ] );	// Example: wpsso-wc-shipping-delivery-time.
			define( 'WPSSOWCSDT_URLPATH', trailingslashit( plugins_url( '', $plugin_file ) ) );
			define( 'WPSSOWCSDT_VERSION', $info[ 'version' ] );
		}

		public static function require_libs( $plugin_file ) {

			require_once WPSSOWCSDT_PLUGINDIR . 'lib/filters.php';
			require_once WPSSOWCSDT_PLUGINDIR . 'lib/register.php';
			require_once WPSSOWCSDT_PLUGINDIR . 'lib/woocommerce.php';

			add_filter( 'wpssowcsdt_load_lib', array( __CLASS__, 'load_lib' ), 10, 3 );
		}

		public static function load_lib( $success = false, $filespec = '', $classname = '' ) {

			if ( false !== $success ) {

				return $success;
			}

			if ( ! empty( $classname ) ) {

				if ( class_exists( $classname ) ) {

					return $classname;
				}
			}

			if ( ! empty( $filespec ) ) {

				$file_path = WPSSOWCSDT_PLUGINDIR . 'lib/' . $filespec . '.php';

				if ( file_exists( $file_path ) ) {

					require_once $file_path;

					if ( empty( $classname ) ) {

						return SucomUtil::sanitize_classname( 'wpssowcsdt' . $filespec, $allow_underscore = false );
					}

					return $classname;
				}
			}

			return $success;
		}
	}
}
