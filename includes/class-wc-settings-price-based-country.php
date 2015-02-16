<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Settings_Price_Based_Country' ) ) :

/**
 * WC_Settings_Price_Based_Country
 *
 * WooCommerce Price Based Country settings page
 *
 * @class 		WC_Settings_Price_Based_Country
 * @version		1.2.3
 * @category	Class
 * @author 		oscargare
 */
class WC_Settings_Price_Based_Country extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'price_based_country';
		$this->label = __( 'Price Based on Country', 'woocommerce-product-price-based-countries' );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_admin_field_country_groups', array( $this, 'country_groups_table' ) );		
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );		
			
	}

	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {	
		
		$sections = array();

		if ( file_exists( WCPBC_GEOIP_DB) ) {

			$sections = array(
				''         => __( 'Price Based on Country Options', 'woocommerce-product-price-based-countries' )
			);

			$country_groups =  get_option( '_oga_wppbc_countries_groups' );

			if ( $country_groups ) {
				foreach ($country_groups as $key => $country_group ) {
					$sections[$key] = $country_group['name'];
				}
			}
		}

		return $sections;
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {				

		if ( file_exists( WCPBC_GEOIP_DB ) ) {		

			$next_update = wp_next_scheduled( 'wcpbc_update_geoip' );
			$debug_country = '';
			$debug_ip = get_option( 'wc_price_based_country_debug_ip' );
			if ( $debug_ip ) {
				$debug_country = WC()->countries->countries[ get_country_from_ip( $debug_ip ) ];
			}
			
			return array(			
			
				array( 
					'title' => __( 'Pricing groups', 'woocommerce-product-price-based-countries' ), 
					'type' => 'title', 
					'desc' => 'Pricing groups are listed below. Add a group for each price you need to add to products and include the countries for which this price will be displayed. For deleted a group, check "Delete" and save changes', 
					'id' => 'price_based_country_groups'
				),

				array( 'type' => 'country_groups' ),				

				array( 'type' => 'sectionend', 'id' => 'price_based_country_groups' ),

				array( 
					'title' => __( 'GeoIp Database', 'woocommerce-product-price-based-countries' ), 
					'type' => 'title', 
					'desc' => 'This product includes GeoLite2 data created by MaxMind, available from <a href="http://www.maxmind.com">http://www.maxmind.com</a>.', 
					'id' => 'price_based_country_geoip'
				),

				array(
					'title' => __( 'Auto update GeoIP', 'woocommerce-product-price-based-countries' ),
					'desc' 		=> __( 'Download GeoIP Database Once Every 4 Weeks', 'woocommerce-product-price-based-countries' ),
					'id' 		=> 'wc_price_based_country_update_geoip',
					'default'	=> 'yes',
					'type' 		=> 'checkbox',
					'desc_tip'	=> $next_update ? sprintf(__('Next update at %s', 'woocommerce-product-price-based-countries'), date_i18n( 'Y-m-d H:i', $next_update ) ):''
				),
				
				array( 'type' => 'sectionend', 'id' => 'price_based_country_geoip' ),		

				array( 
					'title' => __( 'Debug Mode', 'woocommerce-product-price-based-countries' ), 
					'type' => 'title', 
					'desc' => 'If you want to check that prices are shown successfully, enable debug mode and enter the IP with which you want to try.', 
					'id' => 'price_based_country_debug'
				),

				array(
					'title' => __( 'Enabled/Disabled', 'woocommerce-product-price-based-countries' ),
					'desc' 		=> __( 'Enabled debug mode', 'woocommerce-product-price-based-countries' ),
					'id' 		=> 'wc_price_based_country_debug_mode',
					'default'	=> 'no',
					'type' 		=> 'checkbox'					
				),

				array(
					'title' => __( 'Debugging IP', 'woocommerce-product-price-based-countries' ),					
					'id' 		=> 'wc_price_based_country_debug_ip',
					'desc'	=> $debug_country,
					'default'	=> '',
					'type' 		=> 'text'					
				),
				
				array( 'type' => 'sectionend', 'id' => 'price_based_country_debug' )


			);			

		} else {

			return array(

				array( 
					'title' => __( 'Download GeoIP Database', 'woocommerce-product-price-based-countries' ), 
					'type' => 'title', 
					'desc' => __( 'WooCommerce Price Based on Countries works with MaxMind GeoIP DataBase, to activate check “Download GeoIP Database” and Save changes.', 'woocommerce-product-price-based-countries' ), 
					'id' => 'price_based_country_options'
				),

				array(
					'title' => __( 'Enabled Price Based on Country', 'woocommerce-product-price-based-countries' ),
					'desc' 		=> __( 'Download GeoIP Database', 'woocommerce-product-price-based-countries' ),
					'id' 		=> 'wc_price_based_country_update_geoip',
					'default'	=> 'yes',
					'type' 		=> 'checkbox'
				),

				array( 'type' => 'sectionend', 'id' => 'price_based_country_options' )		
			);

		}		
		
	}

	/**
	 * Output country groups table.
	 *
	 * @access public
	 * @return void
	 */
	public function country_groups_table() {
		?>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php _e( 'Groups', 'woocommerce-product-price-based-countries' ) ?></th>
		    <td class="forminp">
				<table class="widefat" cellspacing="0">
					<thead>
						<tr>
							<th style="width:5px;"></th>							
							<th><?php _e( 'Group Name', 'woocommerce-product-price-based-countries' ) ?></th>
							<th><?php _e( 'Countries', 'woocommerce-product-price-based-countries' ) ?></th>
							<th><?php _e( 'Currency', 'woocommerce' ); ?></th>
							<th style="width:120px;"></th>
							<th style="width:80px;"><?php _e( 'Delete', 'woocommerce' ); ?></th>
						</tr>
					</thead>
						<?php
							$currencies = get_woocommerce_currencies();							

							$country_groups = get_option( '_oga_wppbc_countries_groups' );							
							
							if ( $country_groups ) {
								
								foreach ($country_groups as $key => $country_group) {

									echo '<tr id="' . $key . '">';							

									echo '<td></td>';

									echo '<td>' . $country_group['name'] . '</td>';

									echo '<td>';

									$country_display = array();
									
									foreach( $country_group['countries'] as $iso_code ) {
										$country_display[] = WC()->countries->countries[$iso_code];										
									}

									echo  implode($country_display, ', ');

									echo '</td>';

									echo '<td>' . $currencies[$country_group['currency']] . ' (' . get_woocommerce_currency_symbol($country_group['currency']) . ')</td>';

									echo '<td>';
									echo '<a class="button" href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . $key) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';								
									echo '</td>';

									echo '<td style="padding:15px 10px;"><input type="checkbox" value="' . $key . '" name="delete_group[]" /></td>';

									echo '</tr>';							
								}
							}							
							
						?>
					<tbody>
					</tbody>
					<tfoot>
						<tr>							
							<th style="width:5px;"></th>
							<th colspan="5">
								<a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=new_group') ?>" class="button">+ Add group</a>								
							</th>							
						</tr>						
					</tfoot>
				</table>
			</td>
		</tr>
		<?php					

	}

	/**
	 * Output section.
	 *
	 * @access public
	 * @return void
	 */
	public function section_settings( $not_available_countries, $group = array() ) {

		if ( ! isset( $group['name'] ) ) $group['name'] = '';
		if ( ! isset( $group['countries'] ) ) $group['countries'] = array();
		if ( ! isset( $group['currency'] ) ) $group['currency'] = get_option('woocommerce_currency');

		?>
		<h3><?php echo $group['name'] ? esc_html( $group['name'] ) : __( 'Add Group', 'woocommerce-product-price-based-countries' ); ?></h3>
		<table class="form-table">

			<!-- Group name -->
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="group_name"><?php _e( 'Group Name', 'woocommerce-product-price-based-countries' ); ?></label>
					<?php //echo $tip; ?>
				</th>
                <td class="forminp forminp-text">
                	<input name="group_name" id="group_name" type="text" value="<?php echo esc_attr( $group['name'] ); ?>"/> 
                	<?php //echo $description; ?>
                </td>
			</tr>

			<!-- Country multiselect -->			
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="group_countries"><?php _e( 'Countries', 'woocommerce-product-price-based-countries' ); ?></label>
				</th>
				<td class="forminp">
					<select multiple="multiple" name="group_countries[]" style="width:350px" data-placeholder="<?php _e( 'Choose countries&hellip;', 'woocommerce' ); ?>" title="Country" class="chosen_select">
						<?php 	
							
							$countries = WC()->countries->countries;							

							asort( $countries );
							
		        			foreach ( $countries as $key => $val ) {
		        				if ( ! in_array( $key, $not_available_countries ) ) {
                					echo '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( $key, $group['countries'] ), true, false ).'>' . $val . '</option>';
                				}
                			}
						?>
					</select>
				</td>
			</tr>

			<!-- Currency select -->			
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="group_currency"><?php _e( 'Currency', 'woocommerce' ); ?></label>
					<?php //echo $tip; ?>
				</th>
				<td class="forminp forminp-select">
					<select name="group_currency" id="group_currency" class="chosen_select">
						<?php
							foreach ( get_woocommerce_currencies() as $code => $name ) {
								echo '<option value="' . esc_attr( $code ) . '" ' . selected( $group['currency'], $code ) .'>' . $name . ' (' . get_woocommerce_currency_symbol( $code ) . ')' . '</option>';
							}
						?>
					</select>
				</td>
			</tr>

		</table>

		<?php

	}

	/**
	 * Output the settings
	 */
	public function output() {
		global $current_section;

		if ( $current_section ) {

			$country_groups = get_option('_oga_wppbc_countries_groups');				

			$not_available_countries = array();

			if ( $country_groups ) {

				foreach ( $country_groups as $key => $value ) {					

					foreach ( $value['countries'] as $code ) {

						if ( $current_section !== $key ) $not_available_countries[] = $code;							
					}
				}
			}			

			if ( $current_section == 'new_group' ) {
				
				$this->section_settings( $not_available_countries );

			} else {			
				
				if ( isset( $country_groups[$current_section] ) ) {								

					$this->section_settings( $not_available_countries, $country_groups[$current_section] );
				}					
			}								

		} else {
			parent::output();			
		}	
	}


	/**
	 * Save section settings
	 */
	public function section_save() {

		global $current_section;

		$save = false;

		if ( ! $_POST['group_name'] ) {

			WC_Admin_Settings::add_error( __( 'Group name is required.', 'woocommerce-product-price-based-countries' ) );

		} elseif( ! isset( $_POST['group_countries'] ) ) {

			WC_Admin_Settings::add_error( __( 'Add at least one country to the list.', 'woocommerce-product-price-based-countries' ) );

		} else {

			$section_settings = get_option('_oga_wppbc_countries_groups');

			if (! $section_settings ) {
				$section_settings = array();
			}
			
			$key = ( $current_section == 'new_group' ) ? sanitize_title( $_POST['group_name'] ) : $current_section;
			
			$section_settings[$key]['name'] = $_POST['group_name'];
			$section_settings[$key]['countries'] = $_POST['group_countries'];
			$section_settings[$key]['currency'] = $_POST['group_currency'];
			
			update_option( '_oga_wppbc_countries_groups', $section_settings );
			
			if ( $current_section == 'new_group' ) {
				$current_section = $key ;					
			}

			$save = true;
			
		}

		return $save;

	}

	/**
	 * Save global settings 
	 */
	public function save() {
		
		global $current_section;
		
		if ( $current_section && $this->section_save() ) {
			
			update_option( 'wc_price_based_country_timestamp', time() );

		} else {
						
			if ( ! empty( $_POST['wc_price_based_country_debug_ip'] ) && ! filter_var( $_POST['wc_price_based_country_debug_ip'], FILTER_VALIDATE_IP ) ){

				WC_Admin_Settings::add_error( __( 'Debugging IP must be a valid IP address.', 'woocommerce-product-price-based-countries' ) );

			} else {
				
				if ( isset( $_POST['delete_group'] ) ) {
					
					$section_settings = (array) get_option('_oga_wppbc_countries_groups');
					$metakeys = array();

					foreach ( $_POST['delete_group'] as $value ) {

						unset( $section_settings[$value] );

						$metakeys[] = "'_". $value ."_price'";
						$metakeys[] = "'_". $value ."_sale_price'";
						$metakeys[] = "'_". $value ."_variable_price'";
						$metakeys[] = "'_". $value ."_variable_sale_price'";
					}

					update_option( '_oga_wppbc_countries_groups', $section_settings );
					
					//delete postmeta data
					global $wpdb;
					$wpdb->query( "DELETE FROM " . $wpdb->postmeta . " WHERE meta_key in (" . implode(',', $metakeys) . ")" );

				}

				//Database geoip update

				if ( ! wp_next_scheduled( 'wcpbc_update_geoip' ) && isset( $_POST['wc_price_based_country_update_geoip'] ) ) {

					$update_errors = wcpbc_donwload_geoipdb();
					
					if ( $update_errors ) {

						WC_Admin_Settings::add_error( $update_errors );					

						unset( $_POST['wc_price_based_country_update_geoip'] );

					} else {
						wp_schedule_event( time() + 2419200, '4weeks', 'wcpbc_update_geoip');					

						WC_Admin_Settings::add_message( __( 'GeoIP info has been updated.', 'woocommerce-product-price-based-countries' ) );

					}					


				} elseif ( wp_next_scheduled( 'wcpbc_update_geoip' ) && ! isset( $_POST['wc_price_based_country_update_geoip'] ) ) {

					wp_unschedule_event( wp_next_scheduled('wcpbc_update_geoip'), 'wcpbc_update_geoip' );
				}

				//save settings
				
				$settings = $this->get_settings();
				WC_Admin_Settings::save_fields( $settings );							

				update_option( 'wc_price_based_country_timestamp', time() );
			}

		}		
	}
	
}

endif;

return new WC_Settings_Price_Based_Country();

?>