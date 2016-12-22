<?php
	/*
	*	Goodlayers Talk Option File
	*/

	// create the course post type
	add_action( 'init', 'gdlr_lms_create_cause' );
	function gdlr_lms_create_cause() {
		register_post_type( 'course',
			array(
				'labels' => array(
					'name'               => __('Talk', 'gdlr-lms'),
					'singular_name'      => __('Talk', 'gdlr-lms'),
					'add_new'            => __('Add New', 'gdlr-lms'),
					'add_new_item'       => __('Add New Talk', 'gdlr-lms'),
					'edit_item'          => __('Edit Talk', 'gdlr-lms'),
					'new_item'           => __('New Talk', 'gdlr-lms'),
					'all_items'          => __('All Talks', 'gdlr-lms'),
					'view_item'          => __('View Talk', 'gdlr-lms'),
					'search_items'       => __('Search Talk', 'gdlr-lms'),
					'not_found'          => __('No courses found', 'gdlr-lms'),
					'not_found_in_trash' => __('No courses found in Trash', 'gdlr-lms'),
					'parent_item_colon'  => '',
					'menu_name'          => __('Talks', 'gdlr-lms')
				),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'talk'  ), /*line originally commented: slug = course */
				'capability_type'    => array('course', 'courses'),
				'map_meta_cap' 		 => true,
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 5,
				'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields', 'revisions' )
			)
		);

		// create course categories
		register_taxonomy(
			'course_category', array("course"), array(
				'hierarchical' => true,
				'show_admin_column' => true,
				'label' => __('Talk Categories', 'gdlr-lms'),
				'singular_label' => __('Talk Category', 'gdlr-lms'),
				'rewrite' => array('slug' => 'course_category'),
				'capabilities' => array('manage_terms'=>'course_taxes', 'edit_terms'=>'course_taxes_edit',
					'delete_terms'=>'course_taxes_edit', 'assign_terms'=>'course_taxes')
				));
		register_taxonomy_for_object_type('course_category', 'course');

		// create course tag
		register_taxonomy(
			'course_tag', array('course'), array(
				'hierarchical' => false,
				'show_admin_column' => true,
				'label' => __('Talk Tags', 'gdlr-lms'),
				'singular_label' => __('Talk Tag', 'gdlr-lms'),
				'rewrite' => array( 'slug' => 'course_tag' ),
				'capabilities' => array('manage_terms'=>'course_taxes', 'edit_terms'=>'course_taxes',
					'delete_terms'=>'course_taxes', 'assign_terms'=>'course_taxes')
				));
		register_taxonomy_for_object_type('course_tag', 'course');

		add_filter('single_template', 'gdlr_lms_register_course_template');
	}

	// register single course template
	function gdlr_lms_register_course_template($template) {
		global $post, $current_user;

		if( $post->post_type == 'course' ){
			$template = '';
			
			if( isset($_GET['course_type']) ){
				global $wpdb, $gdlr_course_settings, $gdlr_course_options, $lms_page, $lms_lecture, $payment_row;
				$authorization = false;
				
				$gdlr_course_options = gdlr_lms_get_course_options($post->ID);
				$gdlr_course_settings = gdlr_lms_get_course_content_settings($post->ID);
				$lms_page = (empty($_GET['course_page']))? 1: intval($_GET['course_page']);
				$lms_lecture = (empty($_GET['lecture']))? 1: intval($_GET['lecture']);
				
				if( !is_user_logged_in() && !empty($gdlr_course_options['allow-non-member']) && $gdlr_course_options['allow-non-member'] == 'enable' &&
					(empty($course_options['online-course']) || $course_options['online-course'] == 'enable') ){
					$authorization = true;
				}else if( is_user_logged_in() ){
					if( $current_user->ID == $post->post_author ){
						$authorization = true;
					}else{
						
						
						
						if( !empty($gdlr_course_options['lock-course-date']) && $gdlr_course_options['lock-course-date'] == 'enable' && 
							(( !empty($gdlr_course_options['start-date']) && strtotime(date('Y-m-d')) < strtotime($gdlr_course_options['start-date']) ) ||
							 ( !empty($gdlr_course_options['end-date']) && strtotime(date('Y-m-d')) > strtotime($gdlr_course_options['end-date']) )) ){
							$authorization = false;
						}else{
					
							// check if purchase before
							$payment_row = gdlr_lms_get_payment_row($post->ID, $current_user->ID);
							
							if(!empty($payment_row)){
								if( $payment_row->payment_status == 'paid' ){
									$authorization = true;

									if( $lms_page - 1 > $payment_row->attendance_section ){
										$lms_page = $payment_row->attendance_section + 1;
									}
									
									if( $payment_row->attendance_section < $lms_page){
										$current_date = strtotime(date('Y-m-d H:i:s'));
										
										if( !empty($gdlr_course_settings[$lms_page-2]['wait-time']) && $lms_page > 1 ){
											$available_date = strtotime($payment_row->attendance) + (intval($gdlr_course_settings[$lms_page-2]['wait-time']) * 86400);
										}else if( !empty($gdlr_course_settings[$lms_page-2]['wait-date']) && $lms_page > 1 ){
											$available_date = strtotime($gdlr_course_settings[$lms_page-2]['wait-date']);
										}else{
											$available_date = strtotime($payment_row->attendance);
										}
										
										if( $lms_page > 1 && $current_date <= $available_date ){
											global $gdlr_time_left;
											$gdlr_time_left = $available_date - $current_date;
										}else{
											$wpdb->update( $wpdb->prefix . 'gdlrpayment',
												array('attendance'=>date('Y-m-d H:i:s'), 'attendance_section'=>$lms_page), array('id'=>$payment_row->id),
												array('%s', '%d'), array('%d')
											);
											$payment_row->attendance_section = $payment_row->attendance_section + 1;
										}
									}
								}

							// check whether it is free course
							}else{
							
								if( empty($gdlr_course_options['price-one']) ){
									$authorization = true;

									$gdlr_course_options['booked-seat'] = intval($gdlr_course_options['booked-seat']) + 1;
									update_post_meta($post->ID, 'gdlr-lms-course-settings', json_encode($gdlr_course_options));

									$running_number = intval(get_post_meta($post->ID, 'student-booking-id', true));
									$running_number = empty($running_number)? 1: $running_number + 1;
									update_post_meta($post->ID, 'student-booking-id', $running_number);

									$code  = substr(get_user_meta($current_user->ID, 'first_name',true), 0, 1) . substr(get_user_meta($current_user->ID, 'last_name',true), 0, 1);
									$code .= $running_number . $gdlr_course_options['course-code'] . $post->ID;

									$data = serialize(array(
										'amount' => 1,
										'price' => 0,
										'code' => $code
									));

									$wpdb->insert( $wpdb->prefix . 'gdlrpayment',
										array('course_id'=>$post->ID, 'student_id'=>$current_user->ID, 'author_id'=>$post->post_author,
											'payment_date'=>date('Y-m-d H:i:s'), 'payment_status'=>'paid', 'price'=>'0',
											'payment_info'=>$data, 'attendance'=>date('Y-m-d H:i:s'), 'attendance_section'=>'1'),
										array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s')
									);
									
									$payment_row = gdlr_lms_get_payment_row($post->ID, $current_user->ID);
								}
							}
						}
					}
				}

				if($authorization){
					if($_GET['course_type'] == 'content'){
						$template .=  'single-course-content.php';
					}else if($_GET['course_type'] == 'quiz'){
						$template .=  'single-course-quiz.php';
					}else if($_GET['course_type'] == 'section-quiz'){
						$template .=  'single-section-quiz.php';
					}
				}
			}
			$template = empty($template)? 'single-course.php': $template;
			$template = dirname(dirname( __FILE__ )) . '/' . $template;

		}else if( $post->post_type == 'quiz' ){
			if( $current_user->ID == $post->post_author ){
				$template = dirname(dirname( __FILE__ )) . '/single-course-quiz.php';
			}else{
				$template = get_template_directory() . '/404.php';
			}
		}

		return $template;
	}

	// enqueue the necessary admin script
	add_action('admin_enqueue_scripts', 'gdlr_lms_course_script');
	function gdlr_lms_course_script() {
		global $post; if( empty($post) || $post->post_type != 'course' ) return;
		
		gdlr_lms_include_jquery_ui_style();
		wp_enqueue_style('gdlr-lms-meta-box', plugins_url('/stylesheet/meta-box.css', __FILE__));

		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('gdlr-lms-meta-box', plugins_url('/javascript/meta-box.js', __FILE__));
	}

	// add the course option
	add_action('add_meta_boxes', 'gdlr_lms_add_course_meta_box');
	add_action('pre_post_update', 'gdlr_lms_save_course_meta_box');
	function gdlr_lms_add_course_meta_box(){
		add_meta_box('course-option', __('Talk Option', 'gdlr-lms'),
			'gdlr_lms_create_course_meta_box', 'course', 'normal', 'high');
	}
	function gdlr_lms_create_course_meta_box(){
		global $post;

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'course_meta_box', 'course_meta_box_nonce' );

		////////////////////////////////
		//// course setting section ////
		////////////////////////////////

		$course_settings = array(
			'right-sidebar' => array(
				'title' => __('Right Sidebar (If Any)', 'gdlr-lms'),
				'type' => 'combobox',
				'options' => gdlr_lms_get_sidebar_list()
			),
			'prerequisite-course' => array(
				'title' => __('Prerequisite Talk', 'gdlr-lms'),
				'type' => 'combobox',
				'options' => gdlr_lms_get_post_list('course')
			),
			'online-course' => array(
				'title' => __('Online Talk', 'gdlr-lms'),
				'type' => 'checkbox',
				'default' => 'enable',
				'description' => __('Talk content section will be ignored when this option is disabled.', 'gdlr-lms')
			),
			'allow-non-member' => array(
				'title' => __('Allow non member to read course', 'gdlr-lms'),
				'type' => 'checkbox',
				'default' => 'disable',
				'wrapper-class' => 'online-course-enable',
				'description' => __('The price, quiz, seat and wait time field will be omitted.', 'gdlr-lms')
			),
			'course-code' => array(
				'title' => __('Talk Code', 'gdlr-lms'),
				'type' => 'text',
				'description' => __('Use to generate code after submit payment evidence.', 'gdlr-lms')
			),
			'quiz' => array(
				'title' => __('Talk Final Quiz', 'gdlr-lms'),
				'type' => 'combobox',
				'options' => gdlr_lms_get_post_list('quiz'),
				'wrapper-class' => 'online-course-enable'
			),
			'location' => array(
				'title' => __('Location', 'gdlr-lms'),
				'type' => 'text',
				'class' => 'long',
				'wrapper-class' => 'online-course-disable'
			),
			'lock-course-date' => array(
				'title' => __('Lock Talk On Start / End Date', 'gdlr-lms'),
				'type' => 'checkbox',
				'default' => 'disable',
				'wrapper-class' => 'online-course-enable',
				'description' => __('only allow the course to be accessed on specific date range', 'gdlr-lms')
			),
			'start-date' => array(
				'title' => __('Start Date', 'gdlr-lms'),
				'type' => 'datepicker',
				'custom_field' => 'gdlr_course_start_date'
			),
			'end-date' => array(
				'title' => __('End Date', 'gdlr-lms'),
				'type' => 'datepicker',
			),
			'course-time' => array(
				'title' => __('Talk Time', 'gdlr-lms'),
				'type' => 'text',
				'wrapper-class' => 'online-course-disable'
			),
			'expired-date' => array(
				'title' => __('Expired Date', 'gdlr-lms'),
				'type' => 'datepicker',
				'description' => __('This option prevents student from purchasing the course after the selected date', 'gdlr-lms')
			),
			'max-seat' => array(
				'title' => __('Max Seat', 'gdlr-lms'),
				'type' => 'text',
				'wrapper-class' => 'online-course-disable'
			),
			'booked-seat' => array(
				'title' => __('Booked Seat', 'gdlr-lms'),
				'type' => 'text',
				'wrapper-class' => 'online-course-disable'
			),
			'price' => array(
				'title' => __('Price', 'gdlr-lms'),
				'type' => 'text',
				'class' => 'small',
				'description' => __('Leaving this field blankfor free course (Only number is allowed here)', 'gdlr-lms'),
			),
			'discount-price' => array(
				'title' => __('Discount Price', 'gdlr-lms'),
				'type' => 'text',
				'class' => 'small',
				'description' => __('(Only number is allowed here)', 'gdlr-lms')

			),

			// badge and certificate
			'enable-badge' => array(
				'title' => __('Enable Badge', 'gdlr-lms'),
				'type' => 'checkbox',
				'default' => 'disable',
				'wrapper-class' => 'online-course-enable'
			),
			'badge-percent' => array(
				'title' => __('% Of Score To Get Badge', 'gdlr-lms'),
				'type' => 'text',
				'wrapper-class' => 'online-course-enable'
			),
			'badge-title' => array(
				'title' => __('Badge Title', 'gdlr-lms'),
				'type' => 'text',
				'wrapper-class' => 'online-course-enable'
			),
			'badge-file' => array(
				'title' => __('Badge File', 'gdlr-lms'),
				'type' => 'upload',
				'wrapper-class' => 'online-course-enable'
			),
			'enable-certificate' => array(
				'title' => __('Enable Certificate', 'gdlr-lms'),
				'type' => 'checkbox',
				'default' => 'disable'
			),
			'certificate-percent' => array(
				'title' => __('% Of Score To Get Certificate', 'gdlr-lms'),
				'type' => 'text',
				'wrapper-class' => 'enable-certificate-enable'
			),
			'certificate-template' => array(
				'title' => __('Certificate Template', 'gdlr-lms'),
				'type' => 'combobox',
				'options' => gdlr_lms_get_post_list('certificate'),
				'wrapper-class' => 'enable-certificate-enable'
			),
		);
		$course_val = gdlr_lms_decode_preventslashes(get_post_meta($post->ID, 'gdlr-lms-course-settings', true));
		$course_settings_val = empty($course_val)? array(): json_decode($course_val, true);

		echo '<div class="gdlr-lms-meta-wrapper">';
		echo '<h3>' . __('Talk Settings', 'gdlr-lms') . '</h3>';
		foreach($course_settings as $slug => $course_setting){
			$course_setting['slug'] = $slug;
			if( !empty($course_setting['custom_field']) ){
				$course_setting['value'] = get_post_meta($post->ID, $course_setting['custom_field'], true);
			}
			if( empty($course_setting['value']) ){
				$course_setting['value'] = empty($course_settings_val[$slug])? '': $course_settings_val[$slug];
			}
			gdlr_lms_print_meta_box($course_setting);
		}
		echo '<textarea name="gdlr-lms-course-settings">' . esc_textarea($course_val) . '</textarea>';
		echo '</div>';

		/////////////////////
		//// tab section ////
		/////////////////////

		$course_content_options = array(
			'section-name' => array(
				'title' => __('Section Name', 'gdlr-lms'),
				'type' => 'text'
			),
			'section-quiz' => array(
				'title' => __('Section Quiz', 'gdlr-lms'),
				'type' => 'combobox',
				'options' => gdlr_lms_get_post_list('quiz')
			),
			'pass-mark' => array(
				'title' => __('Student have to get', 'gdlr-lms'),
				'type' => 'text',
				'class' => 'small',
				'description' => __('% to continue to next section.', 'gdlr-lms')
			),
			'wait-time' => array(
				'title' => __('Student have to wait', 'gdlr-lms'),
				'type' => 'text',
				'class' => 'small',
				'description' => __('days before continuing to next section.', 'gdlr-lms'),
			),
			'wait-date' => array(
				'title' => __('Next section will be available at', 'gdlr-lms'),
				'type' => 'datepicker',
				'description' => __('( Wait time has to be blank for this field to take effects )', 'gdlr-lms'),
			),
			
			'lecture-section' => array(
				'type' => 'lecture',
				'options' => array(
					'lecture-name' => array(
						'title' => __('Lecture Name', 'gdlr-lms'),
						'type' => 'text'
					),
					'icon-class' => array(
						'title' => __('Icon Class', 'gdlr-lms'),
						'type' => 'text'
					),
					'pdf-download-link' => array(
						'title' => __('PDF Download Link', 'gdlr-lms'),
						'type' => 'upload'
					),
					'allow-free-preview' => array(
						'title' => __('Allow Free Preview', 'gdlr-lms'),
						'type' => 'checkbox',
						'default' => 'disable'
					),
					'lecture-content' => array(
						'type' => 'wysiwyg'
					),
					
					// pdf-download-link, course-content
				)
			)

		);
		$course_content_val = gdlr_lms_decode_preventslashes(get_post_meta($post->ID, 'gdlr-lms-content-settings', true));
		$course_content_options_val = empty($course_content_val)? array(): json_decode($course_content_val, true);
		
		$old_data = false;
		foreach($course_content_options_val as $tabs_key => $tabs_value){
			if( empty($tabs_value['lecture-section']) ){
				$course_content_options_val[$tabs_key]['lecture-section'] = array(0=>array());
				if( !empty($tabs_value['pdf-download-link']) ){
					 $course_content_options_val[$tabs_key]['lecture-section'][0]['pdf-download-link'] = $tabs_value['pdf-download-link'];
				}
				if( !empty($tabs_value['course-content']) ){
					$course_content_options_val[$tabs_key]['lecture-section'][0]['lecture-content'] = $tabs_value['course-content'];
				}
				$course_content_options_val[$tabs_key]['lecture-section'] = json_encode($course_content_options_val[$tabs_key]['lecture-section']);
				$old_data = true;
			}
		}
		if( $old_data ){
			$course_content_val = json_encode($course_content_options_val);
		}
		
		echo '<div class="gdlr-lms-meta-wrapper gdlr-tabs">';
		echo '<h3>' . __('Talk Content', 'gdlr-lms') . '</h3>';
		echo '<div class="course-tab-add-new">';
		echo '<span class="head">+</span>';
		echo '<span class="tail">' . __('Add Section', 'gdlr-lms') . '</span>';
		echo '</div>'; // course-tab-add-new
		echo '<div class="course-tab-title">';
		echo '<span class="active">1</span>';
		for( $i = 2; $i <= sizeof($course_content_options_val); $i++ ){
			echo '<span>' . $i . '</span>';
		}
		echo '</div>'; // course-tab-title
		echo '<div class="course-tab-content">';
		echo '<div class="course-tab-remove">Delete</div>';
		foreach($course_content_options as $slug => $course_content_option){
			if( $course_content_option['type'] == 'lecture' ){
				$lectures = empty($course_content_options_val[0][$slug])? array(): json_decode($course_content_options_val[0][$slug], true);
				
				echo '<div class="gdlr-lecture-wrapper">';
				echo '<div class="lecture-tab-add-new">';
				echo '<span class="head">+</span>';
				echo '<span class="tail">' . __('Add Lecture', 'gdlr-lms') . '</span>';
				echo '</div>'; // lecture-tab-add-new
				echo '<div class="lecture-tab-title">';
				echo '<span class="active">1</span>';
				for( $i = 2; $i <= sizeof($lectures); $i++ ){
					echo '<span>' . $i . '</span>';
				}
				echo '</div>'; // lecture-tab-title
				
				echo '<div class="lecture-tab-content">';
				echo '<div class="lecture-tab-remove">Delete</div>';
				foreach($course_content_option['options'] as $l_slug => $lecture_content_option){
					$lecture_content_option['slug'] = $l_slug;
					$lecture_content_option['value'] = empty($lectures[0][$l_slug])? '': $lectures[0][$l_slug];
					gdlr_lms_print_meta_box($lecture_content_option);
				}
				echo '</div>'; // lecture-tab-content
				echo '<textarea type="text" class="gdlr-lms-lecture-content" data-slug="' . $slug . '" >';
				echo empty($course_content_options_val[0][$slug])? '': esc_textarea($course_content_options_val[0][$slug]);
				echo '</textarea>';
				echo '</div>'; // gdlr-lecture-wrapper
			}else{
				$course_content_option['slug'] = $slug;
				$course_content_option['value'] = empty($course_content_options_val[0][$slug])? '': $course_content_options_val[0][$slug];
				gdlr_lms_print_meta_box($course_content_option);
			}
		}
		echo '</div>'; // course-tab-content
		echo '<textarea name="gdlr-lms-content-settings">' . esc_textarea($course_content_val) . '</textarea>';
		echo '</div>';
	}
	function gdlr_lms_save_course_meta_box($post_id){

		// verify nonce & user's permission
		if(!isset($_POST['course_meta_box_nonce'])){ return; }
		if(!wp_verify_nonce($_POST['course_meta_box_nonce'], 'course_meta_box')){ return; }
		if(!current_user_can('edit_post', $post_id)){ return; }

		// save value
		if( isset($_POST['gdlr-lms-course-settings']) ){
			$post_option = gdlr_lms_preventslashes(gdlr_stripslashes($_POST['gdlr-lms-course-settings']));
			$post_option = json_decode(gdlr_lms_decode_preventslashes($post_option), true);
			
			if( !empty($post_option) ){
				update_post_meta($post_id, 'gdlr-lms-course-settings', gdlr_lms_preventslashes($_POST['gdlr-lms-course-settings']));
			}
			if( !empty($post_option['start-date']) ){
				update_post_meta($post_id, 'gdlr_course_start_date', $post_option['start-date']);
			}
		}
		
		if( isset($_POST['gdlr-lms-content-settings']) ){
			update_post_meta($post_id, 'gdlr-lms-content-settings', gdlr_lms_preventslashes($_POST['gdlr-lms-content-settings']));
		}

	}

?>
