<?php get_header(); ?>
<div id="primary" class="content-area gdlr-lms-primary-wrapper">
<div id="content" class="site-content" role="main">
	<?php
		if( function_exists('gdlr_lms_get_header') && !empty($gdlr_lms_option['show-header']) && $gdlr_lms_option['show-header'] == 'enable' ){
			gdlr_lms_get_header();
		}
		
		global $wpdb, $gdlr_lms_option;
		$temp_sql  = "SELECT * FROM " . $wpdb->prefix . "gdlrpayment ";
		$temp_sql .= "WHERE id = " . esc_sql($_GET['invoice']);	
		$result = $wpdb->get_row($temp_sql);

		if( empty($gdlr_lms_option['braintree-live-mode']) || $gdlr_lms_option['braintree-live-mode'] == 'disable' ){
			Braintree_Configuration::environment('sandbox');
		}else{
			Braintree_Configuration::environment('production');
		}
		Braintree_Configuration::merchantId($gdlr_lms_option['braintree-merchant-id']);
		Braintree_Configuration::publicKey($gdlr_lms_option['braintree-public-key']);
		Braintree_Configuration::privateKey($gdlr_lms_option['braintree-private-key']);
	?>
	<div class="gdlr-lms-content">
		<div class="gdlr-lms-container gdlr-lms-container">
			<div class="gdlr-lms-item">	
			
				<?php if( empty($_GET['step']) || $_GET['step'] == 1 ){ ?>
					<form method="post" action="<?php echo esc_url(add_query_arg(array('step'=>2))); ?>">
					  <div id="braintree-payment-form"></div>
					  <input type="submit" value="<?php echo sprintf(__('Pay %s', 'gdlr-lms'), gdlr_lms_money_format(number_format_i18n($result->price, 2))); ?>">
					</form>

					<script src="https://js.braintreegateway.com/v2/braintree.js"></script>
					<script>
					var clientToken = "<?php echo ($clientToken = Braintree_ClientToken::generate()); ?>";

					braintree.setup(clientToken, "dropin", {
					  container: "braintree-payment-form"
					});
					</script>
				<?php }else if( empty($_GET['step']) || $_GET['step'] == 2 ){ 
					$nonce = $_POST["payment_method_nonce"];
					$payment = Braintree_Transaction::sale([
					  'amount' => $result->price,
					  'paymentMethodNonce' => $nonce
					]);
					
					if( !$payment->success ){
						echo '<div class="gdlr-lms-error">';
						print_r($payment->message);
						__('Please try again', 'gdlr-lms');
						echo '</div>';
						echo '<a class="gdlr-lms-button blue" href="' . get_permalink($result->course_id) . '" >' . __('Back To Course', 'gdlr-lms') . '</a>';
					}else{
						$payment_info = unserialize($result->payment_info);
						$wpdb->update( $wpdb->prefix . 'gdlrpayment', 
							array('payment_status'=>'paid', 'attachment'=>serialize($payment), 'payment_date'=>date('Y-m-d H:i:s')), 
							array('id'=>$_GET['invoice']), 
							array('%s', '%s', '%s'), 
							array('%d')
						);	
						
						gdlr_lms_mail($payment_info['email'], 
							__('Braintree Payment Received', 'gdlr-lms'), 
							__('Your verification code is', 'gdlr-lms') . ' ' . $payment_info['code']);		
						
						echo '<div class="gdlr-lms-success">';
						echo __('Your payment has been successfully processed. Thank you.', 'gdlr-lms');
						echo '</div>';
						echo '<a class="gdlr-lms-button blue" href="' . get_permalink($result->course_id) . '" >' . __('Back To Course', 'gdlr-lms') . '</a>';
					}
				} ?>
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

get_footer(); ?>			