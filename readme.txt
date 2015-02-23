=== WooCommerce Product Price Based on Countries ===
Contributors: oscargare
Tags: price based country, dynamic price based country, price by country, dynamic price, woocommerce, geoip
Requires at least: 3.6.1
Tested up to: 4.1
Stable tag: 1.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sets products prices based on country of your site's visitor.

== Description ==

**WooCommerce Product Price Based on Countries** is a extension for WooCommerce that allows you to set dynamic product price based on customer's country. 

The plugin detect automatically visitor's country using geolocation features and display the price and currency you have defined previously for this country (or region), if you didn't have defined a price for this country shows the regular price.

**Key Features**

* Easy settings and integrated with Woocommerce settings page.
* Multicurrency: allows to receive payments in different currencies, reducing the costs of currency conversions.
* Include regular price and sale price by region.
* Automatically detects of customer's country, with price and currency set accordingly.
* Refresh price and currency on order preview, cart and shop when country changes on checkout page.

**Requirements**

* WordPress 3.6 or later
* Woocommerce 2.1.x or later.

This product includes GeoLite2 data created by MaxMind, available from http://www.maxmind.com.

== Installation ==

1. Download, install and activate the plugin.
1. Go to WooCommerce -> Settings -> Product Price Based on Country and configure as required. The first time it will download GeoIp database.
1. Go to the product page and sets the price for the countries you have configured avobe.

== Frequently Asked Questions ==

= I Can't download the GeoIp database the first time, I get following error: "Error downloading GeoIP database from ... Service unavailable" =

MaxMind has included the IP address of your web server in a blacklist and is blocking the download.

To solve this problem you must upload the database manually to your web server following next steps:

1. Download the Maxmind database from http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz
1. Decompress the file.
1. Upload the file via FTP to the web server directory *wc_price_base country* located in your wordpress uploads directory (this directory is usually */wp-content/uploads*). If *wc_price_base country* directory does not exist create it.

You can also ask MaxMind to unblock the IP address of your web server.

Note that automatic Maxmind database updates cannot be enabled until you can successfully download the database from your web server.

== Screenshots ==

1. /assets/screenshot-1.png
2. /assets/screenshot-2.png
3. /assets/screenshot-3.png
4. /assets/screenshot-4.png

== Changelog ==

= 1.2.4 =
* Fixed bug that break style in variable products.
* Fixed bug: prices not show in variable products.

= 1.2.3 =
* Added: Sale price by groups of countries.
* Added: Refresh prices and currency when user changes billing country on checkout page.
* Fixed minor bugs.

= 1.2.2 =
* Fixed bug that not show prices per countries group when added a new variation using the "add variation" button.
* Fixed bug: product variation currency label is wrong.

= 1.2.1 =
* Fixed bug that not allow set prices in variable products.

= 1.2 =
* Added: REST service is replaced by GEOIP Database.
* Added: Improvements in the plugin settings page.
* Added: Debug mode

= 1.1 =
* Added: currency identifier per group of countries.
* Fixed bug in settings page.

= 1.0.1 =
* Fixed a bug that did not allow to add more than one group of countries.

= 1.0 =
* Initial release!
