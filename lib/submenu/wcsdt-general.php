<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2020-2023 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoWcsdtSubmenuWcsdtGeneral' ) && class_exists( 'WpssoAdmin' ) ) {

	class WpssoWcsdtSubmenuWcsdtGeneral extends WpssoAdmin {

		public function __construct( &$plugin, $id, $name, $lib, $ext ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$this->menu_id   = $id;
			$this->menu_name = $name;
			$this->menu_lib  = $lib;
			$this->menu_ext  = $ext;
		}

		public function show_settings_page() {

			$admin_url = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wcsdt' );

			wp_redirect( $admin_url );
		}
	}
}
