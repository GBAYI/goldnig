<?php
	add_action( 'wp_enqueue_scripts', 'gdlr_lms_include_cloud_payment_script' );
	function gdlr_lms_include_cloud_payment_script(){
		global $gdlr_lms_option;
		if( !empty($gdlr_lms_option) && in_array('cloud', $gdlr_lms_option['instant-payment-method']) ){
			wp_enqueue_script('cloud-payment', '//widget.cloudpayments.ru/bundles/cloudpayments');
		}
	}
	
	add_action( 'wp_ajax_gdlr_lms_cloud_payment', 'gdlr_lms_cloud_payment' );
	add_action( 'wp_ajax_nopriv_gdlr_lms_cloud_payment', 'gdlr_lms_cloud_payment' );
	function gdlr_lms_cloud_payment(){	
		global $gdlr_lms_option;
	
		$ret = array();
		$return_val = $_POST['return_val'];
		
		if( !empty($return_val['invoiceId']) && $gdlr_lms_option['cloud-public-id'] == $return_val['publicId'] ){
			global $wpdb;
			$temp_sql  = "SELECT * FROM " . $wpdb->prefix . "gdlrpayment ";
			$temp_sql .= "WHERE id = " . esc_sql($return_val['invoiceId']);	
			$result = $wpdb->get_row($temp_sql);

			$payment_info = unserialize($result->payment_info);

			$wpdb->update( $wpdb->prefix . 'gdlrpayment', 
				array('payment_status'=>'paid', 'attachment'=>serialize($return_val), 'payment_date'=>date('Y-m-d H:i:s')), 
				array('id'=>$return_val['invoiceId']), 
				array('%s', '%s', '%s'), 
				array('%d')
			);	
			
			gdlr_lms_mail($payment_info['email'], 
				__('Cloud Payment Received', 'gdlr-lms'), 
				__('Your verification code is', 'gdlr-lms') . ' ' . $payment_info['code']);				
			
			$ret['status'] = 'success';
			$ret['redirect'] = get_permalink($result->course_id);
		}else{
			$ret['status'] = 'failed';
			$ret['message'] = __('A problem occurs', 'gdlr-lms');	
			$ret['message_sub'] = __('Please refresh the page to try again', 'gdlr-lms');
		}
		
		die(json_encode($ret));
	}	

?>