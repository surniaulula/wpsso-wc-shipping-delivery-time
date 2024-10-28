=== WPSSO Shipping Delivery Time for WooCommerce SEO ===
Plugin Name: WPSSO Shipping Delivery Time for WooCommerce SEO
Plugin Slug: wpsso-wc-shipping-delivery-time
Text Domain: wpsso-wc-shipping-delivery-time
Domain Path: /languages
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.txt
Assets URI: https://surniaulula.github.io/wpsso-wc-shipping-delivery-time/assets/
Tags: schema, shippingDetails, OfferShippingDetails, ShippingDeliveryTime, deliveryTime
Contributors: jsmoriss
Requires Plugins: wpsso, woocommerce
Requires PHP: 7.4.33
Requires At Least: 5.9
Tested Up To: 6.7.0
WC Tested Up To: 9.3.3
Stable Tag: 3.2.0

Shipping delivery time estimates for WooCommerce shipping zones, methods, and classes.

== Description ==

<!-- about -->

**Adding shipping details to your Schema Product markup is important if you offer free or low-cost shipping options, as this will make your products more appealing in Google search results.**

**The WPSSO Shipping Delivery Time for WooCommerce SEO (WCSDT) add-on integrates and extends the *WooCommerce &gt; Settings &gt; Shipping* page with additional shipping delivery times:**

* Choose to show handling and packaging times in the cart and checkout pages.
* Choose to show transit times in the cart and checkout pages.
* Select shipping department hours (cutoff time for new orders and business hours).
* Select min / max handling times for WooCommerce shipping classes.
* Select min / max transit times for WooCommerce shipping zones and methods.
* Increment handling and transit times in half-hours and/or half-days.

The WPSSO WCSDT add-on provides this additional shipping information to the [WPSSO Core plugin](https://wordpress.org/plugins/wpsso/) for complete Schema **shippingDetails**, **OfferShippingDetails**, and **ShippingDeliveryTime** markup.

<!-- /about -->

<h3>WPSSO Core Required</h3>

WPSSO Shipping Delivery Time for WooCommerce SEO (WPSSO WCSDT) is an add-on for [WooCommerce](https://wordpress.org/plugins/woocommerce/) and the [WPSSO Core plugin](https://wordpress.org/plugins/wpsso/).

== Installation ==

<h3 class="top">Install and Uninstall</h3>

* [Install the WPSSO Shipping Delivery Time for WooCommerce SEO add-on](https://wpsso.com/docs/plugins/wpsso-wc-shipping-delivery-time/installation/install-the-plugin/).
* [Uninstall the WPSSO Shipping Delivery Time for WooCommerce SEO add-on](https://wpsso.com/docs/plugins/wpsso-wc-shipping-delivery-time/installation/uninstall-the-plugin/).

== Frequently Asked Questions ==

<h3 class="top">Frequently Asked Questions</h3>

* [How do I exclude shipping details from the Schema markup?](https://wpsso.com/docs/plugins/wpsso-wc-shipping-delivery-time/faqs/how-do-i-exclude-offer-shipping-details-from-the-markup/)

== Screenshots ==

01. The WooCommerce shipping options page with additional shipping delivery times.

== Changelog ==

<h3 class="top">Version Numbering</h3>

Version components: `{major}.{minor}.{bugfix}[-{stage}.{level}]`

* {major} = Major structural code changes and/or incompatible API changes (ie. breaking changes).
* {minor} = New functionality was added or improved in a backwards-compatible manner.
* {bugfix} = Backwards-compatible bug fixes or small improvements.
* {stage}.{level} = Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).

<h3>Standard Edition Repositories</h3>

* [GitHub](https://surniaulula.github.io/wpsso-wc-shipping-delivery-time/)
* [WordPress.org](https://plugins.trac.wordpress.org/browser/wpsso-wc-shipping-delivery-time/)

<h3>Development Version Updates</h3>

<p><strong>WPSSO Core Premium edition customers have access to development, alpha, beta, and release candidate version updates:</strong></p>

<p>Under the SSO &gt; Update Manager settings page, select the "Development and Up" (for example) version filter for the WPSSO Core plugin and/or its add-ons. When new development versions are available, they will automatically appear under your WordPress Dashboard &gt; Updates page. You can reselect the "Stable / Production" version filter at any time to reinstall the latest stable version.</p>

<p><strong>WPSSO Core Standard edition users (ie. the plugin hosted on WordPress.org) have access to <a href="https://wordpress.org/plugins/wpsso-wc-shipping-delivery-time/advanced/">the latest development version under the Advanced Options section</a>.</strong></p>

<h3>Changelog / Release Notes</h3>

**Version 3.2.0 (2024/08/25)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Changed the main instantiation action hook from 'init_objects' to 'init_objects_preloader'.
* **Requires At Least**
	* PHP v7.4.33.
	* WordPress v5.9.
	* WPSSO Core v18.10.0.
	* WooCommerce v6.0.0.

== Upgrade Notice ==

= 3.2.0 =

(2024/08/25) Changed the main instantiation action hook from 'init_objects' to 'init_objects_preloader'.

