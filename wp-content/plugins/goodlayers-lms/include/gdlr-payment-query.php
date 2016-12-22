<?php
	/*	
	*	Goodlayers Payment Table Query File
	*/		
	
	// redirect to payment page
	add_filter('home_template', 'gdlr_lms_register_payment_template');
	add_filter('page_template', 'gdlr_lms_register_payment_template');
	add_filter('paged_template', 'gdlr_lms_register_payment_template');
	function gdlr_lms_register_payment_template($template){
		if( !empty($_GET['payment-method']) && $_GET['payment-method'] == 'stripe' ){
			$template = dirname(dirname( __FILE__ )) . '/single-stripe.php';
		}else if( !empty($_GET['payment-method']) && $_GET['payment-method'] == 'paymill' ){
			$template = dirname(dirname( __FILE__ )) . '/single-paymill.php';
		}else if( !empty($_GET['payment-method']) && $_GET['payment-method'] == 'authorize' ){
			$template = dirname(dirname( __FILE__ )) . '/single-authorize.php';
		}else if( !empty($_GET['payment-method']) && $_GET['payment-method'] == 'braintree' ){
			$template = dirname(dirname( __FILE__ )) . '/single-braintree.php';
		}
		return $template;
	}	
	
	// if payment or booked record exists
	function gdlr_lms_payment_row_exists($course_id, $student_id){
		if( empty($course_id) || empty($student_id) ) return;
		global $wpdb;
	
		$sql  = 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'gdlrpayment ';
		$sql .= 'WHERE course_id=' . $course_id . ' AND student_id=' . $student_id;

		return $wpdb->get_var($sql);
	}
	
	// return the row of specific course payment
	function gdlr_lms_get_payment_row($course_id, $student_id, $column = '*'){
		if( empty($course_id) || empty($student_id) ) return;
		
		global $wpdb;
		
		$sql  = 'SELECT ' . $column . ' FROM ' . $wpdb->prefix . 'gdlrpayment ';
		$sql .= 'WHERE course_id=' . $course_id . ' AND student_id=' . $student_id;
		
		return $wpdb->get_row($sql);	
	}
	
	// return the row of specific quiz
	function gdlr_lms_get_quiz_row($quiz_id, $course_id, $student_id, $column = '*'){	
		if( empty($course_id) || empty($student_id) ) return;
	
		global $wpdb;		
		
		$sql  = 'SELECT ' . $column . ' FROM ' . $wpdb->prefix . 'gdlrquiz ';
		$sql .= 'WHERE quiz_id=' . $quiz_id . ' AND student_id=' . $student_id . ' AND course_id=' . $course_id;

		return $wpdb->get_row($sql);	
	}
?>