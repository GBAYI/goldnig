<?php

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_ajax_legull_hide_dashboard_message', 'legull_ajax_hide_dashboard_message' );
add_action( 'wp_ajax_legull_tracking_disallow', 'legull_pluginlytics_hide_tracking_notice' );
add_action( 'wp_ajax_legull_tracking_allow', 'legull_pluginlytics_hide_tracking_notice' );
add_filter( 'admin_body_class', 'legull_admin_body_class' );
add_shortcode( 'legull', 'legull_shortcode' );
add_shortcode( 'legull_var', 'legull_shortcode_fake' );
add_shortcode( 'legull_part', 'legull_shortcode_fake' );
add_shortcode( 'legull_condition', 'legull_shortcode_fake' );

function legull_ajax_hide_dashboard_message(){
	update_option( 'Legull_Hide_Dashboard_Message', 'yes' );
	echo 'yes';
	wp_die();
}

function legull_pluginlytics_hide_tracking_notice( $allow_tracking = 'no' ) {
	if( !empty( $_POST['allow'] ) ){
		$allow_tracking = $_POST['allow'];
	}
	update_option( 'pluginlytics_allow_tracking', $allow_tracking );
	echo 'yes';
	wp_die();
}


function legull_admin_body_class( $classes ){
	global $current_screen;
	if ( is_admin() && strpos( $current_screen->id, 'legull' ) !== false ){
		$classes .= ' legull-admin '; 
	}
	return $classes;
}

function legull_custom_activation_message( $translated_text, $untranslated_text, $domain ){
	$old_activation_message = array(
        "Plugin <strong>activated</strong>.",
        "Selected plugins <strong>activated</strong>." 
    );

    if ( in_array( $untranslated_text, $old_activation_message, true ) ){
        $translated_text = sprintf( '%s <b><a href="%s">%s</a></b>',
        		__('Thank you for activating Legull, your legal terms management solution.', 'legull'),
        		get_admin_url() . 'admin.php?page=legull_dashboard',
        		__('Get Started', 'legull')
        		);
        remove_filter( current_filter(), __FUNCTION__, 99 );
    }

	return $translated_text;
}

function legull_enqueue_scripts(){
	wp_enqueue_script( 'legull', LEGULL_URL . 'asset/legull-scripts.js', array( 'jquery' ), '1.0', true );
}

