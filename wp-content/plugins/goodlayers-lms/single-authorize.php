<?php 
	if( !empty($_GET['response']) && $_GET['response'] == 1 ){
		include_once('../../../wp-load.php');
		include_once('include/payment-api/authorize-php/autoload.php');
		
		global $gdlr_lms_option, $wpdb;
		
		$api_login_id = $gdlr_lms_option['authorize-api-id'];
		$md5_setting = $gdlr_lms_option['authorize-md5-hash']; 
		
		$response = new AuthorizeNetSIM($api_login_id, $md5_setting);
		if($response->isAuthorizeNet()){
		
			if($response->approved){
				$wpdb->update( $wpdb->prefix . 'gdlrpayment', 
					array('payment_status'=>'paid', 'attachment'=>serialize($response), 'payment_date'=>date('Y-m-d H:i:s')), 
					array('id'=>$_GET['invoice']), 
					array('%s', '%s', '%s'), 
					array('%d')
				);	
				
				$temp_sql  = "SELECT * FROM " . $wpdb->prefix . "gdlrpayment ";
				$temp_sql .= "WHERE id = " . $_GET['invoice'];	
				$result = $wpdb->get_row($temp_sql);			

				$redirect_url = get_permalink($result->course_id);
			}else{
				$redirect_url = add_query_arg(array('payment-method'=> 'authorize', 'response'=>2,
					'response_code'=>$response->response_code, 'response_reason_text'=>$response->response_reason_text), home_url());
			}
			
			// Send the Javascript back to AuthorizeNet, which will redirect user back to your site.
			echo AuthorizeNetDPM::getRelayResponseSnippet($redirect_url);
		}else{ 
			die("Error. Check your MD5 Setting.");
			$redirect_url = add_query_arg(array('payment-method'=> 'authorize', 'response'=>2,
					'response_code'=>$response->response_code, 'response_reason_text'=>$response->response_reason_text), home_url());
?>
<html>
	<head>
		<script type='text/javascript'charset='utf-8'>window.location='<?php echo $redirect_url; ?>';</script>
		<noscript><meta http-equiv='refresh' content='1;url=<?php echo $redirect_url; ?>'></noscript>
	</head>
	<body></body>
</html>		
<?php		
		}
		
		die("");
	}
	get_header();  
?>
<div id="primary" class="content-area gdlr-lms-primary-wrapper">
<div id="content" class="site-content" role="main">
<?php
	if( function_exists('gdlr_lms_get_header') && !empty($gdlr_lms_option['show-header']) && $gdlr_lms_option['show-header'] == 'enable' ){
		gdlr_lms_get_header();
	}
?>
	<div class="gdlr-lms-content">
		<div class="gdlr-lms-container gdlr-lms-container">
			<div class="gdlr-lms-item gdlr-lms-authorize-payment">	
			<?php
				if( !empty($_GET['response']) && $_GET['response'] == 2 ){
					echo '<div class="gdlr-lms-error" style="margin-bottom: 0px;">';
					echo $_GET['response_code'] . ' : ' . $_GET['response_reason_text'];
					echo '</div>';
				}else if( !empty($_GET['invoice']) ){
					global $gdlr_lms_option, $wpdb;

					$relay_response_url = plugin_dir_url( __FILE__ ) . 'single-authorize.php?response=1&invoice=' . $_GET['invoice']; 
					
					$api_login_id = $gdlr_lms_option['authorize-api-id'];
					$transaction_key = $gdlr_lms_option['authorize-transaction-key'];
				
					$temp_sql  = "SELECT * FROM " . $wpdb->prefix . "gdlrpayment ";
					$temp_sql .= "WHERE id = " . $_GET['invoice'];	
					$result = $wpdb->get_row($temp_sql);
					
					$fp_sequence = $_GET['invoice']; 
					$amount = $result->price;
					
					$test_mode = (empty($gdlr_lms_option['authorize-live-mode']) || $gdlr_lms_option['authorize-live-mode'] == 'disable')? true: false; 
		
					echo AuthorizeNetDPM::getCreditCardForm($amount, $fp_sequence, $relay_response_url, $api_login_id, $transaction_key,
						$test_mode, $test_mode);
				}
			?>
			</div>
		</div>
	</div>
</div>
</div>
<?php	
	if( !empty($gdlr_lms_option['show-sidebar']) && $gdlr_lms_option['show-sidebar'] == 'enable' ){ 
		get_sidebar( 'content' );
		get_sidebar();
	}

	get_footer();
?>