=== WPSSO Shipping Delivery Time for WooCommerce SEO ===
Plugin Name: WPSSO Shipping Delivery Time for WooCommerce SEO
Plugin Slug: wpsso-wc-shipping-delivery-time
Text Domain: wpsso-wc-shipping-delivery-time
Domain Path: /languages
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.txt
Assets URI: https://surniaulula.github.io/wpsso-wc-shipping-delivery-time/assets/
Tags: woocommerce, schema, shipping, shippingDetails, OfferShippingDetails, ShippingDeliveryTime, deliveryTime, delivery, schema.org
Contributors: jsmoriss
Requires Plugins: wpsso, woocommerce
Requires PHP: 7.2.34
Requires At Least: 5.5
Tested Up To: 6.3.0
WC Tested Up To: 8.0.0
Stable Tag: 2.8.1

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

**Version 2.9.0 (2023/08/09)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Added support for the `WpssoAdmin->settings_saved_notice()` method.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.5.
	* WPSSO Core v15.19.0.
	* WooCommerce v5.0.

**Version 2.8.1 (2023/01/26)**

* **New Features**
	* None.
* **Improvements**
	* Added compatibility declaration for WooCommerce HPOS.
	* Updated the minimum WordPress version from v5.2 to v5.5.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Updated the `WpssoAbstractAddOn` library class.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.5.
	* WPSSO Core v14.7.0.
	* WooCommerce v5.0.

**Version 2.8.0 (2023/01/20)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Updated the `SucomAbstractAddOn` common library class.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v14.5.0.
	* WooCommerce v5.0.

**Version 2.7.1 (2022/03/07)**

* **New Features**
	* None.
* **Improvements**
	* Shortened the SSO menu item name from "WooCommerce Shipping" to "WC Shipping".
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v11.5.0.
	* WooCommerce v5.0.

**Version 2.7.0 (2022/02/07)**

* **New Features**
	* None.
* **Improvements**
	* Added PHP `setlocale()` calls before/after using `wc_format_localized_decimal()`.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v10.2.0.
	* WooCommerce v5.0.

**Version 2.6.0 (2022/02/05)**

* **New Features**
	* None.
* **Improvements**
	* Added a call to `wc_format_localized_decimal()` for displayed shipping times.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v10.1.0.
	* WooCommerce v5.0.

**Version 2.5.0 (2022/01/19)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Renamed the lib/abstracts/ folder to lib/abstract/.
	* Renamed the `SucomAddOn` class to `SucomAbstractAddOn`.
	* Renamed the `WpssoAddOn` class to `WpssoAbstractAddOn`.
	* Renamed the `WpssoWpMeta` class to `WpssoAbstractWpMeta`.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v9.14.0.
	* WooCommerce v5.0.

== Upgrade Notice ==

= 2.9.0 =

(2023/08/09) Added support for the new `WpssoAdmin->settings_saved_notice()` method.

= 2.8.1 =

(2023/01/26) Added compatibility declaration for WooCommerce HPOS. Updated the minimum WordPress version from v5.2 to v5.5.

= 2.8.0 =

(2023/01/20) Updated the `SucomAbstractAddOn` common library class.

= 2.7.1 =

(2022/03/07) Shortened the SSO menu item name from "WooCommerce Shipping" to "WC Shipping".

= 2.7.0 =

(2022/02/07) Added PHP `setlocale()` calls before/after using `wc_format_localized_decimal()`.

= 2.6.0 =

(2022/02/05) Added a call to `wc_format_localized_decimal()` for displayed shipping times.

= 2.5.0 =

(2022/01/19) Renamed the lib/abstracts/ folder and its classes.