function legull_enqueue_admin_scripts() {
	// add_thickbox();
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_style( "wp-jquery-ui-dialog" );
	wp_enqueue_script( 'jquery-readmore', LEGULL_URL . 'asset/readmore.min.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'legull', LEGULL_URL . 'asset/legull-admin-scripts.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_style( 'legull', LEGULL_URL . 'asset/style.css' );
}

function  legull_icon( $size = 16, $base64 = false ) {
	if ( $base64 ) {
		$file = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDUxMiA1MTIiIGhlaWdodD0iNTEycHgiIGlkPSJMYXllcl8xIiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCA1MTIgNTEyIiB3aWR0aD0iNTEycHgiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPjxwYXRoIGQ9Ik00MDcuNjU4LDI1Ny4xOTFDNDQyLjA0NSwyMTEuODM4LDQzOC4yOTcsMTUxLjk3LDQ4MSw1My4yMzJDMjU0LjQwNyw1MC4yNDQsMTU2LjE2MywyMDYuNTQsMTE0LjM2MiwzNjMuMzcgIEwzMSw0NTguODExbDU5LjM4OS0xNi41NDZsNDcuODMzLTYzLjc4NmMxMzAuMzYzLTE2LjYzMSwyMzguMTc3LTg4Ljg3MywyMzguMTc3LTg4Ljg3M2wtNzAuMTU2LTUxLjQ5NyAgQzMwNi4yNDIsMjM4LjEwOCwzNjAuOTgsMjM0LjE5LDQwNy42NTgsMjU3LjE5MXogTTE3MC43NjQsMzI5Ljk0bC0xLjE5OC0xLjMyNGMxOC41ODktMjkuMjAxLDExOC43NS0xNzguOTI5LDI1NC4wNDYtMjM3LjY5OSAgQzM1OS4yMDUsMTI0Ljk2NywyMjUuNjg2LDI1OC44MjYsMTcwLjc2NCwzMjkuOTR6IiBmaWxsPSIjNEQ0RDREIi8+PC9zdmc+';
	} else {
		$file = LEGULL_URL . "asset/icon-{$size}.png";
	}

	return $file;
}

function legull_publish_terms_to_import() {
	global $shortcode_tags;
	$status          = false;
	$tagnames        = array_keys( $shortcode_tags );
	$tagregexp       = join( '|', array_map( 'preg_quote', $tagnames ) );
	$shortcode_regex = get_shortcode_regex();
	$docs            = apply_filters( 'legull_publish_terms_to_import/list', glob( LEGULL_PATH . "docs/*.md" ) );
	include_once( LEGULL_PATH . 'lib/parsedown.php' );
	$Parsedown = new Parsedown();
	foreach ( $docs as $filename ) {
		$import_file     = basename( $filename );
		$content         = file_get_contents( $filename );
		$check_if_exists = new WP_Query( array( 'meta_key' => 'legull_file', 'meta_value' => $import_file, 'post_type' => LEGULL_CPT ) );

		$import_post = array(
			'post_type'      => LEGULL_CPT,
			'post_status'    => 'publish',
			'ping_status'    => 'closed',
			'comment_status' => 'closed'
		);
		if ( count( $check_if_exists->posts ) ) {
			$import_post['ID'] = $check_if_exists->posts[0]->ID;
		}

		// setup defaults
		$post_title = $import_file;

		$look_for_shortcode = 'legull_part';
		if ( has_shortcode( $content, $look_for_shortcode ) ) {
			$legull_part_regex_pattern = str_replace( $tagregexp, $look_for_shortcode, $shortcode_regex );
			$content                   = preg_replace_callback( '/' . $legull_part_regex_pattern . '/s', 'legull_shortcode_part_include', $content );
		}

		$look_for_shortcode = 'legull_condition';
		if ( has_shortcode( $content, $look_for_shortcode ) ) {
			$legull_part_regex_pattern = str_replace( $tagregexp, $look_for_shortcode, $shortcode_regex );
			$content                   = preg_replace_callback( '/' . $legull_part_regex_pattern . '/s', 'legull_shortcode_condition', $content );
		}

		$look_for_shortcode = 'legull_var';
		if ( has_shortcode( $content, $look_for_shortcode ) ) {
			$legull_var_regex_pattern = str_replace( $tagregexp, $look_for_shortcode, $shortcode_regex );
			preg_match_all( '/' . $legull_var_regex_pattern . '/s', $content, $matches );

			// include space because attributes are not trimmed
			// set page title/name
			if ( in_array( ' name="title"', $matches[3] ) ) {
				$post_title = $matches[5][0];
			}

			// clean the content from [legull_var]
			$content = legull_strip_shortcode( $content, 'legull_var' );
		}

		$import_post['post_title']   = $post_title;
		$import_post['post_name']    = $post_title;
		$import_post['post_content'] = $Parsedown->text( $content );
		if( !empty( $import_post['post_title'] ) && !empty( $import_post['post_content'] ) ) {
			$document_id                 = wp_insert_post( $import_post );
			update_post_meta( $document_id, 'legull_file', $import_file );
			$status = true;
		} else if( !empty( $import_post['ID'] ) ) {
			// bypass the trash
			wp_delete_post( $import_post['ID'], true );
		}
	}

	return $status;
}

function legull_shortcode_part_include( $matches ) {
	// $matches = apply_filters( 'legull_shortcode_part_include/matches', $matches );
	$content = '';
	if ( !empty( $matches[5] ) ) {
		$part_path = LEGULL_PATH . "docs/part";
		$file      = apply_filters( 'legull_shortcode_part_include/file', $part_path . '/' . $matches[5] . '.md', $part_path, $matches[5] . '.md' );
		if ( !empty( $file ) && file_exists( $file ) ) {
			$content = file_get_contents( $file );
		}
	}

	return apply_filters( 'legull_shortcode_part_include/content', $content );
}

function legull_shortcode_condition( $matches ) {
	// $matches = apply_filters( 'legull_shortcode_part_include/matches', $matches );
	$content = '';
	if ( preg_match( "/(.*)=[\"|'](.*)[\"|']/", $matches[3], $condition ) && $content = $matches[5] ) {
		$condition_value = legull_get_var( trim( $condition[2] ) );
		if (
			( trim( $condition[1] ) == 'is' && $condition_value ) ||
			( trim( $condition[1] ) == 'isnot' && !$condition_value )
		) {
			$content = $matches[5];
		} else {
			$content = '';
		}
	}

	return apply_filters( 'legull_shortcode_condition/content', $content );
}

function legull_seek_option( $haystack, $needle ) {
	$output = '';
	foreach ( (array) $haystack as $key => $value ) {
		if ( $key == $needle ) {
			$output = $value;
		} elseif ( is_array( $value ) ) {
			$output = legull_seek_option( $value, $needle );
		}
	}

	return $output;
}

function legull_get_value( $field_id, $section = null ) {
	$response = null;
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		$options  = get_option( 'Legull' );
		$response = legull_seek_option( $options, $field_id );
	} else {
		global $legull;
		if ( $section == null ) {
			$response = $legull->getValue( $field_id );
		} else {
			$response = $legull->getValue( $section, $field_id );
		}
	}

	return $response;
}

