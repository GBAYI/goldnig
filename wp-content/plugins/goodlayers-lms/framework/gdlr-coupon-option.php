<?php
	/*	
	*	Goodlayers Coupon Option file
	*	---------------------------------------------------------------------
	*	This file creates all coupon options and attached to the theme
	*	---------------------------------------------------------------------
	*/

	// add action to create coupon post type
	add_action( 'init', 'gdlr_lms_create_coupon' );
	if( !function_exists('gdlr_lms_create_coupon') ){
		function gdlr_lms_create_coupon() {
			register_post_type( 'coupon',
				array(
					'labels' => array(
						'name'               => __('Coupon', 'gdlr-lms'),
						'singular_name'      => __('Coupon', 'gdlr-lms'),
						'add_new'            => __('Add New', 'gdlr-lms'),
						'add_new_item'       => __('Add New Coupon', 'gdlr-lms'),
						'edit_item'          => __('Edit Coupon', 'gdlr-lms'),
						'new_item'           => __('New Coupon', 'gdlr-lms'),
						'all_items'          => __('All Coupon', 'gdlr-lms'),
						'view_item'          => __('View Coupon', 'gdlr-lms'),
						'search_items'       => __('Search Coupon', 'gdlr-lms'),
						'not_found'          => __('No coupon found', 'gdlr-lms'),
						'not_found_in_trash' => __('No coupon found in Trash', 'gdlr-lms'),
						'parent_item_colon'  => '',
						'menu_name'          => __('Coupon', 'gdlr-lms')
					),
					'public'             => true,
					'publicly_queryable' => true,
					'show_ui'            => true,
					'show_in_menu'       => true,
					'query_var'          => true,
					'rewrite'            => false,
					'capability_type'    => 'post',
					'has_archive'        => true,
					'hierarchical'       => false,
					'menu_position'      => 5,
					'supports'           => array( 'title', 'custom-fields' )
				)
			);
			
			// add filter to style single template
			add_filter('single_template', 'gdlr_lms_register_coupon_template');
		}
	}	
	
	if( !function_exists('gdlr_lms_register_coupon_template') ){
		function gdlr_lms_register_coupon_template($template){
			if( get_post_type() == 'coupon' ){
				$template = GDLR_LOCAL_PATH . '/404.php';
			}
			return $template;
		}
	}
	
	// enqueue the necessary admin script
	add_action('admin_enqueue_scripts', 'gdlr_lms_coupon_script');
	function gdlr_lms_coupon_script() {
		global $post; if( empty($post) || $post->post_type != 'coupon' ) return;
		
		gdlr_lms_include_jquery_ui_style();
		wp_enqueue_style('gdlr-lms-meta-box', plugins_url('/stylesheet/meta-box.css', __FILE__));

		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('gdlr-lms-meta-box', plugins_url('/javascript/meta-box.js', __FILE__));
	}	
	
	// add the coupon option
	add_action('add_meta_boxes', 'gdlr_lms_add_coupon_meta_box');
	add_action('pre_post_update', 'gdlr_lms_save_coupon_meta_box');
	function gdlr_lms_add_coupon_meta_box(){
		add_meta_box('course-option', __('Coupon Option', 'gdlr-lms'),
			'gdlr_lms_create_coupon_meta_box', 'coupon', 'normal', 'high');
	}
	function gdlr_lms_create_coupon_meta_box(){
		global $post;

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'coupon_meta_box', 'coupon_meta_box_nonce' );

		////////////////////////////////
		//// course setting section ////
		////////////////////////////////

		$coupon_settings = array(
			'coupon-code' => array(
				'title' => __('Coupon Code' , 'gdlr-lms'),
				'type' => 'text',
				'custom_field' => 'gdlr-coupon-code'
			),
			'coupon-amount' => array(
				'title' => __('Coupon Amount' , 'gdlr-lms'),
				'type' => 'text',
				'default' => -1,
				'description' => __('Fill -1 for unlimited uses', 'gdlr-lms')
			),	
			'coupon-expiry' => array(
				'title' => __('Coupon Expiry Date' , 'gdlr-lms'),
				'type' => 'datepicker'
			),
			'coupon-discount-type' => array(
				'title' => __('Coupon Discount Type' , 'gdlr-lms'),
				'type' => 'combobox',
				'options' => array(
					'percent' => __('Percent', 'gdlr-lms'),
					'amount' => __('Amount', 'gdlr-lms')
				)
			),
			'coupon-discount-amount' => array(
				'title' => __('Coupon Discount Amount' , 'gdlr-lms'),
				'type' => 'text',
				'description' => __('Only number is allowed here', 'gdlr-lms')
			),
			'specify-course' => array(
				'title' => __('Apply only to specific course ( course id separated by comma )' , 'gdlr-lms'),
				'type' => 'textarea'
			),
		);
		$coupon_val = gdlr_lms_decode_preventslashes(get_post_meta($post->ID, 'gdlr-lms-coupon-settings', true));
		$coupon_settings_val = empty($coupon_val)? array(): json_decode($coupon_val, true);

		echo '<div class="gdlr-lms-meta-wrapper">';
		foreach($coupon_settings as $slug => $coupon_setting){
			$coupon_setting['slug'] = $slug;
			if( !empty($coupon_setting['custom_field']) ){
				$coupon_setting['value'] = get_post_meta($post->ID, $coupon_setting['custom_field'], true);
			}
			if( empty($coupon_setting['value']) ){
				$coupon_setting['value'] = empty($coupon_settings_val[$slug])? '': $coupon_settings_val[$slug];
			}
			gdlr_lms_print_meta_box($coupon_setting);
		}
		echo '<textarea name="gdlr-lms-coupon-settings">' . esc_textarea($coupon_val) . '</textarea>';
		echo '</div>';
	}
	function gdlr_lms_save_coupon_meta_box($post_id){

		// verify nonce & user's permission
		if(!isset($_POST['coupon_meta_box_nonce'])){ return; }
		if(!wp_verify_nonce($_POST['coupon_meta_box_nonce'], 'coupon_meta_box')){ return; }
		if(!current_user_can('edit_post', $post_id)){ return; }

		// save value
		if( isset($_POST['gdlr-lms-coupon-settings']) ){
			$post_option = gdlr_lms_preventslashes(gdlr_stripslashes($_POST['gdlr-lms-coupon-settings']));
			$post_option = json_decode(gdlr_lms_decode_preventslashes($post_option), true);
			
			if( !empty($post_option) ){
				update_post_meta($post_id, 'gdlr-lms-coupon-settings', gdlr_lms_preventslashes($_POST['gdlr-lms-coupon-settings']));
			}
			if( !empty($post_option['coupon-code']) ){
				update_post_meta($post_id, 'gdlr-coupon-code', $post_option['coupon-code']);
			}
		}
		
		if( isset($_POST['gdlr-lms-content-settings']) ){
			update_post_meta($post_id, 'gdlr-lms-content-settings', gdlr_lms_preventslashes($_POST['gdlr-lms-content-settings']));
		}

	}
	
	
?>