<?php

	add_action( 'wp_enqueue_scripts', 'gdlr_lms_include_stripe_payment_script' );
	function gdlr_lms_include_stripe_payment_script(){
		if( !empty($_GET['payment-method']) && $_GET['payment-method'] == 'stripe' ){
			wp_enqueue_script('stripe', 'https://js.stripe.com/v2/');
		}
	}
	
	add_action( 'wp_head', 'gdlr_lms_stripe_payment_head' );
	function gdlr_lms_stripe_payment_head(){
		global $gdlr_lms_option;
	
		if( !empty($_GET['payment-method']) && $_GET['payment-method'] == 'stripe' ){ ?>
<script type="text/javascript">
Stripe.setPublishableKey('<?php echo $gdlr_lms_option['stripe-publishable-key']; ?>');

jQuery(function($){
	function stripeResponseHandler(status, response) {
		var form = $('#payment-form');

		if (response.error) {
			// Show the errors on the form
			form.find('.payment-errors').text(response.error.message).slideDown();
			form.find('input[type="submit"]').prop('disabled', false);
			form.find('.gdlr-lms-loading').slideUp();
		}else{
			// response contains id and card, which contains additional card details
			$.ajax({
				type: 'POST',
				url: form.attr('data-ajax'),
				data: {'action':'gdlr_lms_stripe_payment','token': response.id, 'invoice': form.attr('data-invoice')},
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
		
		if( $(this).find('[data-stripe="name"]').val() == "" ){
			form.find('.payment-errors').text('<?php _e('Please fill the card holder name', 'gdlr-lms'); ?>').slideDown();
			return false;
		}
		
		// Disable the submit button to prevent repeated clicks
		form.find('input[type="submit"]').prop('disabled', true);
		form.find('.payment-errors, .gdlr-lms-notice').slideUp();
		form.find('.gdlr-lms-loading').slideDown();
		
		Stripe.card.createToken(form, stripeResponseHandler);

		// Prevent the form from submitting with the default action
		return false;
	});
});
</script>
<?php	}

	
	}
	
	add_action( 'wp_ajax_gdlr_lms_stripe_payment', 'gdlr_lms_stripe_payment' );
	add_action( 'wp_ajax_nopriv_gdlr_lms_stripe_payment', 'gdlr_lms_stripe_payment' );
	function gdlr_lms_stripe_payment(){	
		global $gdlr_lms_option;
	
		$ret = array();

		Stripe::setApiKey($gdlr_lms_option['stripe-secret-key']);
		
		if( !empty($_POST['token']) && !empty($_POST['invoice']) ){
			global $wpdb;

			$temp_sql  = "SELECT * FROM " . $wpdb->prefix . "gdlrpayment ";
			$temp_sql .= "WHERE id = " . $_POST['invoice'];	
			$result = $wpdb->get_row($temp_sql);
			$payment_info = unserialize($result->payment_info);
			
			try{
				$charge = Stripe_Charge::create(array(
				  "amount" => (floatval($result->price) * 100),
				  "currency" => $gdlr_lms_option['stripe-currency-code'],
				  "card" => $_POST['token'],
				  "description" => $payment_info['email']
				));
				
				$wpdb->update( $wpdb->prefix . 'gdlrpayment', 
					array('payment_status'=>'paid', 'attachment'=>serialize($charge), 'payment_date'=>date('Y-m-d H:i:s')), 
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
			}catch(Stripe_CardError $e) {
				$ret['status'] = 'failed';
				$ret['message'] = $e->getMessage();
			}
		}else{
			$ret['status'] = 'failed';
			$ret['message'] = __('Failed to retrieve the course, please made the payment from course page again.', 'gdlr-lms');	
		}
		
		die(json_encode($ret));
	}
	
?>