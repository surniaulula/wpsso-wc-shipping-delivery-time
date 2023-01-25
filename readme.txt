=== WPSSO Shipping Delivery Time for WooCommerce SEO ===
Plugin Name: WPSSO Shipping Delivery Time for WooCommerce SEO
Plugin Slug: wpsso-wc-shipping-delivery-time
Text Domain: wpsso-wc-shipping-delivery-time
Domain Path: /languages
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.txt
Assets URI: https://surniaulula.github.io/wpsso-wc-shipping-delivery-time/assets/
Tags: woocommerce, shipping, delivery, schema, schema.org, OfferShippingDetails, deliveryTime, ShippingDeliveryTime
Contributors: jsmoriss
Requires Plugins: wpsso, woocommerce
Requires PHP: 7.2
Requires At Least: 5.4
Tested Up To: 6.1.1
WC Tested Up To: 7.3.0
Stable Tag: 2.8.0

Shipping delivery time estimates for WooCommerce shipping zones, methods, and classes.

== Description ==

<!-- about -->

Integrates and extends the *WooCommerce &gt; Settings &gt; Shipping* page with additional shipping delivery times:

* Optionally show handling and packaging times in the cart and checkout pages.
* Optionally show transit times in the cart and checkout pages.
* Select shipping department hours (cutoff time for new orders and business hours).
* Select min / max handling times for WooCommerce shipping classes.
* Select min / max transit times for WooCommerce shipping zones and methods.
* Increment handling and transit times in half-hours and/or half-days.

Provides shipping delivery time information to the [WPSSO Core Premium plugin](https://wpsso.com/) for Schema (aka Schema.org) ShippingDeliveryTime markup.

Adding shipping details to your Schema Product markup is important if you offer free or low-cost shipping options, as this will make your products more appealing in Google search results.

<!-- /about -->

<h3>WPSSO Core Required</h3>

WPSSO Shipping Delivery Time for WooCommerce SEO (WPSSO WCSDT) is an add-on for [WooCommerce](https://wordpress.org/plugins/woocommerce/) and the [WPSSO Core plugin](https://wordpress.org/plugins/wpsso/).

== Installation ==

<h3 class="top">Install and Uninstall</h3>

* [Install the WPSSO Shipping Delivery Time for WooCommerce SEO add-on](https://wpsso.com/docs/plugins/wpsso-wc-shipping-delivery-time/installation/install-the-plugin/).
* [Uninstall the WPSSO Shipping Delivery Time for WooCommerce SEO add-on](https://wpsso.com/docs/plugins/wpsso-wc-shipping-delivery-time/installation/uninstall-the-plugin/).

== Frequently Asked Questions ==

<h3 class="top">Frequently Asked Questions</h3>

* [How do I exclude shipping details from JSON-LD markup?](https://wpsso.com/docs/plugins/wpsso-wc-shipping-delivery-time/faqs/how-do-i-exclude-offer-shipping-details-from-the-markup/)

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

<p><strong>WPSSO Core Premium customers have access to development, alpha, beta, and release candidate version updates:</strong></p>

<p>Under the SSO &gt; Update Manager settings page, select the "Development and Up" (for example) version filter for the WPSSO Core plugin and/or its add-ons. When new development versions are available, they will automatically appear under your WordPress Dashboard &gt; Updates page. You can reselect the "Stable / Production" version filter at any time to reinstall the latest stable version.</p>

<h3>Changelog / Release Notes</h3>

**Version 2.8.1-dev.5 (2023/01/25)**

* **New Features**
	* None.
* **Improvements**
	* Added compatibility declaration for WooCommerce HPOS.
	* Updated the minimum WordPress version from v5.2 to v5.4.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Updated the `WpssoAbstractAddOn` library class.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.4.
	* WPSSO Core v14.6.1-dev.5.
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

**Version 2.4.1 (2021/11/16)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Refactored the `SucomAddOn->get_missing_requirements()` method.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v9.8.0.
	* WooCommerce v5.0.

**Version 2.4.0 (2021/10/22)**

* **New Features**
	* None.
* **Improvements**
	* Changed the shipping and handling time container from '&lt;label&gt;&lt;/label&gt;' to '&lt;span class="shipping-handling-time"&gt;&lt;/span&gt;'.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v5.0.
	* WPSSO Core v9.0.0.
	* WooCommerce v3.8.0.

**Version 2.3.0 (2021/09/24)**

Maintenance release for WPSSO Core v9.0.0.

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v5.0.
	* WPSSO Core v9.0.0.
	* WooCommerce v3.8.0.

**Version 2.2.1 (2021/02/25)**

* **New Features**
	* None.
* **Improvements**
	* Updated the banners and icons of WPSSO Core and its add-ons.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v5.0.
	* WPSSO Core v8.34.0.
	* WooCommerce v3.8.0.

**Version 2.2.0 (2020/11/30)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Included the `$addon` argument for library class constructors.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v4.5.
	* WPSSO Core v8.16.0.
	* WooCommerce v3.8.0.

**Version 2.1.0 (2020/11/07)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* Moved the activation of shipping offers for Schema JSON-LD from WPSSO JSON to WPSSO WCSDT.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v5.6.
	* WordPress v4.4.
	* WPSSO Core v8.13.0.
	* WooCommerce v3.6.4.

**Version 2.0.1 (2020/10/29)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* Fixed incorrect WPSSOWCSDT_FILEPATH constant reference in WpssoWcsdtRegister.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v5.6.
	* WordPress v4.4.
	* WPSSO Core v8.10.0
	* WooCommerce v3.6.4.

**Version 2.0.0 (2020/10/28)**

* **New Features**
	* None.
* **Improvements**
	* Moved the "Shipping delivery times" settings to a new WooCommerce Shipping section.
	* Added a "Shipping department hours" section to the "Shipping delivery times" settings.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Added a new lib/woocommerce-admin.php library file.
	* Moved the 'wcsdt_handling_time' and 'wcsdt_transit_time' options to the WPSSO Core settings array.
* **Requires At Least**
	* PHP v5.6.
	* WordPress v4.4.
	* WPSSO Core v8.10.0
	* WooCommerce v3.6.4.

== Upgrade Notice ==

= 2.8.1-dev.5 =

(2023/01/25) Added compatibility declaration for WooCommerce HPOS. Updated the minimum WordPress version from v5.2 to v5.4.

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

= 2.4.1 =

(2021/11/16) Refactored the `SucomAddOn->get_missing_requirements()` method.

= 2.4.0 =

(2021/10/22) Changed the shipping and handling time container from '&lt;label&gt;&lt;/label&gt;' to '&lt;span class="shipping-handling-time"&gt;&lt;/span&gt;'.

= 2.3.0 =

(2021/09/24) Maintenance release for WPSSO Core v9.0.0.

= 2.2.1 =

(2021/02/25) Updated the banners and icons of WPSSO Core and its add-ons.

= 2.2.0 =

(2020/11/30) Included the `$addon` argument for library class constructors.

= 2.1.0 =

(2020/11/07) Moved the activation of shipping offers for Schema JSON-LD from WPSSO JSON to WPSSO WCSDT.

= 2.0.1 =

(2020/10/29) Fixed incorrect WPSSOWCSDT_FILEPATH constant reference in WpssoWcsdtRegister.

= 2.0.0 =

(2020/10/28) Added a "Shipping department hours" section to the "Shipping delivery times" settings.

