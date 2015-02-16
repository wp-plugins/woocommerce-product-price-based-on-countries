<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCPBC_Frontend' ) ) :

require_once 'class-wcpbc-customer.php';	

/**
 * WCPBC_Frontend
 *
 * WooCommerce Price Based Country Front-End
 *
 * @class 		WCPBC_Frontend
 * @version		1.2.3
 * @category	Class
 * @author 		oscargare
 */
class WCPBC_Frontend {

	/**
	 * @var WCPBC_Customer $customer
	 */
	protected $customer = null;

	function __construct(){
		
		add_action( 'woocommerce_init', array(&$this, 'init') );		

		add_action( 'woocommerce_checkout_update_order_review', array( &$this, 'checkout_country_update' ) );
		
		add_action( 'wp_enqueue_scripts', array( &$this, 'load_checkout_script' ) );
		
		add_filter( 'woocommerce_customer_default_location', array( &$this, 'default_customer_country' ) );
			
		add_filter( 'woocommerce_currency',  array( &$this, 'currency' ) );

		add_filter( 'woocommerce_get_regular_price', array( &$this, 'get_regular_price') , 10, 2 );

		add_filter( 'woocommerce_get_sale_price', array( &$this, 'get_sale_price') , 10, 2 );
		
		add_filter('woocommerce_get_price', array( &$this, 'get_price' ), 10, 2 );
		
		add_filter( 'woocommerce_get_variation_regular_price', array( &$this, 'get_variation_regular_price' ), 10, 4 );
						
		add_filter( 'woocommerce_get_variation_price', array( &$this, 'get_variation_price' ), 10, 4 );		

	}		

	function init() {

		//WCPBC_Customer instance
		$this->customer = new WCPBC_Customer();			

	}

	function load_checkout_script( ) {

		if ( is_checkout() ) {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'wc-price-based-country-checkout', plugin_dir_url( WCPBC_FILE ) . 'assets/js/wcpbc-checkout' . $suffix . '.js', array( 'wc-checkout', 'wc-cart-fragments' ), WC_VERSION, true );
		}

	}

	function checkout_country_update( $post_data ) {			
		
		if ( isset( $_POST['country'] ) && $_POST['country'] !== $this->customer->country ) {
			
			$this->customer->set_country( $_POST['country'] );
						
		}
	}

	function default_customer_country( $country ) {
		
		return country_from_client_ip();			
	}	

	function currency( $currency ) {

		$wppbc_currency = $currency;
		
		if ( $this->customer->currency !== '' ) {
			
			$wppbc_currency = $this->customer->currency;
			
		}
		
		return $wppbc_currency;
	}		

	function get_regular_price ( $price, $product, $price_meta_key = '_price' ) {	
		
		$wppbc_price = $price;
		
		if ( $this->customer->group_key !== '' ) {					
			
			if ( get_class( $product ) == 'WC_Product_Variation' ) {
				
				$post_id = $product->variation_id;	
				$meta_key = '_' . $this->customer->group_key . '_variable' . $price_meta_key;

			} else {

				$post_id = $product->id;  
				$meta_key = '_' . $this->customer->group_key . $price_meta_key;

			}		
						
			$wppbc_price = get_post_meta( $post_id, $meta_key, true );
			
			$wppbc_price = ($wppbc_price == '' OR $wppbc_price == 0) ? $price : $wppbc_price;										
			
		}
			
		return $wppbc_price;
	}

	function get_sale_price ( $price, $product ) {	
		
		return $this->get_regular_price( $price, $product, '_sale_price');

	}
	
	function get_price ($price, $product) {			
		
		$wcpbc_sale_price = $this->get_sale_price( '', $product );

		$wcpbc_price = ( $wcpbc_sale_price != '' && $wcpbc_sale_price > 0 )? $wcpbc_sale_price : $this->get_regular_price( $price, $product );		
		
		return $wcpbc_price;
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
		 
}

endif;

$wcpbc_frontend = new WCPBC_Frontend();

?>
