<?php
/**
 * WooCommerce Price Based Country Front-End
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCPBC_Frontend' ) ) :

/**
 * WCPBC_Frontend
 */
class WCPBC_Frontend {

	function __construct(){

		add_action( 'plugins_loaded', array( &$this, 'set_client_location_data' ) );

		add_filter( 'woocommerce_customer_default_location', array( &$this, 'default_customer_country' ) );
			
		add_filter( 'woocommerce_currency',  array( &$this, 'currency' ) );

		add_filter( 'woocommerce_get_regular_price', array( &$this, 'get_regular_price') , 10, 2 );
		
		add_filter('woocommerce_get_price', array( &$this, 'get_price' ), 10, 2 );
		
		add_filter( 'woocommerce_get_variation_regular_price', array( &$this, 'get_variation_regular_price' ), 10, 4 );
						
		add_filter( 'woocommerce_get_variation_price', array( &$this, 'get_variation_price' ), 10, 4 );		
	}

	function set_client_location_data() {

		if( ! session_id()) session_start();		   			   	
   		
   		if ( isset( $_SESSION['oga_wppbc_data'] ) && $_SESSION['oga_wppbc_data']['timestamp'] < get_option( 'wc_price_based_country_timestamp' ) ) {
   				unset( $_SESSION['oga_wppbc_data'] );
   		}

   		if ( ! isset( $_SESSION['oga_wppbc_data'] ) ) {

   			$client_country = self::client_country_code();
	
			if ( $client_country ) {
				
				$countries_groups = get_option( '_oga_wppbc_countries_groups' );
				
				foreach ( $countries_groups as $key => $group_data ) {				

					foreach ( $group_data['countries'] as $country ) {

						if ( $country == $client_country ) {

							$_SESSION['oga_wppbc_data']['group'] = $key;
							$_SESSION['oga_wppbc_data']['country_code'] = $client_country;
							$_SESSION['oga_wppbc_data']['currency'] = $countries_groups[$key]['currency'];	
							$_SESSION['oga_wppbc_data']['timestamp'] = time();					
							break 2;
						}
					}
				}
			}
   		}

   		/*if ( get_option( 'wc_price_based_country_debug_mode' ) == 'yes' && !( defined('DOING_AJAX') && DOING_AJAX ) ) {
   			print_r('WC Price Based Country debug info: ' . print_r( $_SESSION['oga_wppbc_data'], true ) );
   		}*/
   		

	}

	function default_customer_country( $country ) {
		
		$wppbc_country = $country;
		
		if ( isset( $_SESSION['oga_wppbc_data']['country_code'] ) ) {
			
			$wppbc_country = $_SESSION['oga_wppbc_data']['country_code'];
			
		}
		
		return $wppbc_country;
	}	

	function currency( $currency ) {

		$wppbc_currency = $currency;
		
		if ( isset( $_SESSION['oga_wppbc_data']['currency'] ) ) {
			
			$wppbc_currency = $_SESSION['oga_wppbc_data']['currency'];
			
		}
		
		return $wppbc_currency;
	}	
	
	function get_regular_price ( $price, $product ) {	
		
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
	
	function get_price ($price, $product) {
		
		$wppbc_price = $price;	
						
		if (! $product->get_sale_price() ) {
			
			$wppbc_price =  $this->get_regular_price( $price, $product );
						
		} 	
		
		return $wppbc_price;
	}
	
	
	function get_variation_regular_price( $price, $product, $min_or_max, $display ) {
		
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
	
	
	function get_variation_price( $price, $product, $min_or_max, $display ) {		
		
		$wppbc_price = $price;		
			
		if (! $product->get_variation_sale_price($min_or_max) ) {						
			
			$wppbc_price = $this->get_variation_regular_price( $price, $product, $min_or_max, $display );															
		}	
		
		return $wppbc_price;
	}

	protected static function client_country_code() {	

		$debug_ip = get_option( 'wc_price_based_country_debug_ip' );

		if ( get_option( 'wc_price_based_country_debug_mode' ) == 'yes' && $debug_ip ) {

			$client_ip = $debug_ip;

		} else {

			if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && $_SERVER['HTTP_CLIENT_IP'] ) {
			
				$client_ip = $_SERVER['HTTP_CLIENT_IP'];
				
			} elseif( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && $_SERVER['HTTP_X_FORWARDED_FOR'] ) {
				
				$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			
			} else {
				
				$client_ip = $_SERVER['REMOTE_ADDR'];
			}			

		}	
		
		return get_country_from_ip( $client_ip );
	}

		 
}

endif;

$wcpbc_frontend = new WCPBC_Frontend();

?>
