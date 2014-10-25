=== WooCommerce Product Price Based on Countries ===
Contributors: oscargare
Tags: price based country, dynamic price based country, price by country, dynamic price, woocommerce, geoip
Requires at least: 3.0.1
Tested up to: 3.9.2
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sets products prices based on country of your site's visitor.

== Description ==

**WooCommerce Product Price Based on Countries** is a extension for WooCommerce that allows you to set dynamic pricing for products based on the country of your website's visitors. The plugin gets the country using your favorite IP geolocation RESTful service (e.g. ipinfo.io, ipinfodb.com or db-ip.com) and displays product price you have defined previously for this country (or group of countries), if you didn't have defined a price for this country shows the regular price.

This plugin is WooCommerce 2.x compatible.

Based on *Woocommerce Price by Country* by *Sweet Homes*.

== Installation ==

1. Download, install and activate the plugin.
1. Go to WooCommerce -> Settings -> Integration -> Product Price Based on Countries section.
1. Set the URL of your favorite API IP-based GeoLocation. Enter *{ip}* to indicate de client IP address, e.g. *http://example.com/api?ip={ip}*. The URL call must return a valid JSON.
1. Set field name of JSON response which contains the two-letter country code.
1. Add many groups of countries as you need.
1. Go to product data and sets prices for the groups you have defined.

== Changelog ==

= 1.0.1 =
* Fixed a bug that did not allow to add more than one group of countries.

= 1.0 =
* Initial release!
