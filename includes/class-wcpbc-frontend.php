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
 * @version		1.3.0
 * @author 		oscargare
 */
class WCPBC_Frontend {

	/**
	 * @var WCPBC_Customer $customer
	 */
	protected $customer = null;

	function __construct(){
		
		add_action( 'woocommerce_init', array(&$this, 'init') );		

		add_action( 'wp_enqueue_scripts', array( &$this, 'load_checkout_script' ) );

		add_action( 'woocommerce_checkout_update_order_review', array( &$this, 'checkout_country_update' ) );			

		add_action( 'wcpbc_manual_country_selector', array( &$this, 'country_select' ) );
		
		add_filter( 'woocommerce_customer_default_location', array( &$this, 'default_customer_country' ) );
			
		add_filter( 'woocommerce_currency',  array( &$this, 'currency' ) );

		add_filter( 'woocommerce_get_regular_price', array( &$this, 'get_regular_price') , 10, 2 );

		add_filter( 'woocommerce_get_sale_price', array( &$this, 'get_sale_price') , 10, 2 );
		
		add_filter('woocommerce_get_price', array( &$this, 'get_price' ), 10, 2 );
		
		add_filter( 'woocommerce_get_variation_regular_price', array( &$this, 'get_variation_regular_price' ), 10, 4 );
						
		add_filter( 'woocommerce_get_variation_price', array( &$this, 'get_variation_price' ), 10, 4 );			

	}		

	/**
	 * Instance WCPBC Customer after WooCommerce init	 
	 */
	public function init() {
				
		if ( isset($_POST['wcpbc-manual-country']) && $_POST['wcpbc-manual-country'] ) {			
			
			WC()->customer->set_country($_POST['wcpbc-manual-country']);
		}
				
		$this->customer = new WCPBC_Customer();						
		

	}

	/**
	 * Add script to checkout page	 
	 */
	public function load_checkout_script( ) {

		if ( is_checkout() ) {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'wc-price-based-country-checkout', plugin_dir_url( WCPBC_FILE ) . 'assets/js/wcpbc-checkout' . $suffix . '.js', array( 'wc-checkout', 'wc-cart-fragments' ), WC_VERSION, true );
		}

	}

	/**
	 * Update WCPBC Customer country when order review is update
	 */
	public function checkout_country_update( $post_data ) {			
		
		if ( isset( $_POST['country'] ) && ! in_array( $_POST['country'] , $this->customer->countries ) ) {
			
			$this->customer->set_country( $_POST['country'] );
						
		}
	}

	/**
	 * Output manual country select form
	 */
	public function country_select() {

		$all_countries = WC()->countries->get_countries();		
		$base_country = wc_get_base_location();			

		$countries[ $base_country['country'] ] = $all_countries[$base_country['country']];

		foreach ( WCPBC()->get_regions() as $region ) {
			
			foreach ( $region['countries'] as $country ) {

				if ( ! array_key_exists( $country, $countries ) ) {
					$countries[ $country ] = $all_countries[$country];					
				}
			}			
		}

		asort( $countries );
		
		$other_country = key( array_diff_key($all_countries, $countries ) );
		
		$countries[$other_country] = apply_filters( 'wcpbc_other_countries_text', __( 'Other countries' ) );	

		wc_get_template('country-selector.php', array( 'countries' => $countries ), 'woocommerce-product-price-based-on-countries/', untrailingslashit( plugin_dir_path( WCPBC_FILE ) ) . '/templates/' );
	}

	/**
	 * Return default WC customer country from IP
	 * @return string
	 */
	public function default_customer_country( $country ) {
		
		$wppbc_country = country_from_client_ip();
		if ( $wppbc_country ) {
			return $wppbc_country;
		}
		return $country;			
	}	

	/**
	 * Return currency
	 * @return string currency
	 */
	public function currency( $currency ) {

		$wppbc_currency = $currency;
		
		if ( $this->customer->currency !== '' ) {
			
			$wppbc_currency = $this->customer->currency;
			
		}
		
		return $wppbc_currency;
	}		

	/**
	 * Returns the product's regular price
	 * @return string price
	 */
	public function get_regular_price ( $price, $product, $price_meta_key = '_price' ) {	
		
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
			
			if ( $wppbc_price === '' OR $wppbc_price == 0 ) {

				if ( $this->customer->empty_price_method ) {
					$wppbc_price = ($price * $this->customer->exchange_rate);

				} else {
					$wppbc_price = $price;
				}
			}
			
		}
			
		return $wppbc_price;
	}

	
	/**
	 * Returns the product's sale price
	 * @return string price
	 */
	public function get_sale_price ( $price, $product ) {	
		
		return $this->get_regular_price( $price, $product, '_sale_price');

	}
	

	/**
	 * Returns the product's active price.	 
	 * @return string price
	 */
	public function get_price ($price, $product) {			
		
		$wcpbc_sale_price = $this->get_sale_price( '', $product );

		$wcpbc_price = ( $wcpbc_sale_price != '' && $wcpbc_sale_price > 0 )? $wcpbc_sale_price : $this->get_regular_price( $price, $product );		
		
		return $wcpbc_price;
	}
	
	/**
	 * Get the min or max variation regular price.
	 * @param  string $min_or_max - min or max
	 * @param  boolean  $display Whether the value is going to be displayed
	 * @return string price
	 */
	public function get_variation_regular_price( $price, $product, $min_or_max, $display ) {
		
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
	
	/**
	 * Get the min or max variation active price.
	 * @param  string $min_or_max - min or max
	 * @param  boolean  $display Whether the value is going to be displayed
	 * @return string price
	 */		
	public function get_variation_price( $price, $product, $min_or_max, $display ) {		
		
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
