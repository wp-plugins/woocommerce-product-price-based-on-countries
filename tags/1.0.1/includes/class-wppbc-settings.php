<?php 
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( class_exists( 'WC_Integration' ) ) {
	
	class WPPBC_Settings extends WC_Integration {
	
	
		function __construct() {
	
			$this->id = 'oga_wppbc';
			$this->method_title = __( 'Product Price Based on Countries', 'woocommerce-product-price-based-countries' );		
			
	
			add_action( 'woocommerce_update_options_integration_' . $this->id , array( &$this, 'process_admin_options') );
			add_action( 'wp_ajax_wpbc_get_uniqid', array( &$this, 'get_id' ) );
		}
			
		
		
		function get_id() { 
		
			die( json_encode( array( 'id' => uniqid() ) ) );
		
		}
		
		
		function admin_options() {
						
			$settingsForEdit = get_option( '_oga_wppbc_countries_groups' );						
			
			$ArrayForJson = array();
			
			if(is_array($settingsForEdit)) {
						
				foreach($settingsForEdit as $key => $element) {
					$ArrayForJson[$key] = $element['countries'];
				}			
			
			}
			
			$JsonSetings = json_encode($ArrayForJson);
			
			?>
			<table id="wpbc_settings_table" class="form-table" data-json-settings='<?php echo $JsonSetings; ?>'>				

				<tr valign="top">
					<th class="titledesc" scope="row"><label for="_oga_wppbc_apiurl">URL API REST</label><img class="help_tip" data-tip='<?php _e( "URL of API geolocation service (call must return a JSON) Enter {ip} to indicate client IP.", 'woocommerce-product-price-based-countries' ); ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" /></th>
					<td><input class="regular-text code" name="_oga_wppbc_apiurl" type="url" id="_oga_wppbc_apiurl" value="<?php echo get_option('_oga_wppbc_apiurl'); ?>" /></td>
				</tr>
				
				<tr valign="top">
					<th class="titledesc" scope="row"><label for="_oga_wppbc_api_country_field"><?php _e('Country code field name'); ?></label><img class="help_tip" data-tip='<?php _e( "Field name of JSON response which contains the country code.", 'woocommerce-product-price-based-countries' ); ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" /></th>
					<td><input class="regular-text code" name="_oga_wppbc_api_country_field" type="text" id="_oga_wppbc_api_country_field" value="<?php echo get_option('_oga_wppbc_api_country_field'); ?>" /></td>
				</tr>

				
				<?php $this->min_settings() ?>
				<?php $this->generate_settings_html(); ?>
			</table>
	
			<div><input type="hidden" name="section" value="<?php echo $this->id; ?>" /></div>
	
			<?php
		}				
		
		function min_settings() { 
		
			global $woocommerce;				
			
			$c = new WC_Countries();
			
			if ( !isset( $s ) )
				$s = array();
			
			
			?>
			<script>
			jQuery( document ).ready( function( $ ) { 
				$( "#wpbc_add_new_group" ).click( function() { 
				
					var uid = null;
					
					$.post( ajaxurl, { action:'wpbc_get_uniqid' }, function( data ) { 
					
						try { 
							var j = $.parseJSON( data );
							
							uid = j.id;
							
						} catch( err ) { 
					
							alert( '<?php _e( 'An error occurred. Try again.', 'woocomerce-price-by-country' )?>');
							return false;
					
						}
					
						var html = '\
							<tr>\
							<td width="10%">\
								<label class="">\
									<input type="text" name="group_level_name[group_level_' + uid + ']" placeholder="<?php _e( 'Enter a group name','woocomerce-price-by-country' )?>" value="">\
								</label>\
							</td>\
							<td>\
								<div style="" class="group_states_box">\
									<select name="group_level_country[group_level_' + uid + '][]" multiple="multiple" class="chosen_select">\
									<?php
									foreach( $c->countries as $k => $v ) {
										if ( in_array( $k, (array)$s ) )
											$selected = ' selected';
										else
											$selected = '';
										echo '<option value="' . $k . '"' . $selected . '>' . $v . '</option>\ ';
									}
									?>
									</select>\
								</div>\
								<button type="button" class="button all_button" style="display:inline-block;"><?php _e('All'); ?></button>\
								<button type="button" class="button none_button" style="display:inline-block;"><?php _e('None'); ?></button>\
								<button type="button" class="button es_button" style="display:inline-block;"><?php _e('ES'); ?></button>\
								<button type="button" class="button usa_button" style="display:inline-block;"><?php _e('USA'); ?></button>\
								<button type="button" class="button eu_button" style="display:inline-block;"><?php _e('EU'); ?></button>\
							</td>\
							<td width="10%">\
							</td>\
						</tr>\
						';
						
						$( '.group_table' ).append( html );
						$("select.chosen_select").chosen();
						return false;
					
					});
					
	
				})
				
				$( ".edit_group" ).click( function() { 
				
					var uid = $(this).attr('id'),
						prev = $(this).prev('.group-name'),
						prevName = prev.val(),
						parentTrLinea = $(this).parents('tr').eq(0);
					
					
					
					$.post( ajaxurl, { action:'wpbc_get_uniqid' }, function( data ) { 
					
						var settings = $('#wpbc_settings_table').attr('data-json-settings'),
							jsonSettings = JSON.parse(settings),
							currentEdit = jsonSettings[uid];
						
							console.log(currentEdit);
						
						var html = '\
							<td width="10%">\
								<label class="group-name">\
									<input type="text"  name="group_level_name[' + uid + ']" placeholder="<?php _e( 'Enter a group name','woocomerce-price-by-country' )?>" value="'+prevName+'">\
								</label>\
							</td>\
							<td>\
								<div style="width:300px;" class="group_states_box">\
									<select name="group_level_country['+ uid + '][]" multiple="multiple" class="chosen_select">\ ';
						
							var html1 = '';		
							
							
							<?php foreach( $c->countries as $k => $v ) { ?>
								
								var founded = $.inArray( "<?php echo $k ?>", currentEdit );
								var selected = '';
								
								if(founded != -1){
									selected = 'selected';
								} else {
									selected = '';
								}
								html1 +=  '<option value="<?php echo $k ;?>" '+selected+'><?php echo  $v ?></option>\ ';
							
							<?php } ?>
						
							var html2 = '</select>\
								</div>\
								<button type="button" class="button all_button" style="display:inline-block;"><?php _e('All'); ?></button>\
								<button type="button" class="button none_button" style="display:inline-block;"><?php _e('None'); ?></button>\
								<button type="button" class="button es_button" style="display:inline-block;"><?php _e('ES'); ?></button>\
								<button type="button" class="button usa_button" style="display:inline-block;"><?php _e('USA'); ?></button>\
								<button type="button" class="button eu_button" style="display:inline-block;"><?php _e('EU'); ?></button>\
							</td>\
							<td width="10%">\
							</td>\
						';
						
						var res = html.concat(html1,html2);
						
						
						parentTrLinea.html( res );
						$("select.chosen_select").chosen({width: "95%"});
						return false;
					
					});
					
	
				})
				
				
				
			})
			</script>
									
			
			<tr valign="top">
				<th class="titledesc" scope="row" colspan="2">
					<h5 style="margin:0">
					<?php _e( 'Add price groups by country', 'woocomerce-price-by-country' )?>:
					</h5>
				</th>
			</tr>
			<style>
				.help_tip.tiered { width: 16px; float: none !important; }
				.group_states_box .chzn-container { width: 100% !important; }
			</style>
			
			<tr valign="top">
				<td colspan="2" class="forminp wpbc_groups">	
					<table width="100%" class="group_table">
						<tr>
							<th>
								<strong><?php _e( 'Group Name', 'woocomerce-price-by-country' ) ?></strong>
							</th>
							<th>
								<strong><?php _e( 'Countries', 'woocomerce-price-by-country' ) ?></strong>
							</th>
							<th>
								<strong><?php _e( 'Delete', 'ignitewoo_tiered_pricing' ) ?></strong>
								<img class="help_tip tiered" src="<?php echo $woocommerce->plugin_url() ?>/assets/images/help.png" data-tip="<?php _e( 'Delete a group of price', 'woocomerce-price-by-country' )?>">
							</th>
							<th>
								<strong><?php _e( 'Edit', 'ignitewoo_tiered_pricing' ) ?></strong>
								<img class="help_tip tiered" src="<?php echo $woocommerce->plugin_url() ?>/assets/images/help.png" data-tip="<?php _e( 'Edit group', 'woocomerce-price-by-country' )?>">
							</th>
						</tr>
						<?php
													
						$settingsArray = get_option( '_oga_wppbc_countries_groups' );
	
						if (!empty($settingsArray)):
							foreach($settingsArray as $key => $data):
						?>
							<tr>
								<td width="10%">
									<label class="group-name">
										<span><?php echo stripslashes( $data['name'] ) ?></span> 
									</label>
								</td>
								<td>
									<?php echo implode(', ', $data['countries']); ?>
								</td>
								<td width="10%">
									<input type="checkbox" value="<?php echo $key ?>" style="" id="<?php echo $key ?>" name="group_level_delete[<?php echo $key ?>]" class="input-text wide-input "> 
								</td>
								<td width="20%">
									<input type="hidden" class="group-name" name="group-name" value="<?php echo stripslashes( $data['name'] ) ?>">
									<button type="button" class="button edit_group" id="<?php echo $key ?>" value="group_level_edit[<?php echo $key ?>]"><?php _e( 'Edit ', 'woocomerce-price-by-country' )?></button>
								</td>
							</tr>
						<?php 
							endforeach;
							
						endif;
						?>
					</table>
				</td>
			</tr>
			
			<tr>
				<th></th>
				<td><button type="button" class="button" id="wpbc_add_new_group"><?php _e( 'Add New Group', 'woocomerce-price-by-country' )?></button></td>
			<?php 
			
			
			// preset buttons
			
			wc_enqueue_js("
				jQuery('.all_button').live('click', function(){
					var self = jQuery(this),
						parentTD = self.parent();
					parentTD.find('select option').attr(\"selected\",\"selected\");
					parentTD.find('select').trigger('chosen:updated');
					return false;
				});
	
				jQuery('.none_button').live('click', function(){
					var self = jQuery(this),
						parentTD = self.parent();
					parentTD.find('select option').removeAttr(\"selected\");
					parentTD.find('select').trigger('chosen:updated');
					return false;
				});
	
				jQuery('.es_button').live('click', function(){
					var self = jQuery(this),
						parentTD = self.parent();
					parentTD.find('option[value=\"ES\"]').attr(\"selected\",\"selected\");
					parentTD.find('select').trigger('chosen:updated');
					return false;
				});
				
				jQuery('.usa_button').live('click', function(){
					var self = jQuery(this),
						parentTD = self.parent();
					parentTD.find('option[value=\"US\"]').attr(\"selected\",\"selected\");
					parentTD.find('select').trigger('chosen:updated');
					return false;
				});
	
				jQuery('.eu_button').live('click', function(){
					var self = jQuery(this),
						parentTD = self.parent();
					parentTD.find('option[value=\"AL\"], option[value=\"AD\"], option[value=\"AM\"], option[value=\"AT\"], option[value=\"BY\"], option[value=\"BE\"], option[value=\"BA\"], option[value=\"BG\"], option[value=\"CH\"], option[value=\"CY\"], option[value=\"CZ\"], option[value=\"DE\"], option[value=\"DK\"], option[value=\"EE\"], option[value=\"ES\"], option[value=\"FO\"], option[value=\"FI\"], option[value=\"FR\"], option[value=\"GB\"], option[value=\"GE\"], option[value=\"GI\"], option[value=\"GR\"], option[value=\"HU\"], option[value=\"HR\"], option[value=\"IE\"], option[value=\"IS\"], option[value=\"IT\"], option[value=\"LT\"], option[value=\"LU\"], option[value=\"LV\"], option[value=\"MC\"], option[value=\"MK\"], option[value=\"MT\"], option[value=\"NO\"], option[value=\"NL\"], option[value=\"PO\"], option[value=\"PT\"], option[value=\"RO\"], option[value=\"RU\"], option[value=\"SE\"], option[value=\"SI\"], option[value=\"SK\"], option[value=\"SM\"], option[value=\"TR\"], option[value=\"UA\"], option[value=\"VA\"]').attr(\"selected\",\"selected\");
					parentTD.find('select').trigger('chosen:updated');
					return false;
				});
			");
			
			echo '</tr>';
			
			?>
			
			<?php
		}
		
		function process_admin_options() {																					
			
			$settings = get_option( '_oga_wppbc_countries_groups' );
			
			if ((!empty($_POST['group_level_name'])) && (!empty($_POST['group_level_country']))) {
				
				$new_goups = array();
				
				$names = $_POST['group_level_name'];
				$countries = $_POST['group_level_country'];				
					
				if ($names) {
				
					foreach($names as $key => $name) {
						
						$new_goups[$key]['name'] = $name;
						$new_goups[$key]['countries'] = $countries[$key];
					}
				}
				
				$settings = array_merge( $settings, $new_goups );				
					
			}
				
			if ( !empty( $_POST[ 'group_level_delete' ] ) ) {
				 
				foreach( $_POST[ 'group_level_delete' ] as $key => $delgroup ) {
					 
					unset($settings[$delgroup]);
				}												
			}
			
			update_option( '_oga_wppbc_apiurl', sanitize_text_field( $_POST['_oga_wppbc_apiurl'] ) );
			
			update_option ( '_oga_wppbc_api_country_field', sanitize_key( $_POST['_oga_wppbc_api_country_field'] ) );
			
			update_option( '_oga_wppbc_countries_groups', $settings ); 
			
		}
		
		
	}	//end Class
	
	add_action( 'woocommerce_integrations', 'oga_wppbc_settings'  );
	
	function oga_wppbc_settings() {				
		
		$integrations[] = 'WPPBC_Settings';
		
		return $integrations;	
	}
	 
}
	
	
	
?>