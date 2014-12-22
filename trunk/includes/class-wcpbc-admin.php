<?php
/**
 * WooCommerce Price Based Country Admin 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCPBC_Admin' ) ) :

/**
 * WCPBC_Admin
 */
class WCPBC_Admin {

	function __construct(){

		add_filter('woocommerce_get_settings_pages', array( &$this, 'settings_price_based_country' ) );

		add_action( 'woocommerce_product_options_pricing', array( &$this, 'product_options_countries_prices' ) );
			
		add_action( 'woocommerce_process_product_meta_simple', array( &$this, 'process_product_simple_countries_prices' ) ) ;						
		
		add_action( 'woocommerce_product_after_variable_attributes', array( &$this, 'product_variable_attributes_countries_prices', 10, 3 ) );
		
		add_action( 'woocommerce_process_product_meta_variable', array( &$this, 'process_product_variable_countries_prices' ) );
		
		add_action( 'woocommerce_save_product_variation', array( &$this, 'save_product_variation_countries_prices', 10, 2 ) );

		add_filter( 'woocommerce_currency',  array( &$this, 'order_currency' ) );

		add_action( 'admin_notices', array( &$this, 'check_database_file' ) );

	}

	/**
	 * Add Price Based Country settings tab to woocommerce settings
	 */
	function settings_price_based_country( $settings ) {

		$settings[] = include( 'class-wc-settings-price-based-country.php' );

		return $settings;
	}

	/**
	 * Add price input to product simple metabox
	 */
	function product_options_countries_prices() {	
		
		$countries_groups =  (array) get_option( '_oga_wppbc_countries_groups' );		

		foreach ( $countries_groups as $key => $value ) {				
			
			woocommerce_wp_text_input( array(
				'id' => '_' . $key . '_price', 
				'label' => __( 'Price for', 'woocommerce-product-price-based-countries' ) . ' ' . $value['name'] . ' (' . get_woocommerce_currency_symbol($value['currency']) . ')', 
				'data_type' => 'price'			
			) );
		}
				
	}
	
	/**
	 * Save meta data product simple
	 */
	function process_product_simple_countries_prices( $post_id ) {
		
		$countries_groups =  get_option( '_oga_wppbc_countries_groups' );
		
		foreach ($countries_groups as $key => $value ) {
			
			$id = '_' . $key . '_price';
			
			update_post_meta( $post_id, $id, wc_format_decimal( $_POST[$id] ) );
		}	
		
	}
	
	/**
	 * Add price input to product variation metabox
	 */
	function product_variable_attributes_countries_prices( $loop, $variation_data, $variation ) {
			
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
	
	function process_product_variable_countries_prices( $post_id ) {
		/*for future versions; process and save de min and max variation price*/ 
		
	}
	
	/**
	 * Save meta data product variation
	 */
	function save_product_variation_countries_prices( $variation_id, $i ) {
			
		$countries_groups = get_option( '_oga_wppbc_countries_groups' );
		
		foreach ( $countries_groups as $key => $value ) {
			
			$meta_key = '_' . $key . '_variable_price';
			
			if ( isset( $_POST[$meta_key][$i] ) ) {
				
				update_post_meta( $variation_id, $meta_key, wc_format_decimal( $_POST[$meta_key][$i] ) );
				
			}
		}				
	}

	/**
	 * default currency in order
	 */
	function order_currency( $currency )	{

		global $post;

		if ($post && $post->post_type == 'shop_order' ) {
			
			global $theorder;
			if ( $theorder ) 
				return $theorder->order_currency;

		}
			
		return $currency;
	}

	/**
	 * Display admin notices 
	 */
	function check_database_file() {

		global $pagenow;		
		
		if ( 'admin.php' == $pagenow && isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] == 'price_based_country' )			
			return;

		if ( ! file_exists( WCPBC_GEOIP_DB ) ) {			
			?>
			<div class="update-nag">
				<strong>WooCommerce Product Price Based on Countries</strong> now works with <span style="font-style:italic">GeoIP Database</span>, please go to <a href="<?php echo self_admin_url('admin.php?page=wc-settings&tab=price_based_country'); ?>">settings page</a> to activate.
	   		</div>
			<?php							
		}
	}

}

endif;

$wcpbc_admin = new WCPBC_Admin();

?>