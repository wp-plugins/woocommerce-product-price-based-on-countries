=== WooCommerce Product Price Based on Countries ===
Contributors: oscargare
Tags: price based country, dynamic price based country, price by country, dynamic price, woocommerce, geoip
Requires at least: 3.6.1
Tested up to: 4.2.2
Stable tag: 1.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sets products prices based on country of your site's visitor.

== Description ==

**WooCommerce Product Price Based on Countries** is a extension for WooCommerce that allows you to set dynamic product price based on customer's country. 

The plugin detect automatically visitor's country using geolocation features and display the price and currency you have defined previously for this country (or region). 
If you didn't have defined a price for this country you have two options:

* Shows the regular price.
* Apply a exchange rate to automatically calculate price.


**Key Features**

* Easy settings and integrated with Woocommerce settings page.
* Multicurrency: allows to receive payments in different currencies, reducing the costs of currency conversions.
* Include regular price and sale price by region.
* It's possible set a exchange rate to automatically calculate price for a region when the product's price leave blank.
* Automatically detects of customer's country, with price and currency set accordingly.
* Refresh price and currency on order preview, cart and shop when country changes on checkout page.
* Included action hook to add a country selector to front-end.


**Requirements**

* WordPress 3.6 or later
* Woocommerce 2.1.x or later.

This product includes GeoLite2 data created by MaxMind, available from http://www.maxmind.com.

== Installation ==

1. Download, install and activate the plugin.
1. Go to WooCommerce -> Settings -> Product Price Based on Country and configure as required. The first time it will download GeoIp database.
1. Go to the product page and sets the price for the countries you have configured avobe.

= Country Selector (for developers) =

1. Add action "wcpbc_manual_country_selector" to your theme.
1. To customize the country selector:
	1. Create a directory named "woocommerce-product-price-based-on-countries" in your theme directory. 
	1. Copy to the directory created avobe the file "country-selector.php" included in the plugin.
	1. Work with this file.

== Frequently Asked Questions ==

= I Can't download the GeoIp database the first time, I get following error: "Error downloading GeoIP database from ... Service unavailable" =

MaxMind has included the IP address of your web server in a blacklist and is blocking the download.

To solve this problem you must upload the database manually to your web server following next steps:

1. Download the Maxmind database from http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz
1. Decompress the file.
1. Upload the file via FTP to the web server directory *wc_price_base country* located in your wordpress uploads directory (this directory is usually */wp-content/uploads*). If *wc_price_base country* directory does not exist create it.

You can also ask MaxMind to unblock the IP address of your web server.

Note that automatic Maxmind database updates cannot be enabled until you can successfully download the database from your web server.

= That way can I test that the prices are displayed correctly for a given country? =

If you are in a test environment, you can configure the debug mode in the setting page.
In a production environment you can use a privacy VPN tools like [hola](http://hola.org/) or [ZenMate](https://zenmate.com/)

You should do the test in a private browsing window to prevent data stored in the session.

== Screenshots ==

1. /assets/screenshot-1.png
2. /assets/screenshot-2.png
3. /assets/screenshot-3.png
4. /assets/screenshot-4.png

== Changelog ==

= 1.3.1 =
* Fixed: Price before discount not show for variable products with sale price.

= 1.3.0 =
* Added: Exchange rate to apply when price leave blank.
* Added: Hook and template to add a country selector.
* Fixed minor bugs.

= 1.2.5 =
* Fixed bug that breaks execution of cron jobs when run from wp-cron.php.
* Fixed bug: Error in uninstall procedure.

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
