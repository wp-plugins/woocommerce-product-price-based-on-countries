<?php

/*
 Plugin Name: WooCommerce Product Price Based on Countries
 Description: Sets products prices based on country of your site's visitor.
 Author: Oscar Garcia Arenas
 Version: 1.0.1
 Plugin URI: https://wordpress.org/plugins/woocommerce-product-price-based-on-countries/
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

require_once( plugin_dir_path(__FILE__).'includes/wppbc-functions.php');

if (  in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				
	register_activation_hook( __FILE__, 'oga_wppbc_install' );
	
	function oga_wppbc_install() {
		
		global $wpdb;

		$version = '1.0.1';
		
		$current_version = get_option( '_oga_wppbc_version' );
				
		/* update from Sweet Homes "WooCommerce Price by Country" plugin */
				
		$wc_pbc_settings = unserialize( get_option( 'woocommerce_price_by_country_settings' ) );
				
		if ( $wc_pbc_settings && ! $current_version ) {			
			
			update_option( '_oga_wppbc_countries_groups', $wc_pbc_settings ); 

			foreach ( $wc_pbc_settings as $group => $value ) {			

				$query = "SELECT ID, meta_value FROM $wpdb->posts INNER JOIN $wpdb->postmeta on post_id = ID ";
				$query .= "WHERE meta_key = '_" . $group . "_price' and post_type = 'product_variation'";

				$variation_prices = $wpdb->get_results($query);

				foreach ( $variation_prices as $variation_price ) {

					$meta_key = '_' . $group . '_variable_price';

					update_post_meta( $variation_price->ID, $meta_key, ( is_numeric( $variation_price->meta_value ) ) ? $variation_price->meta_value : '' );

				}
			}

		}
		
		/* end update from Sweet Homes */		
		

		update_option( '_oga_wppbc_version', $version );
		
	}

	add_action('plugins_loaded', 'oga_wppbc_setup');

	function  oga_wppbc_setup () {		
		
		include_once ( plugin_dir_path(__FILE__).'includes/class-wppbc-settings.php' );

		if ( is_admin() && !( defined('DOING_AJAX') && DOING_AJAX ) ) {
						
			//nothing			
						
		} elseif( type_user_agent() !== 'bot' ) {	//no bots ip location						
			
			if(!session_id()) {
				session_start();		
	   		}  	   		   		   	
	   		
	   		if ( ! isset( $_SESSION['oga_wppbc_data'] ) ) {	   			   		
	   		
	   			$data = oga_wppbc_location_data();
	   			
	   			if ( $data ) {
	   			
	   				$_SESSION['oga_wppbc_data'] = $data;
	   			 
	   			}	
	   			   		
	   	}	//end if ( ! isset( $_SESSION['oga_wppbc_data'] ) )
												
		}	//end elseif( type_user_agent() !== 'bot'
		
	}	
					
	add_action('init', 'oga_wppbc_init');
	
	function oga_wppbc_init(){
		
		if ( is_admin() && !( defined('DOING_AJAX') && DOING_AJAX ) ) {
							
			add_action( 'woocommerce_product_options_pricing', 'oga_wppbc_product_options_countries_prices' );
			
			add_action( 'woocommerce_process_product_meta_simple', 'oga_wppbc_process_product_simple_countries_prices' );						
			
			add_action( 'woocommerce_product_after_variable_attributes', 'oga_wppbc_product_variable_attributes_countries_prices', 10, 3 );
			
			add_action( 'woocommerce_process_product_meta_variable', 'oga_wppbc_process_product_variable_countries_prices' );
			
			add_action( 'woocommerce_save_product_variation', 'oga_wppbc_save_product_variation_countries_prices', 10, 2 );
			
		} else {
						
			add_filter( 'woocommerce_customer_default_location', 'oga_wppbc_default_customer_country' );
			
			add_filter( 'woocommerce_get_regular_price', 'oga_wppbc_get_regular_price', 10, 2 );
			
			add_filter('woocommerce_get_price', 'oga_wppbc_get_price', 10, 2);
			
			add_filter( 'woocommerce_get_variation_regular_price', 'oga_wppbc_get_variation_regular_price', 10, 4 );
							
			add_filter( 'woocommerce_get_variation_price', 'oga_wppbc_get_variation_price', 10, 4 );
						
		}
	}		

	function oga_wppbc_product_options_countries_prices() {	
		
		$countries_groups =  get_option( '_oga_wppbc_countries_groups' );		
		
		foreach ( $countries_groups as $key => $value ) {				
			
			woocommerce_wp_text_input( array(
				'id' => '_' . $key . '_price', 
				'label' => __( 'Price for', 'woocommerce-product-price-based-countries' ) . ' ' . $value['name'] . ' (' . get_woocommerce_currency_symbol() . ')', 
				'data_type' => 'price'			
			) );
		}
				
	}
	
	function oga_wppbc_process_product_simple_countries_prices( $post_id ) {
		
		$countries_groups =  get_option( '_oga_wppbc_countries_groups' );
		
		foreach ($countries_groups as $key => $value ) {
			
			$id = '_' . $key . '_price';
			
			update_post_meta( $post_id, $id, wc_format_decimal( $_POST[$id] ) );
		}	
		
	}
	
	function oga_wppbc_product_variable_attributes_countries_prices( $loop, $variation_data, $variation ) {
			
		$countries_groups = get_option( '_oga_wppbc_countries_groups' );
		
		foreach ($countries_groups as $key => $value ) {
			
			$id = '_' . $key . '_variable_price';
			
			$price = isset( $variation_data[$id] ) ? esc_attr( $variation_data[$id][0] ) : ''; 
				 
			?>
				<tr>
					<td colspan="2">
						<label><?php echo __( 'Price for', 'woocommerce-product-price-based-countries' ) . ' ' . $value['name'] . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
						<input type="text" name="<?php echo $id . '[' . $loop . ']'; ?>" value="<?php echo $price; ?>" class="wc_input_price" />
					</td>							
				</tr>
			<?php
					
		}
	}
	
	function oga_wppbc_process_product_variable_countries_prices( $post_id ) {
		/*for future versions; process and save de min and max variation price*/ 
		
	}
	
	function oga_wppbc_save_product_variation_countries_prices( $variation_id, $i ) {
			
			$countries_groups = get_option( '_oga_wppbc_countries_groups' );
			
			foreach ( $countries_groups as $key => $value ) {
				
				$meta_key = '_' . $key . '_variable_price';
				
				if ( isset( $_POST[$meta_key][$i] ) ) {
					
					update_post_meta( $variation_id, $meta_key, wc_format_decimal( $_POST[$meta_key][$i] ) );
					
				}
			}				
	}
	
	function oga_wppbc_default_customer_country( $country ) {
		
		$wppbc_country = $country;
		
		if ( isset( $_SESSION['oga_wppbc_data']) && $_SESSION['oga_wppbc_data'] ) {
			
			$wppbc_country = $_SESSION['oga_wppbc_data']['country_code'];
			
		}
		
		return $wppbc_country;
	}	
	
	function oga_wppbc_get_regular_price ( $price, $product ) {	
		
		$wppbc_price = $price;
		
		if ( isset( $_SESSION['oga_wppbc_data'] )  && $_SESSION['oga_wppbc_data'] ) {
			
			$wppbc_group = $_SESSION['oga_wppbc_data']['group'];
			
			if ( get_class( $product ) == 'WC_Product_Variation' ) {
				
				$post_id = $product->variation_id;	
				$meta_key = '_' . $wppbc_group . '_variable_price';

			} else {

				$post_id = $product->id;  
				$meta_key = '_' . $wppbc_group . '_price';

			}		
						
			$wppbc_price = get_post_meta( $post_id, $meta_key, true );
			
			$wppbc_price = ($wppbc_price == '' OR $wppbc_price == 0) ? $price : $wppbc_price;										
			
		}
			
		return $wppbc_price;
	}
	
	function oga_wppbc_get_price ($price, $product) {
		
		$wppbc_price = $price;	
						
		if (! $product->get_sale_price() ) {
			
			$wppbc_price =  oga_wppbc_get_regular_price( $price, $product );
						
		} 	
		
		return $wppbc_price;
	}
	
	
	function oga_wppbc_get_variation_regular_price( $price, $product, $min_or_max, $display ) {
		
		$wppbc_price = $price;		
			
		$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
						
		$prices = array();
		
		$display = array();
				
		foreach ($product->get_children() as $variation_id) {
			
			$variation = $product->get_child( $variation_id );
			
			if ( $variation ) {
				
				$prices[$variation_id] = $variation->get_price();
				
				$display[$variation_id] = $tax_display_mode == 'incl' ? $variation->get_price_including_tax() : $variation->get_price_excluding_tax();
			}				 
		}			
		
		if ( $min_or_max == 'min' ) {
			asort($prices);
		} else {
			arsort($prices);
		}		
			
		if ( $display ) {
			
			$variation_id = key( $prices );				
			$wppbc_price = $display[$variation_id];
			
		} else {
							
			$wppbc_price = current($prices);
			
		}			
		
		return $wppbc_price;
	}
	
	
	function oga_wppbc_get_variation_price( $price, $product, $min_or_max, $display ) {
		
		$wppbc_price = $price;		
			
		if (! $product->get_variation_sale_price($min_or_max) ) {						
			
			$wppbc_price = oga_wppbc_get_variation_regular_price( $price, $product, $min_or_max, $display );															
		}	
		
		return $wppbc_price;
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