function legull_get_var( $field_id ) {
	$value = null;
	$section = legull_get_var_section( $field_id );
	switch ( $field_id ) {
		case 'last_updated':
			$value = date( get_option('date_format', 'F jS, Y'), legull_get_value( $field_id, $section ));
			break;
		case 'siteurl':
			$link  = legull_get_value( $field_id, $section );
			$value = sprintf( '<a href="%s">%s</a>', $link, $link );
			break;
		case 'sitename':
		case 'owner_name':
		case 'owner_email':
		case 'owner_locality':
		case 'entity_type':
		case 'privacy_name':
		case 'privacy_email':
		case 'privacy_address':
		case 'DMCA_address':
		case 'DMCA_telephone':
		case 'DMCA_email':
		case 'support_email':
		case 'support_phone':
			$value = legull_get_value( $field_id, $section );
			break;
		case 'has_support_contact':
			$boolean = legull_get_value( $field_id, $section );
			$value = reset($boolean) == 1 ? true : false;
			break;
		case 'has_DMCA_agent':
		case 'has_advertising':
		case 'has_california':
		case 'has_cookies':
		case 'has_info_track':
		case 'has_personalization':
		case 'has_anonymous':
		case 'has_purchased_data':
		case 'has_data_buyer':
		case 'has_collectdata':
		case 'has_sharedata':
		case 'has_sharedata_aggregate':
		case 'has_sharedata_helpers':
		case 'has_sharedata_ads':
		case 'has_sharedata_unlimited':
		case 'has_usergenerated':
		case 'has_3p_content':
		case 'has_advertising_network':
		case 'has_advertising_adsense':
		case 'has_over18':
		case 'has_no13':
		case 'has_arbitration':
		case 'has_SSL':
		case 'has_no_scrape':
		case 'has_password':
			$boolean_value = legull_get_value( $field_id, $section );
			$value = legull_check_if_really_true( $boolean_value, $field_id );
			break;
	}

	return $value;
}

function legull_check_if_really_true( $value_check = null, $field_id = null ){
	$status = false;
	
	if( $value_check == 1 ){
		$status = true;
	}

	if( !$status && $field_id != null && strpos( $field_id, 'has_' ) !== false && $value_check != 'NO' && $value_check != '' ){
		$status = true;
	}

	return $status;
}

function legull_get_var_section( $field_id ){
	$section = null;
	$sections = array(
		'ownership' => array('siteurl','sitename','owner_name','owner_email','owner_locality','entity_type'),
		'tracking' => array('has_california','privacy_name','privacy_email','privacy_address','has_cookies','has_info_track','has_personalization','has_anonymous','has_purchased_data','has_data_buyer','has_collectdata','has_sharedata','has_sharedata_aggregate','has_sharedata_helpers','has_sharedata_ads','has_sharedata_unlimited'),
		'usercontent' => array('has_usergenerated','has_3p_content','has_DMCA_agent','DMCA_address','DMCA_telephone','DMCA_email'),
		'advertising' => array('has_advertising','has_advertising_network','has_advertising_adsense'),
		'misc' => array('has_over18','has_no13','has_arbitration','has_SSL','has_support_contact','support_email','support_phone','last_updated','has_no_scrape','has_password')
		);
	foreach( $sections as $key => $fields ){
		if( in_array($field_id, $fields) ){
			$section = $key;
		}
	}
	return $section;
}

function legull_integrated_plugins(){
	$plugins = array();
	// check if gravity forms is active
	if( class_exists( 'GFCommon' ) ){
		$plugins[] = 'gravityforms';
	}
	return $plugins;
}

function legull_shortcode( $atts, $content = null ) {
	$a = shortcode_atts(
		array(
			'display' => ''
		), $atts
	);

	return legull_get_var( $a['display'] );
}

function legull_shortcode_fake() {
	return '';
}

function legull_strip_shortcode( $content, $shortcode ) {
	global $shortcode_tags;

	$stack          = $shortcode_tags;
	$shortcode_tags = array( $shortcode => 1 );
	$content        = strip_shortcodes( $content );
	$shortcode_tags = $stack;

	return $content;
}

// conditional to check whether Gravity Forms shortcode is on a page
function legull_has_gform() {
     global $post;
     $all_content = get_the_content();
	if (strpos($all_content,'[gravityform') !== false) {
		return true;
	} else {
		return false;
	}
}

function legull_get_terms_content( $strip_tags = false ){

	$content = '';

	$terms_args = array(
		'post_type' => LEGULL_CPT,
		'post_status' => 'publish',
		'posts_per_page'         => 1,
		'meta_key'       => 'legull_file',
		'meta_value'     => 'terms-of-service.md',
	);
	
	$terms = new WP_Query( $terms_args );
	
	if( count( $terms->posts ) > 0 ){
		$content = apply_filters( 'the_content', $terms->posts[0]->post_content );
		if( $strip_tags )
			$content = wp_strip_all_tags( $content );
	}

	return $content;
}

function legull_get_terms_link(){
	return sprintf( "<a href='%s' target='_blank'>%s</a>", '#', __( 'Terms & Conditions', 'legull' ) );
}
