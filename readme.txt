=== Shipping Delivery Time for WooCommerce | WPSSO Add-on ===
Plugin Name: WPSSO Shipping Delivery Time for WooCommerce
Plugin Slug: wpsso-wc-shipping-delivery-time
Text Domain: wpsso-wc-shipping-delivery-time
Domain Path: /languages
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.txt
Assets URI: https://surniaulula.github.io/wpsso-wc-shipping-delivery-time/assets/
Tags: woocommerce, shipping, delivery, schema, schema.org, OfferShippingDetails, deliveryTime, ShippingDeliveryTime
Contributors: jsmoriss
Requires PHP: 5.6
Requires At Least: 4.4
Tested Up To: 5.5.1
WC Tested Up To: 4.6.1
Stable Tag: 1.0.1

Shipping delivery time estimates for WooCommerce shipping zones, methods, and classes.

== Description ==

Extends the **WooCommerce &gt; Settings &gt; Shipping &gt; Shipping options** with additional shipping delivery times:

* Optionally show handling &amp; packaging times under the shipping methods.
* Optionally show transit times in the shipping method label. 
* Select min / max handling times for WooCommerce shipping classes.
* Select min / max transit times for WooCommerce shipping zones and methods.
* Increment handling and transit times in half-hours and/or half-days.
* Provides shipping delivery time information to the [WPSSO Core Premium plugin](https://wpsso.com/extend/plugins/wpsso/) and the [WPSSO Schema JSON-LD Markup add-on](https://wpsso.com/extend/plugins/wpsso-schema-json-ld/free/) for Schema ShippingDeliveryTime markup.

<h3>WPSSO Core Plugin Required</h3>

WPSSO Shipping Delivery Time for WooCommerce (aka WPSSO WCSDT) is an add-on for the [WPSSO Core plugin](https://wpsso.com/extend/plugins/wpsso/free/).

WPSSO Core and its add-ons make sure your content looks great on social sites and in search results, no matter how your URLs are crawled, shared, re-shared, posted, or embedded.

See [better Schema for WooCommerce products](https://wpsso.com/docs/plugins/wpsso/installation/better-schema-for-woocommerce/) for more information on customizing the WPSSO Core plugin, the WPSSO Schema JSON-LD Markup add-on, and the WooCommerce plugin settings for better Schema markup and Google Rich Results.

== Installation ==

<h3 class="top">Install and Uninstall</h3>

* [Install the WPSSO Shipping Delivery Time for WooCommerce add-on](https://wpsso.com/docs/plugins/wpsso-wc-shipping-delivery-time/installation/install-the-plugin/).
* [Uninstall the WPSSO Shipping Delivery Time for WooCommerce add-on](https://wpsso.com/docs/plugins/wpsso-wc-shipping-delivery-time/installation/uninstall-the-plugin/).

== Frequently Asked Questions ==

<h3 class="top">Frequently Asked Questions</h3>

* None.

== Screenshots ==

01. The WooCommerce shipping options page with additional shipping delivery times.

== Changelog ==

<h3 class="top">Version Numbering</h3>

Version components: `{major}.{minor}.{bugfix}[-{stage}.{level}]`

* {major} = Major structural code changes / re-writes or incompatible API changes.
* {minor} = New functionality was added or improved in a backwards-compatible manner.
* {bugfix} = Backwards-compatible bug fixes or small improvements.
* {stage}.{level} = Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).

<h3>Standard Version Repositories</h3>

* [GitHub](https://surniaulula.github.io/wpsso-wc-shipping-delivery-time/)
* [WordPress.org](https://plugins.trac.wordpress.org/browser/wpsso-wc-shipping-delivery-time/)

<h3>Changelog / Release Notes</h3>

**Version 2.0.0-dev.9 (2020/10/24)**

* **New Features**
	* None.
* **Improvements**
	* Moved the "Shipping delivery times" settings to a new WooCommerce Shipping section.
	* Added a "Shipping department hours" section to the "Shipping delivery times" settings.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Added a new lib/woocommerce-admin.php library file.
	* Moved the 'wcsdt_handling_time' and 'wcsdt_transit_time' database options to the WPSSO Core settings array.
* **Requires At Least**
	* PHP v5.6.
	* WordPress v4.4.
	* WPSSO Core v8.8.1.
	* WooCommerce v3.6.4.

**Version 1.0.1 (2020/10/17)**

**Initial release.**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* Fixed backwards compatibility with older 'init_objects' and 'init_plugin' action arguments.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v5.6.
	* WordPress v4.4.
	* WPSSO Core v8.8.1.
	* WooCommerce v3.6.4.

== Upgrade Notice ==

= 2.0.0-dev.9 =

(2020/10/24) Added a "Shipping department hours" section to the "Shipping delivery times" settings.

= 1.0.1 =

(2020/10/17) Initial release.

