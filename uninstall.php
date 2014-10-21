<?php

// If uninstall not called from WordPress exit
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();

//delete de options

delete_option( '_oga_wppbc_apiurl' );

delete_option ( '_oga_wppbc_api_country_field' );

delete_option( '_oga_wppbc_countries_groups' ); 

//delete all custom countries prices 

global $wpdb;

$wpdb->query( "DELETE FROM " . $wpdb->postmeta . " WHERE meta_key LIKE '_group_level_%_price'" );

?>
