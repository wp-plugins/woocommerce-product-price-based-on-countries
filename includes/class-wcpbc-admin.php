<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCPBC_Admin' ) ) :

/**
 * WCPBC_Admin
 *
 * WooCommerce Price Based Country Admin 
 *
 * @class 		WCPBC_Admin
 * @version		1.2.4
 * @category	Class
 * @author 		oscargare
 */
class WCPBC_Admin {

	function __construct(){
		
		add_action('init', array(&$this, 'init'));

		add_action( 'admin_enqueue_scripts', array( &$this, 'load_admin_script' ) );

	}

	/**
	 * Hook actions and filters
	 */
	function init() {

		add_filter('woocommerce_get_settings_pages', array( &$this, 'settings_price_based_country' ) );
		
		add_action( 'woocommerce_product_options_general_product_data', array( &$this, 'product_options_countries_prices' ) );
		
		add_action( 'woocommerce_process_product_meta_simple', array( &$this, 'process_product_simple_countries_prices' ) ) ;						
		
		if ( WC()->version < '2.3') {

			add_action( 'woocommerce_product_after_variable_attributes', array( &$this, 'product_variable_attributes_countries_prices_wc2_2') , 10, 3 );

		} else {

			add_action( 'woocommerce_product_after_variable_attributes', array( &$this, 'product_variable_attributes_countries_prices') , 10, 3 );
		}
				
		
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

		if ( count( WCPBC()->get_regions() ) ) {
	?>		
		<div class="options_group show_if_simple show_if_external wc-metaboxes-wrapper" style="margin-bottom: 25px;">			
			<p class="toolbar">				
				<a href="#" class="close_all"><?php _e( 'Close all', 'woocommerce' ); ?></a><a href="#" class="expand_all"><?php _e( 'Expand all', 'woocommerce' ); ?></a>
				<strong>Price Based on Country</strong>
			</p>

			<div class="wc-metaboxes">
	<?php 		
			foreach ( WCPBC()->get_regions() as $key => $value ) {			
		
			?>
				<div class="wc-metabox">
					<h3>					
						<div class="handlediv" title="<?php _e( 'Click to toggle', 'woocommerce' ); ?>"></div>
						<strong class=""><?php echo __( 'Price for', 'woocommerce-product-price-based-countries' ) . ' ' .$value['name'] ;?></strong>
					</h3>
					<div class="wc-metabox-content">
					<table cellpadding="0" cellspacing="0" class="">
						<tbody>
							<tr>
								<td>
									<label style="margin:0px;"><?php echo __( 'Regular Price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol($value['currency']) . ')'; ?></label>
									<input type="text" id="<?php echo '_' . $key . '_price'; ?>" name="<?php echo '_' . $key . '_price'; ?>" value="<?php echo wc_format_localized_price( get_post_meta( get_the_ID(), '_' . $key . '_price' , true ) ); ?>" class="short wc_input_price" />
								</td>
								<td>
									<label style="margin:0px;"><?php echo __( 'Sale Price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol($value['currency']) . ')'; ?></label>
									<input type="text" id="<?php echo '_' . $key . '_sale_price'; ?>" name="<?php echo '_' . $key . '_sale_price'; ?>" value="<?php echo wc_format_localized_price( get_post_meta( get_the_ID(), '_' . $key . '_sale_price' , true ) ); ?>" class="wc_input_price wcpbc_sale_price" />								
								</td>
							</tr>
						</tbody>
					</table>					
					</div>
				</div>

			<?php

				}			

			?>

			</div>
		</div>
	<?php				
		}
	}
	
	/**
	 * Save meta data product simple
	 */
	function process_product_simple_countries_prices( $post_id ) {				
		
		foreach ( WCPBC()->get_regions() as $key => $value ) {
			
			$id = '_' . $key . '_price';			
			
			update_post_meta( $post_id, $id, wc_format_decimal( $_POST[$id] ) );

			$id = '_' . $key . '_sale_price';			
			
			update_post_meta( $post_id, $id, wc_format_decimal( $_POST[$id] ) );
		}	
		
	}
	
	/**
	 * Deprecated for Woocommerce 2.2
	 * Add price input to product variation metabox
	 */
	function product_variable_attributes_countries_prices_wc2_2( $loop, $variation_data, $variation ) {	
		
		if ( count( WCPBC()->get_regions() ) ) {

		?>
			<tr><td colspan="2"><strong>Price Based on Country<strong></td></tr>
		<?php

			foreach ( WCPBC()->get_regions() as $key => $value ) {

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
	
	/**	
	 * Add price input to product variation metabox
	 */
	function product_variable_attributes_countries_prices( $loop, $variation_data, $variation ) {							
		 
		foreach ( WCPBC()->get_regions() as $key => $value) {

			$_regular_price = wc_format_localized_price( get_post_meta( $variation->ID, '_' . $key . '_variable_price', true) );
			$_sale_price = wc_format_localized_price( get_post_meta( $variation->ID, '_' . $key . '_variable_sale_price', true) );

		?>

			<div style="width:100%; overflow:auto; padding-right:10px;border-top:1px solid #eee;">

				<p><strong><?php echo __( 'Price for', 'woocommerce-product-price-based-countries' ) . ' ' . $value['name']; ?></strong></p>

				<p class="form-row form-row-first">
					<label><?php echo __( 'Regular Price:', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol( $value['currency'] ) . ')'; ?></label>
					<input type="text" size="5" name="<?php echo '_' . $key . '_variable_price[' . $loop. ']'; ?>" value="<?php if ( isset( $_regular_price ) ) echo esc_attr( $_regular_price ); ?>" class="wc_input_price" placeholder="" />
				</p>
				<p class="form-row form-row-last">
					<label><?php echo __( 'Sale Price:', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol( $value['currency'] ) . ')'; ?></label>
					<input type="text" size="5" name="<?php echo '_' . $key . '_variable_sale_price[' . $loop. ']'; ?>" value="<?php if ( isset( $_sale_price ) ) echo esc_attr( $_sale_price ); ?>" class="wc_input_price" />
				</p>

			</div>

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
		
		foreach ( WCPBC()->get_regions() as $key => $value ) {
			
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

	function load_admin_script( ) {	

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'wc-price-based-country-admin', plugin_dir_url( WCPBC_FILE ) . 'assets/js/wcpbc-admin' . $suffix . '.js', array('jquery'), WC_VERSION, true );		

	}

}

endif;

$wcpbc_admin = new WCPBC_Admin();

?>