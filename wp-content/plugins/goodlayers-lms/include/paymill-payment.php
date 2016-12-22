<?php

	add_action( 'wp_enqueue_scripts', 'gdlr_lms_include_paymill_payment_script' );
	function gdlr_lms_include_paymill_payment_script(){
		if( !empty($_GET['payment-method']) && $_GET['payment-method'] == 'paymill' ){
			wp_enqueue_script('paymill', 'https://bridge.paymill.com/');
		}
	}
	
	add_action( 'wp_head', 'gdlr_lms_paymill_payment_head' );
	function gdlr_lms_paymill_payment_head(){
		global $gdlr_lms_option;
	
		if( !empty($_GET['payment-method']) && $_GET['payment-method'] == 'paymill' ){ ?>
<script type="text/javascript">
var PAYMILL_PUBLIC_KEY = '<?php echo $gdlr_lms_option['paymill-public-key']; ?>';

jQuery(function($){
	function PaymillResponseHandler(error, result) {
		var form = $('#payment-form');

		if(error){
			// Show the errors on the form
			form.find('.payment-errors').text(error.apierror).slideDown();
			form.find('input[type="submit"]').prop('disabled', false);
			form.find('.gdlr-lms-loading').slideUp();
		}else{
			// response contains id and card, which contains additional card details
			$.ajax({
				type: 'POST',
				url: form.attr('data-ajax'),
				data: {'action':'gdlr_lms_paymill_payment','token': result.token, 'invoice': form.attr('data-invoice')},
				dataType: 'json',
				error: function(a, b, c){ 
					console.log(a, b, c); 
					form.find('.gdlr-lms-loading').slideUp(); 
				},
				success: function(data){
					console.log(data);
				
					form.find('.gdlr-lms-loading').slideUp();
					form.find('.gdlr-lms-notice').removeClass('success failed')
						.addClass(data.status).html(data.message).slideDown();
					
					if( data.status == 'failed' ){
						form.find('input[type="submit"]').prop('disabled', false);
					}
						
					if( data.redirect ){
						window.location.replace(data.redirect);
					}
				}
			});	
			// and redirect
		}
	}	

	$('#payment-form').submit(function(event){
		var form = $(this);

		// Disable the submit button to prevent repeated clicks
		form.find('input[type="submit"]').prop('disabled', true);
		form.find('.payment-errors, .gdlr-lms-notice').slideUp();
		form.find('.gdlr-lms-loading').slideDown();
		
		paymill.createToken({
			number: $('.card-number').val(), 
			exp_month: $('.card-expiry-month').val(),   
			exp_year: $('.card-expiry-year').val(),     
			cvc: $('.card-cvc').val() //,                  
			//amount_int: $('.card-amount-int').val(),   
			//currency: $('.card-currency').val(),   
		}, PaymillResponseHandler);                 

		// Prevent the form from submitting with the default action
		return false;
	});
});
</script>
<?php	}

	
	}
	
	add_action( 'wp_ajax_gdlr_lms_paymill_payment', 'gdlr_lms_paymill_payment' );
	add_action( 'wp_ajax_nopriv_gdlr_lms_paymill_payment', 'gdlr_lms_paymill_payment' );
	function gdlr_lms_paymill_payment(){	
		global $gdlr_lms_option;
	
		$ret = array();
		
		if( !empty($_POST['token']) && !empty($_POST['invoice']) ){
			global $wpdb;

			$temp_sql  = "SELECT * FROM " . $wpdb->prefix . "gdlrpayment ";
			$temp_sql .= "WHERE id = " . $_POST['invoice'];	
			$result = $wpdb->get_row($temp_sql);
			$payment_info = unserialize($result->payment_info);
			
			$apiKey = $gdlr_lms_option['paymill-private-key'];
			$request = new Paymill\Request($apiKey);
			
			$payment = new Paymill\Models\Request\Payment();
			$payment->setToken($_POST['token']);
			
			try{
				$response  = $request->create($payment);
				$paymentId = $response->getId();
				
				$transaction = new Paymill\Models\Request\Transaction();
				$transaction->setAmount(floatval($result->price) * 100)
							->setCurrency($gdlr_lms_option['paymill-currency-code'])
							->setPayment($paymentId)
							->setDescription($payment_info['email']);

				$response = $request->create($transaction);
				
				$wpdb->update( $wpdb->prefix . 'gdlrpayment', 
					array('payment_status'=>'paid', 'attachment'=>serialize($response), 'payment_date'=>date('Y-m-d H:i:s')), 
					array('id'=>$_POST['invoice']), 
					array('%s', '%s', '%s'), 
					array('%d')
				);	
				
				gdlr_lms_mail($payment_info['email'], 
					__('Stripe Payment Received', 'gdlr-lms'), 
					__('Your verification code is', 'gdlr-lms') . ' ' . $payment_info['code']);				
				
				$ret['status'] = 'success';
				$ret['message'] = __('Payment complete, redirecting to the course page.', 'gdlr-lms');
				$ret['redirect'] = get_permalink($result->course_id);
				$ret['data'] = $result;
			}catch(PaymillException $e) {
				$ret['status'] = 'failed';
				$ret['message'] = $e->getErrorMessage();
			}
		}else{
			$ret['status'] = 'failed';
			$ret['message'] = __('Failed to retrieve the course, please made the payment from course page again.', 'gdlr-lms');	
		}
		
		die(json_encode($ret));
	}
	
?>