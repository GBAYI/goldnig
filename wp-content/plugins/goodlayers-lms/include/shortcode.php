<?php	
	
	// social shortcode
	add_shortcode('gdlr_lms_social', 'gdlr_lms_social_shortcode');
	function gdlr_lms_social_shortcode( $atts ){
		extract( shortcode_atts(array('type' => 'facebook', 'url' => ''), $atts) );	

		$icon_url = plugins_url('social-icon-color/' . $type . '.png', dirname(__FILE__));
		return '<a class="lms-social-shortcode" target="_blank" href="' . $url . '"><img src="' . $icon_url . '" alt="' . $type . '"/></a>';
	}	

	// login button
	add_shortcode('lms_login', 'gdlr_lms_login_shortcode');
	function gdlr_lms_login_shortcode( $atts ){
		ob_start();
		gdlr_lms_header_signin();
		$ret = ob_get_contents();
		ob_end_clean();
		
		return $ret;
	}	
	
	// course search
	add_shortcode('lms_course_search', 'gdlr_lms_course_search_shortcode');
	function gdlr_lms_course_search_shortcode( $atts ){	
		ob_start();
		gdlr_lms_print_course_search(array());
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}

	// course item
	add_shortcode('lms_course', 'gdlr_lms_course_shortcode');
	function gdlr_lms_course_shortcode( $atts ){	
		extract( shortcode_atts(array('category' => '', 'style' => 'grid', 'column'=>'3', 'course_id'=>'', 
			'num_fetch'=>6, 'thumbnail_size'=>'full', 'orderby'=>'date', 'order'=>'desc', 'pagination'=>'yes'), $atts) );
		
		$settings = array(
			'course_id'=> (empty($course_id)? '': explode(',', $course_id)),
			'category'=>$category,
			'course-style'=>$style,
			'course-size'=>$column,
			'num-fetch'=>$num_fetch,
			'thumbnail-size'=>$thumbnail_size,
			'orderby'=>$orderby,
			'order'=>$order,
			'course-layout'=>'fitRows'
		);
		$settings['pagination'] = ($pagination=='yes')? 'enable': 'disable';
		
		ob_start();
		gdlr_lms_print_course_item($settings);
		$ret = ob_get_contents();
		ob_end_clean();		
		return $ret;
	}

	// instructor item
	add_shortcode('lms_instructor', 'gdlr_lms_instructor_shortcode');
	function gdlr_lms_instructor_shortcode( $atts ){	
		extract( shortcode_atts(array('name' => '', 'style' => 'grid', 'thumbnail_size'=>'full', 'num_excerpt'=>'20'), $atts) );
		
		$settings = array(
			'user'=>$name,
			'instructor-style'=>$style,
			'instructor-size'=>1,
			'num-fetch'=>1,
			'num-excerpt'=>$num_excerpt,
			'thumbnail-size'=>$thumbnail_size,
			'instructor-type'=>'single',
			'instructor-layout'=>'fitRows'
		);

		ob_start();
		gdlr_lms_print_instructor_item($settings);
		$ret = ob_get_contents();
		ob_end_clean();		
		return $ret;
	}	
	
	// instructor item
	add_shortcode('lms_instructors', 'gdlr_lms_instructors_shortcode');
	function gdlr_lms_instructors_shortcode( $atts ){	
		extract( shortcode_atts(array('role' => '', 'style' => 'grid', 'column'=>'3',
			'num_fetch'=>6, 'thumbnail_size'=>'full', 'num_excerpt'=>'20', 'pagination'=>'yes'), $atts) );
		
		$settings = array(
			'role'=>$role,
			'instructor-style'=>$style,
			'instructor-size'=>$column,
			'num-fetch'=>$num_fetch,
			'num-excerpt'=>$num_excerpt,
			'thumbnail-size'=>$thumbnail_size,
			'instructor-type'=>'multiple',
			'instructor-layout'=>'fitRows'
		);
		// $settings['pagination'] = ($pagination=='yes')? 'enable': 'disable';
		
		ob_start();
		gdlr_lms_print_instructor_item($settings);
		$ret = ob_get_contents();
		ob_end_clean();		
		return $ret;
	}	
	
	/* ADD SHORTCODE TO VISUAL EDITOR */

	// add filter to include the goodlayers shortcode to tinymce
	add_action('init', 'gdlr_lms_add_tinymce_button');
	function gdlr_lms_add_tinymce_button() {
		add_filter('mce_external_plugins', 'gdlr_lms_register_tinymce_button_script');
		add_filter('mce_buttons', 'gdlr_lms_register_tinymce_button');
	}

	// register the script to tinymce
	function gdlr_lms_register_tinymce_button_script($plugin_array) {
		$plugin_array['gdlr_lms'] =	plugins_url('javascript/shortcode.js', __FILE__);
		
		return $plugin_array;
	}

	// add the button to tinymce
	function gdlr_lms_register_tinymce_button($buttons) {
		array_push($buttons, 'gdlr_lms');
		return $buttons;
	}
	
	add_action('admin_print_scripts', 'gdlr_lms_print_shortcodes_variable');
	function gdlr_lms_print_shortcodes_variable(){
		?>
<script type="text/javascript">
var gdlr_lms_shortcodes = [
{	title: 'Talk', 
	value: '[lms_course category="CATEGORY_SLUG" style="grid/grid-2/medium/full" column="3" num_fetch="6" thumbnail_size="full" orderby="date" order="desc" pagination="yes" ]' 
},
{	title: 'Talk Search', 
	value: '[lms_course_search]'
},
{	title: 'Instructor', 
	value: '[lms_instructor name="INSTRUCTOR_ID" style="grid/grid-2" num_excerpt="20" thumbnail_size="full" ]'
},
{	title: 'Instructors', 
	value: '[lms_instructors role="instructor" style="grid/grid-2" column="3" num_fetch="6" num_excerpt="20" thumbnail_size="full" ]'
},
{	title: 'Login Button', 
	value: '[lms_login]'
}
];
</script>
	<?php
	}	

?>