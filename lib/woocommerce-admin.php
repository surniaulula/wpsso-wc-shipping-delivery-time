<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2020-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoWcsdtWooCommerceAdmin' ) ) {

	class WpssoWcsdtWooCommerceAdmin {

		private $p;	// Wpsso class object.
		private $a;	// WpssoWcsdt class object.
		private $form;	// SucomForm class object.

		/*
		 * Instantiated by WpssoWcsdtWooCommerce->__construct().
		 */
		public function __construct( &$plugin ) {

			$this->p =& $plugin;
			$this->a =& $addon;

			add_filter( 'woocommerce_get_sections_shipping', array( $this, 'add_sections' ), 10, 1 );
			add_filter( 'woocommerce_get_settings_shipping', array( $this, 'add_settings' ), 10, 2 );

			add_action( 'woocommerce_settings_wcsdt_options_end', array( $this, 'show_stylesheet' ), 10 );
			add_action( 'woocommerce_settings_wcsdt_options_end', array( $this, 'show_shipping_dept' ), 20 );
			add_action( 'woocommerce_settings_wcsdt_options_end', array( $this, 'show_handling_time' ), 30 );
			add_action( 'woocommerce_settings_wcsdt_options_end', array( $this, 'show_transit_time' ), 40 );
			add_action( 'woocommerce_settings_save_shipping', array( $this, 'save_settings' ) );
		}

		public function add_sections( $sections ) {

			SucomUtil::add_after_key( $sections, 'classes', 'wcsdt', __( 'Shipping delivery times', 'wpsso-wc-shipping-delivery-time' ) );

			return $sections;
		}

		public function add_settings( $settings, $current_section ) {

			if ( 'wcsdt' !== $current_section ) {

				return $settings;
			}

			wp_enqueue_script( 'sucom-metabox' );

			$def_opts = $this->p->opt->get_defaults();	// Passed by reference.

			$this->form = new SucomForm( $this->p, WPSSO_OPTIONS_NAME, $this->p->options, $def_opts, $this->p->id );

			/*
			 * See woocommerce/includes/admin/settings/class-wc-settings-shipping.php for examples.
			 */
			$settings = array(
				array(
					'title' => __( 'Shipping delivery times', 'wpsso-wc-shipping-delivery-time' ),
					'id'    => 'wcsdt_title',
					'type'  => 'title',
				),
				array(
					'title'         => __( 'Show shipping estimates', 'wpsso-wc-shipping-delivery-time' ),

					/*
					 * Start of 'Show delivery estimates' checkbox group.
					 */
					'desc'          => __( 'Show handling and packaging times in the cart and checkout pages.', 'wpsso-wc-shipping-delivery-time' ),
					'id'            => 'wcsdt_show_handling_times',	// Option name with yes/no value.
					'type'          => 'checkbox',
					'default'       => 'yes',
					'checkboxgroup' => 'start',
				),
				array(
					'desc'          => __( 'Show transit times in the cart and checkout pages.', 'wpsso-wc-shipping-delivery-time' ),
					'id'            => 'wcsdt_show_transit_times',	// Option name with yes/no value.
					'type'          => 'checkbox',
					'default'       => 'yes',
					'checkboxgroup' => 'end',
				),
				array(
					'id'   => 'wcsdt_options',
					'type' => 'sectionend',
				),
			);

			return $settings;
		}

		public function show_stylesheet() {

			?><style type="text/css">
				.woocommerce table.form-table table.shipdept tr.first-row td {
					padding-top:0;
				}
				.woocommerce table.form-table .shipdept-day,
				.woocommerce table.form-table .shipping-class,
				.woocommerce table.form-table .shipping-method {
					width:10em;
				}
				.woocommerce table.form-table .class-description,
				.woocommerce table.form-table .shipping-rate {
					width:16em;
				}
				.woocommerce table.form-table .shipdept-timezone,
				.woocommerce table.form-table .minimum-time,
				.woocommerce table.form-table .maximum-time {
					width:auto;
				}
				.woocommerce table.form-table .minimum-time input[type="number"],
				.woocommerce table.form-table .maximum-time input[type="number"] {
					width:82px;
				}
				.woocommerce table.form-table .shipdept-time,
				.woocommerce table.form-table .unit-of-time {
					width:auto;
				}
				.woocommerce table.form-table .shipdept-timezone select {
					width:220px;
				}
				.woocommerce table.form-table .shipdept-time select,
				.woocommerce table.form-table .unit-of-time select {
					width:82px;
				}
				.woocommerce table.form-table .shipdept-time-and {
					padding:0;
				}
			</style><?php
		}

		public function show_shipping_dept() {

			echo '<tr valign="top">' . "\n";
			echo '<th scope="row" class="titledesc"><label>' . esc_html__( 'Shipping department hours', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The operational details for the shipping department.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</label></th>' . "\n";
			echo '<td class="forminp">' . "\n";

			$this->show_shipping_dept_table();

			echo '</td><!-- .forminp -->' . "\n";
			echo '</tr>' . "\n";
			echo '<tr valign="top">' . "\n";
			echo '<th></th>' . "\n";
			echo '<td class="forminp">' . "\n";

			$this->show_shipping_dept_hours_table();

			echo '</td><!-- .forminp -->' . "\n";
			echo '</tr>' . "\n";
		}

		public function show_shipping_dept_table() {

			echo '<table class="shipdept">' . "\n";

			echo '<tr class="first-row">' . "\n";
			echo '<td align="right">' . _x( 'Shipping department timezone', 'option label', 'wpsso-wc-shipping-delivery-time' ) . '</td>' . "\n";
			echo '<td colspan="3" class="shipdept-timezone">' . $this->form->get_select_timezone( 'wcsdt_shipdept_timezone',
				$css_class = 'timezone' ) . '</td>' . "\n";
			echo '</tr>' . "\n";

			echo '<tr>' . "\n";
			echo '<td align="right"><p>' . __( 'Closes every midday between', 'wpsso-wc-shipping-delivery-time' ) . '</p></td>' . "\n";
			echo '<td class="shipdept-time">' . $this->form->get_select_time_none( 'wcsdt_shipdept_midday_close', $css_class = 'time-hh-mm' ) . '</td>' . "\n";
			echo '<td class="shipdept-time-and"><p>' . __( 'and', 'wpsso-wc-shipping-delivery-time' ) . '</p></td>' .  "\n";
			echo '<td class="shipdept-time">' . $this->form->get_select_time_none( 'wcsdt_shipdept_midday_open', $css_class = 'time-hh-mm' ) . '</td>' . "\n";
			echo '</tr>' . "\n";

			echo '<tr>' . "\n";
			echo '<td align="right"><p>' . __( 'Cutoff time for new orders', 'wpsso-wc-shipping-delivery-time' ) . '</p></td>' . "\n";
			echo '<td class="shipdept-time">' . $this->form->get_select_time_none( 'wcsdt_shipdept_cutoff', $css_class = 'time-hh-mm' ) . '</td>' . "\n";
			echo '</tr>' . "\n";

			echo '</table>' . "\n";
		}

		public function show_shipping_dept_hours_table() {

			$weekdays =& $this->p->cf[ 'form' ][ 'weekdays' ];

			echo '<table class="wc_shipping wp-list-table" cellspacing="0">' . "\n";
			echo '<thead>' . "\n";
			echo '<tr style="background:#e9e9e9;">' . "\n";
			echo '<th colspan="3" style="text-align:center; border:1px solid #e1e1e1;">' .
				_x( 'Shipping department business hours', 'option label', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";
			echo '</tr>' . "\n";
			echo '<tr>' . "\n";
			echo '<th class="shipdept-day">' . esc_html__( 'Day', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";
			echo '<th class="shipdept-time">' . esc_html__( 'Opens at', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";
			echo '<th class="shipdept-time">' . esc_html__( 'Closes at', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";
			echo '</tr>' . "\n";
			echo '</thead>' . "\n";

			/*
			 * Example $weekdays = array(
			 *	'sunday'         => 'Sunday',
			 *	'monday'         => 'Monday',
			 *	'tuesday'        => 'Tuesday',
			 *	'wednesday'      => 'Wednesday',
			 *	'thursday'       => 'Thursday',
			 *	'friday'         => 'Friday',
			 *	'saturday'       => 'Saturday',
			 *	'publicholidays' => 'Public Holidays',
			 * );
			 */
			foreach ( $weekdays as $day_name => $day_label ) {

				$day_opt_pre   = 'wcsdt_shipdept_day_' . $day_name;
				$open_opt_key  = $day_opt_pre . '_open';
				$close_opt_key = $day_opt_pre . '_close';

				// translators: Please ignore - translation uses a different text domain.
				$day_label_transl = _x( $day_label, 'option value', 'wpsso' );

				echo '<tr>' . "\n";
				echo '<td class="shipdept-day"><p>' . $day_label_transl . '</p></td>' . "\n";
				echo '<td class="shipdept-time">' . $this->form->get_select_time_none( $open_opt_key, $css_class = 'time-hh-mm' ) . '</td>' . "\n";
				echo '<td class="shipdept-time">' . $this->form->get_select_time_none( $close_opt_key, $css_class = 'time-hh-mm' ) . '</td>' . "\n";
				echo '</tr>' . "\n";
			}

			echo '</table><!-- .wc_shipping.wp-list-table -->' . "\n";
		}

		public function show_handling_time() {

			echo '<tr valign="top">' . "\n";
			echo '<th scope="row" class="titledesc"><label>' . esc_html__( 'Shipping handling times', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The typical delay between the receipt of an order and the goods leaving the warehouse.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</label></th>' . "\n";
			echo '<td class="forminp">' . "\n";

			$this->show_handling_time_table();

			echo '</td><!-- .forminp -->' . "\n";
			echo '</tr>' . "\n";
		}

		private function show_handling_time_table() {

			$shipping_classes     = WC()->shipping()->get_shipping_classes();
			$classes_admin_url    = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' );
			$classes_label_transl = '<a href="' . $classes_admin_url . '">' . esc_html__( 'Shipping classes', 'wpsso-wc-shipping-delivery-time' ) . '</a>';

			echo '<table class="wc_shipping wp-list-table" cellspacing="0">' . "\n";
			echo '<thead>' . "\n";
			echo '<tr style="background:#e9e9e9;">' . "\n";
			echo '<th colspan="5" style="text-align:center; border:1px solid #e1e1e1;">' . $classes_label_transl . '</th>' . "\n";
			echo '</tr>' . "\n";
			echo '<tr>' . "\n";
			echo '<th class="shipping-class">' . esc_html__( 'Shipping class', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";
			echo '<th class="class-description">' . esc_html__( 'Class description', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";
			echo '<th class="minimum-time">' . esc_html__( 'Minimum time', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The estimated minimum handling and packaging time. Can be left blank.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</th>' . "\n";
			echo '<th class="maximum-time">' . esc_html__( 'Maximum time', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The estimated maximum handling and packaging time. Can be left blank.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</th>' . "\n";
			echo '<th class="unit-of-time">' . esc_html__( 'Unit of time', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";
			echo '</tr>' . "\n";
			echo '</thead>' . "\n";
			echo '<tbody>' . "\n";

			foreach ( $shipping_classes as $shipping_class_obj ) {

				$shipping_class_id   = $shipping_class_obj->term_id;
				$shipping_class_name = $shipping_class_obj->name;
				$shipping_class_desc = $shipping_class_obj->description;

				$this->show_handling_time_table_rows( $shipping_class_id, $shipping_class_name, $shipping_class_desc );

			}

			$shipping_class_id   = 0;
			// translators: Please ignore - translation uses a different text domain.
			$shipping_class_name = __( 'No shipping class', 'woocommerce' );
			$shipping_class_desc = __( 'Products without a shipping class', 'wpsso-wc-shipping-delivery-time' ) ;

			$this->show_handling_time_table_rows( $shipping_class_id, $shipping_class_name, $shipping_class_desc );

			echo '</tbody>' . "\n";
			echo '</table><!-- .wc_shipping.wp-list-table -->' . "\n";
		}

		private function show_handling_time_table_rows( $shipping_class_id, $shipping_class_name, $shipping_class_desc ) {

			if ( isset( $this->p->options[ 'wcsdt_combined_options' ] ) ) {	// Since WPSSO WCSDT v2.0.0.

				$opts =& $this->p->options;

			} else {	// Check for deprecated WPSSO WCSDT v1 options.

				static $opts = null;

				if ( null === $opts ) {

					$opts = array();

					foreach ( get_option( 'wcsdt_handling_time', array() ) as $key => $val ) {

						$opts[ 'wcsdt_handling_' . $key ] = $val;
					}
				}
			}

			$opt_key_pre  = 'wcsdt_handling_c' . $shipping_class_id;
			$opt_key_min  = $opt_key_pre . '_minimum';
			$opt_key_max  = $opt_key_pre . '_maximum';
			$opt_key_unit = $opt_key_pre . '_unit_code';

			$min_val   = isset( $opts[ $opt_key_min ] ) ? round( $opts[ $opt_key_min ] ) : '';
			$max_val   = isset( $opts[ $opt_key_max ] ) ? round( $opts[ $opt_key_max ] ) : '';
			$unit_code = isset( $opts[ $opt_key_unit ] ) ? $opts[ $opt_key_unit ] : 'DAY';

			echo '<tr>' . "\n";
			echo '<td class="shipping-class">' . $shipping_class_name . '</td>' . "\n";
			echo '<td class="class-description">' . $shipping_class_desc . '</td>' . "\n";
			echo '<td class="minimum-time">';
			echo '<input type="number" step="1" min="0" name="' . WPSSO_OPTIONS_NAME . '[' . $opt_key_min . ']" value="' . $min_val . '"/>';
			echo '</td>' . "\n";
			echo '<td class="maximum-time">';
			echo '<input type="number" step="1" min="0" name="' . WPSSO_OPTIONS_NAME . '[' . $opt_key_max . ']" value="' . $max_val . '"/>';
			echo '</td>' . "\n";
			echo '<td class="unit-of-time">' . "\n";

			$this->show_opt_key_unit_select( $opt_key_unit, $unit_code );

			echo '</td>' . "\n";
			echo '</tr>' . "\n";
		}

		public function show_transit_time() {

			echo '<tr valign="top">' . "\n";
			echo '<th scope="row" class="titledesc"><label>' . esc_html__( 'Shipping transit times', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The typical delay between the goods leaving the warehouse and reaching the customer.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</label></th>' . "\n";
			echo '<td class="forminp">' . "\n";

			$this->show_transit_time_table();

			echo '</td><!-- .forminp -->' . "\n";
			echo '</tr>' . "\n";
		}

		private function show_transit_time_table() {

			echo '<table class="wc_shipping wp-list-table" cellspacing="0">' . "\n";

			$zones = WC_Shipping_Zones::get_zones( $context = 'admin' );

			foreach ( $zones as $zone_id => $zone ) {

				$zone_obj          = WC_Shipping_Zones::get_zone( $zone_id );
				$zone_methods      = $zone_obj->get_shipping_methods( $enabled_only = true, $context = 'admin' );
				$zone_name         = $zone_obj->get_zone_name( $context = 'admin' );
				$zone_admin_url    = admin_url( 'admin.php?page=wc-settings&tab=shipping&zone_id=' . $zone_id );
				$zone_label_transl = '<a href="' . $zone_admin_url . '">' . esc_html( sprintf( __( '%s shipping zone',
					'wpsso-wc-shipping-delivery-time' ), $zone_name ) ) . '</a>';

				$this->show_transit_time_table_rows( $zone_label_transl, $zone_id, $zone_methods );
			}

			/*
			 * Locations not covered by your other zones.
			 */
			$world_zone_id      = 0;
			$world_zone_obj     = WC_Shipping_Zones::get_zone( $world_zone_id );	// Locations not covered by your other zones.
			$world_zone_methods = $world_zone_obj->get_shipping_methods();

			// translators: Please ignore - translation uses a different text domain.
			$zone_name         = $world_zone_obj->get_zone_name( $context = 'admin' );
			$zone_admin_url    = admin_url( 'admin.php?page=wc-settings&tab=shipping&zone_id=' . $world_zone_id );
			$zone_label_transl = '<a href="' . $zone_admin_url . '">' . esc_html( $zone_name ) . '</a> ';

			$this->show_transit_time_table_rows( $zone_label_transl, $world_zone_id, $world_zone_methods );

			echo '</table><!-- .wc_shipping.wp-list-table -->' . "\n";
		}

		private function show_transit_time_table_rows( $zone_label_transl, $zone_id, $shipping_methods ) {

			if ( isset( $this->p->options[ 'wcsdt_combined_options' ] ) ) {	// Since WPSSO WCSDT v2.0.0.

				$opts =& $this->p->options;

			} else {	// Check for deprecated WPSSO WCSDT v1 options.

				static $opts = null;

				if ( null === $opts ) {

					$opts = array();

					foreach ( get_option( 'wcsdt_transit_time', array() ) as $key => $val ) {

						$opts[ 'wcsdt_transit_' . $key ] = $val;
					}
				}
			}

			echo '<thead>' . "\n";
			echo '<tr style="background:#e9e9e9;">' . "\n";
			echo '<th colspan="5" style="text-align:center; border:1px solid #e1e1e1;">' . $zone_label_transl . '</th>' . "\n";
			echo '</tr>' . "\n";
			echo '<tr>' . "\n";
			echo '<th class="shipping-method">' . esc_html__( 'Shipping method', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";
			echo '<th class="shipping-rate">' . esc_html__( 'Shipping rate', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";
			echo '<th class="minimum-time">' . esc_html__( 'Minimum time', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The estimated minimum transit time. Can be left blank.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</th>' . "\n";
			echo '<th class="maximum-time">' . esc_html__( 'Maximum time', 'wpsso-wc-shipping-delivery-time' ) .
				wc_help_tip( __( 'The estimated maximum transit time. Can be left blank.',
					'wpsso-wc-shipping-delivery-time' ) ) . '</th>' . "\n";
			echo '<th class="unit-of-time">' . esc_html__( 'Unit of time', 'wpsso-wc-shipping-delivery-time' ) . '</th>' . "\n";
			echo '</tr>' . "\n";
			echo '</thead>' . "\n";
			echo '<tbody>' . "\n";

			if ( empty( $shipping_methods ) ) {

				echo '<tr>';
				echo '<td colspan="5" style="text-align:center;">' .
					// translators: Please ignore - translation uses a different text domain.
					__( 'No shipping methods offered to this zone.', 'woocommerce' ) . '</td>' . "\n";
				echo '</tr>' . "\n";

			} else {

				foreach ( $shipping_methods as $method_inst_id => $method_obj ) {

					$method_url     = admin_url( 'admin.php?page=wc-settings&tab=shipping&instance_id=' . $method_inst_id );
					$method_rate_id = $method_obj->get_rate_id();
					$method_name    = $method_obj->get_title();
					$method_data    = $method_obj->instance_settings;

					$rate_ids  = explode( ':', $method_rate_id );
					$rate_type = reset( $rate_ids );

					if ( 'local_pickup' === $rate_type ) {	// Pickup is not a shipping method.

						continue;
					}

					$opt_key_pre  = 'wcsdt_transit_m' . $method_inst_id;
					$opt_key_min  = $opt_key_pre . '_minimum';
					$opt_key_max  = $opt_key_pre . '_maximum';
					$opt_key_unit = $opt_key_pre . '_unit_code';

					$min_val   = isset( $opts[ $opt_key_min ] ) ? round( $opts[ $opt_key_min ] ) : '';
					$max_val   = isset( $opts[ $opt_key_max ] ) ? round( $opts[ $opt_key_max ] ) : '';
					$unit_code = isset( $opts[ $opt_key_unit ] ) ? $opts[ $opt_key_unit ] : 'DAY';

					echo '<tr>' . "\n";
					echo '<td class="shipping-method">' . $method_name . '</td>' . "\n";
					echo '<td class="shipping-rate">' . $rate_type . '</td>' . "\n";
					echo '<td class="minimum-time">';
					echo '<input type="number" step="1" min="0" name="' . WPSSO_OPTIONS_NAME . '[' . $opt_key_min . ']" value="' . $min_val . '"/>';
					echo '</td>' . "\n";
					echo '<td class="maximum-time">';
					echo '<input type="number" step="1" min="0" name="' . WPSSO_OPTIONS_NAME . '[' . $opt_key_max . ']" value="' . $max_val . '"/>';
					echo '</td>' . "\n";
					echo '<td class="unit-of-time">' . "\n";

					$this->show_opt_key_unit_select( $opt_key_unit, $unit_code );

					echo '</td>' . "\n";
					echo '</tr>' . "\n";
				}
			}

			echo '</tbody>' . "\n";
		}

		private function show_opt_key_unit_select( $opt_key, $unit_code ) {

			echo '<select name="'. WPSSO_OPTIONS_NAME . '[' . $opt_key . ']">' . "\n";

			foreach ( array(
				'HUR' => __( 'Hours', 'wpsso-wc-shipping-delivery-time' ),
				'DAY' => __( 'Days', 'wpsso-wc-shipping-delivery-time' ),
			) as $value => $label ) {

				echo '<option value="' . $value . '"';

				selected( $value, $unit_code, $echo = true );

				echo '>' . $label . '</option>' . "\n";
			}

			echo '</select>' . "\n";
		}

		/*
		 * Action called by WC_Admin_Settings->save() in woocommerce/includes/admin/class-wc-admin-settings.php.
		 */
		public function save_settings() {

			global $current_section;

			if ( 'wcsdt' !== $current_section ) {	// Just in case.

				return;
			}

			$pre_combined_options = empty( $this->p->options[ 'wcsdt_combined_options' ] ) ? false : true;

			$opts = SucomUtil::preg_grep_keys( '/^wcsdt_/', $this->p->options, $invert = true );

			$post_opts = isset( $_POST[ WPSSO_OPTIONS_NAME ] ) ? $_POST[ WPSSO_OPTIONS_NAME ] : array();

			$wcsdt_opts = array();

			if ( is_array( $post_opts ) ) {	// Just in case.

				foreach ( $post_opts as $opt_key => $val ) {

					if ( '' !== $val ) {	// Allow for 0.

						$opt_key = SucomUtil::sanitize_key( $opt_key );	// Just in case.

						$opts[ $opt_key ] = $wcsdt_opts[ $opt_key ] = $val;
					}
				}

				$_POST[ WPSSO_OPTIONS_NAME ] = $wcsdt_opts;
			}

			$opts[ 'wcsdt_combined_options' ] = 1;

			$saved = update_option( WPSSO_OPTIONS_NAME, $opts, $autoload = true );

			$this->p->admin->settings_saved_notice();

			if ( $saved ) {	// Just in case.

				$this->p->options = $opts;

				if ( ! $pre_combined_options ) {	// Remove deprecated WPSSO WCSDT v1 options.

					delete_option( 'wcsdt_handling_time' );

					delete_option( 'wcsdt_transit_time' );
				}
			}
		}
	}
}
