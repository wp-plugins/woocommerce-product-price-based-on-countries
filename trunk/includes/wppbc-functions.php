<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function oga_wppbc_client_ip() {
	
	if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && $_SERVER['HTTP_CLIENT_IP'] ) {
		
		$ip = $_SERVER['HTTP_CLIENT_IP'];
		
	} elseif( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && $_SERVER['HTTP_X_FORWARDED_FOR'] ) {
		
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	
	} else {
		
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	
	return $ip;
		 
}

function oga_wppbc_client_country() {
	
	$country = FALSE;
		
	$apiurl = get_option( '_oga_wppbc_apiurl' );
	
	$country_field = get_option( '_oga_wppbc_api_country_field' );
	
	if ( $apiurl && $country_field ) {
		
		$client_ip = oga_wppbc_client_ip();
		
		if ( $client_ip ) {
			
			$apiurl = str_replace("{ip}", $client_ip, $apiurl );
						
			try {
				
				$ipinfo = json_decode( file_get_contents( $apiurl ) , true );
				
				if ( isset($ipinfo[$country_field]) ) {
					
					$country = $ipinfo[$country_field];
				}				
				
			} catch ( Exception $e ) {
				
				if (defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					throw $e;
				}
			}
		}		
	}		 		
	
	return $country;
		
}

function oga_wppbc_location_data() {
	
	$data = FALSE;
	
	$client_country = oga_wppbc_client_country();
	
	if ( $client_country ) {
		
		$countries_groups = get_option( '_oga_wppbc_countries_groups' );
		
		foreach ( $countries_groups as $key => $group_data ) {
			
			foreach ( $group_data['countries'] as $country ) {
				
				if ( $country == $client_country ) {
					
					$data['group'] = $key;
					$data['country_code'] = $country;
					
					break 2;
				}
				
			}	//end foreach ( $group_data['countries']
			
		}		//end foreach ( $countries_groups
		
	}	//end if ( $client_country )	
		
	return $data;
} 

/* USER-AGENTS Tnks to iamandrus ## http://stackoverflow.com/a/6524325 ##
========================================================================== */

function type_user_agent ( ) {
	
	$type = FALSE;
	
	$user_agent = strtolower ( $_SERVER['HTTP_USER_AGENT'] );
	
	// matches core browser types
	if ( preg_match ( "/mozilla\/|opera\//", $user_agent ) ) {	
								
	 	$type = 'browser';
	
	// matches popular bots; watchmouse|pingdom\.com are "uptime services"
	} elseif ( preg_match ( "/googlebot|adsbot|yahooseeker|yahoobot|msnbot|watchmouse|pingdom\.com|feedfetcher-google/", $user_agent ) ) {
		
		$type == 'bot';
	
	// matches popular mobile devices that have small screens and/or touch inputs
 	// mobile devices have regional trends; some of these will have varying popularity in Europe, Asia, and America
 	// detailed demographics are unknown, and South America, the Pacific Islands, and Africa trends might not be represented, here				 
	} elseif( preg_match ( "/phone|iphone|itouch|ipod|symbian|android|htc_|htc-|palmos|blackberry|opera mini|iemobile|windows ce|nokia|fennec|hiptop|kindle|mot |mot-|webos\/|samsung|sonyericsson|^sie-|nintendo/", $user_agent ) ) {
		
		$type == 'mobile';
	
	// these are less common, and might not be worth checking
	} elseif( preg_match ( "/mobile|pda;|avantgo|eudoraweb|minimo|netfront|brew|teleca|lg;|lge |wap;| wap /", $user_agent ) ) {
		
		$type == 'mobile';
	}
	
	return $type;
  
}
