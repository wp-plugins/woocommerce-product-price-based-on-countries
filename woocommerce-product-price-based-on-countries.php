<?php

/*
 Plugin Name: WooCommerce Product Price Based on Countries
 Plugin URI: https://wordpress.org/plugins/woocommerce-product-price-based-on-countries/
 Description: Sets products prices based on country of your site's visitor.
 Author: Oscar Garcia Arenas
 Version: 1.2.1
 Author URI: google.com/+OscarGarciaArenas
 License: GPLv2
*/

 /*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (  in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {	

	define( 'WCPBC_FILE', __FILE__ );
	
	require_once 'includes/wcpbc-functions.php';	

	if ( is_admin() && !( defined('DOING_AJAX') && DOING_AJAX ) ) {

		require_once 'includes/class-wcpbc-admin.php';	

	} elseif ( ! is_bot() && file_exists( WCPBC_GEOIP_DB ) ) {
		
		require_once 'includes/class-wcpbc-frontend.php';			
	}		

	
	
} else {
	
	add_action( 'admin_init', 'oga_wppbc_deactivate' );
	
	function oga_wppbc_deactivate () {
		
		deactivate_plugins( plugin_basename( __FILE__ ) );
		
	}
	
   add_action( 'admin_notices', 'oga_wppbc_no_woocommerce_admin_notice' );
   
   function oga_wppbc_no_woocommerce_admin_notice () {
	   	?>
	   	<div class="updated">
	   		<p><strong>WooCommerce Product Price Based on Countries</strong> has been deactivated because <a href="http://woothemes.com/">Woocommerce plugin</a> is required</p>
	   	</div>
	   	<?php
    }	
      	
   
}	//end if (  in_array( 'woocommerce/woocommerce.php'



?>