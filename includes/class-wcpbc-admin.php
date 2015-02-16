<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCPBC_Admin' ) ) :

/**
 * WCPBC_Admin
 *
 * WooCommerce Price Based Country Admin 
 *
 * @class 		WCPBC_Admin
 * @version		1.2.3
 * @category	Class
 * @author 		oscargare
 */
class WCPBC_Admin {

	function __construct(){

		add_filter('woocommerce_get_settings_pages', array( &$this, 'settings_price_based_country' ) );
		
		add_action( 'woocommerce_product_options_general_product_data', array( &$this, 'product_options_countries_prices' ) );
		
		add_action( 'woocommerce_process_product_meta_simple', array( &$this, 'process_product_simple_countries_prices' ) ) ;						
		
		add_action( 'woocommerce_product_after_variable_attributes', array( &$this, 'product_variable_attributes_countries_prices') , 10, 3 );
		
		add_action( 'woocommerce_process_product_meta_variable', array( &$this, 'process_product_variable_countries_prices' ) );
		
		add_action( 'woocommerce_save_product_variation', array( &$this, 'save_product_variation_countries_prices' ), 10, 2 );

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
	?>		
		<div class="options_group show_if_simple show_if_external wc-metaboxes-wrapper">			
			<p class="toolbar">				
				<a href="#" class="close_all"><?php _e( 'Close all', 'woocommerce' ); ?></a><a href="#" class="expand_all"><?php _e( 'Expand all', 'woocommerce' ); ?></a>
				<strong>Price Based on Country</strong>
			</p>

			<div class="wc-metaboxes">
	<?php 
		$countries_groups =  get_option( '_oga_wppbc_countries_groups' );

		foreach ($countries_groups as $key => $value ) {
	?>
				<div class="wc-metabox">
					<h3>					
						<div class="handlediv" title="<?php _e( 'Click to toggle', 'woocommerce' ); ?>"></div>
						<strong class=""><?php echo __( 'Price for', 'woocommerce-product-price-based-countries' ) . ' ' .$value['name'] ;?></strong>
					</h3>
					<table cellpadding="0" cellspacing="0" class="wc-metabox-content">
						<tbody>
							<tr>
								<td>
									<label style="margin:0px;"><?php echo __( 'Regular Price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol($value['currency']) . ')'; ?></label>
									<input type="text" id="<?php echo '_' . $key . '_price'; ?>" name="<?php echo '_' . $key . '_price'; ?>" value="<?php echo wc_format_localized_price( get_post_meta( get_the_ID(), '_' . $key . '_price' , true ) ); ?>" class="short wc_input_price" />
								</td>
								<td>
									<label style="margin:0px;"><?php echo __( 'Sale Price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol($value['currency']) . ')'; ?></label>
									<input type="text" id="<?php echo '_' . $key . '_sale_price'; ?>" name="<?php echo '_' . $key . '_sale_price'; ?>" value="<?php echo wc_format_localized_price( get_post_meta( get_the_ID(), '_' . $key . '_sale_price' , true ) ); ?>" class="wc_input_price" />								
								</td>
							</tr>
						</tbody>
					</table>					
				</div>

	<?php

		}

	?>
			</div>
		</div>
	<?php				
				
	}
	
	/**
	 * Save meta data product simple
	 */
	function process_product_simple_countries_prices( $post_id ) {
		
		$countries_groups =  get_option( '_oga_wppbc_countries_groups' );
		
		foreach ($countries_groups as $key => $value ) {
			
			$id = '_' . $key . '_price';			
			
			update_post_meta( $post_id, $id, wc_format_decimal( $_POST[$id] ) );

			$id = '_' . $key . '_sale_price';			
			
			update_post_meta( $post_id, $id, wc_format_decimal( $_POST[$id] ) );
		}	
		
	}
	
	/**
	 * Add price input to product variation metabox
	 */
	function product_variable_attributes_countries_prices( $loop, $variation_data, $variation ) {

		$countries_groups = get_option( '_oga_wppbc_countries_groups' );
		
		if ( $countries_groups ) {

		?>
			<tr><td colspan="2"><strong>Price Based on Country<strong></td></tr>
		<?php

			foreach ($countries_groups as $key => $value ) {

		?>
			<tr><td colspan="2"><?php echo __( 'Price for', 'woocommerce-product-price-based-countries' ) . ' ' . $value['name']; ?></td></tr>				
			<tr>				
				<td >
					<?php		

						$id = '_' . $key . '_variable_price';
						
						$price = wc_format_localized_price( isset( $variation_data[$id] ) ? esc_attr( $variation_data[$id][0] ) : '' ); 
						 
					?>
					<label><?php echo __( 'Regular Price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol($value['currency']) . ')'; ?></label>
					<input type="text" name="<?php echo $id . '[' . $loop . ']'; ?>" value="<?php echo $price; ?>" class="wc_input_price" />
				</td>							

				<td >
					<?php		

						$id = '_' . $key . '_variable_sale_price';
						
						$price = wc_format_localized_price( isset( $variation_data[$id] ) ? esc_attr( $variation_data[$id][0] ) : '' ); 
						 
					?>
					<label><?php echo __( 'Sale Price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol($value['currency']) . ')'; ?></label>
					<input type="text" name="<?php echo $id . '[' . $loop . ']'; ?>" value="<?php echo $price; ?>" class="wc_input_price" />
				</td>							

			</tr>						

			<?php
						
			}		
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

			$meta_key = '_' . $key . '_variable_sale_price';
			
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