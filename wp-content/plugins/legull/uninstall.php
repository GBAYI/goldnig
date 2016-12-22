<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

// remove all Legull artifacts to cleanup on install
$legull_docs = new WP_Query(array(
	'post_type' => 'legull_terms',
	'post_status' => 'any'
	));
foreach( $legull_docs->posts as $doc ) {
	wp_delete_post( $doc->ID, true );
}
delete_option( 'Legull' );