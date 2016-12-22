<?php
	/*	
	*	Goodlayers Framework File
	*	---------------------------------------------------------------------
	*	This file contains the homepage loading button in page option area
	*	---------------------------------------------------------------------
	*/
	
	add_action('add_meta_boxes', 'gdlr_lms_init_course_bkup');
	if( !function_exists('gdlr_lms_init_course_bkup') ){
		function gdlr_lms_init_course_bkup(){
			add_meta_box( 'course-bkup-option', 
				__('Revert To Last Working Data', 'good-lms'), 
				'gdlr_lms_create_course_bkup_option',
				'course',
				'side',
				'default'
			);
			
			add_meta_box( 'course-bkup-option', 
				__('Revert To Last Working Data', 'good-lms'), 
				'gdlr_lms_create_course_bkup_option',
				'quiz',
				'side',
				'default'
			);			
		}
	}
	
	if( !function_exists('gdlr_lms_create_course_bkup_option') ){
		function gdlr_lms_create_course_bkup_option(){
			global $post;

			$course_content_val = get_post_meta($post->ID, 'gdlr-lms-content-settings', true);
			$course_content_options_val = empty($course_content_val)? array(): gdlr_lms_decode_preventslashes(json_decode($course_content_val, true));
			if( !empty($course_content_options_val) ){
				update_post_meta($post->ID, 'gdlr-lms-bkup-content', $course_content_val);
			}
			
			echo '<div id="gdlr-lms-bkup-wrapper" data-ajax="' . esc_url(AJAX_URL) . '" data-id="' . $post->ID . '" data-action="load_bkup_data">';
			echo '<em>';
			echo __('*This option helps you restore the last working data when the content disappear. Note that to use this option will replace all your current page item setting in this page and <strong>This Can\'t Be Undone</strong>.', 'good-lms');
			echo '</em><div class="clear"></div>';
			echo '<input type="button" value="' . __('Restore Lost Data', 'good-lms') . '" />';
			echo '</div>';

		}
	}
	
	add_action('wp_ajax_load_bkup_data', 'gdlr_lms_load_bkup_data');
	if( !function_exists('gdlr_lms_load_bkup_data') ){
		function gdlr_lms_load_bkup_data(){
			
			// verify user's permission
			if(!current_user_can('edit_post', $_POST['post_id'])) return;
			
			if(!empty($_POST['post_id'])){
				$bkup_content = get_post_meta($_POST['post_id'], 'gdlr-lms-bkup-content', true);
				if(!empty($bkup_content)){
					update_post_meta($_POST['post_id'], 'gdlr-lms-content-settings', $bkup_content);
				}
			}
			
			die();
		}
	}

?>