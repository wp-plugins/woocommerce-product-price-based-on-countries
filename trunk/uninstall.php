<?php

// If uninstall not called from WordPress exit
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();

//delete de options

delete_option( '_oga_wppbc_apiurl' );

delete_option ( '_oga_wppbc_api_country_field' );

delete_option( '_oga_wppbc_countries_groups' ); 

delete_option( 'wc_price_based_country_update_geoip' );

delete_option( 'wc_price_based_country_debug_mode' );

delete_option( 'wc_price_based_country_debug_ip' );

delete_option( 'wc_price_based_country_timestamp' );


// unlink geoip db

$geoip_db = p_upload_dir();
$geoip_db = $geoip_db['basedir'] . '/wc-price-based-country/GeoLite2-Country.mmdb';

if ( file_exists($geoip_db) ) 
	unlink( $geoip_db);
?>
