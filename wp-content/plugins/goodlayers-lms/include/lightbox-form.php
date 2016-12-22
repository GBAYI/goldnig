<?php
	/*
	*	Goodlayers Lightbox Form File
	*/

	function gdlr_lms_preview_lightbox_form($content = '', $slug = ''){
?>
<div class="gdlr-lms-lightbox-container lecture-preview <?php echo $slug; ?>" data-return="parent">
	<div class="gdlr-lms-lightbox-close"><i class="fa fa-remove icon-remove"></i></div>
	<?php echo $content; ?>
</div>
<?php
	}
	
	function gdlr_lms_sign_in_lightbox_form(){
?>
<div class="gdlr-lms-lightbox-container login-form">
	<div class="gdlr-lms-lightbox-close"><i class="fa fa-remove icon-remove"></i></div>

	<h3 class="gdlr-lms-lightbox-title"><?php _e('Please sign in first', 'gdlr-lms'); ?></h3>
	<form class="gdlr-lms-form gdlr-lms-lightbox-form" id="loginform" method="post" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>">
		<p class="gdlr-lms-half-left">
			<span><?php _e('Username', 'gdlr-lms'); ?></span>
			<input type="text" name="log" />
		</p>
		<p class="gdlr-lms-half-right">
			 <span><?php _e('Password', 'gdlr-lms'); ?></span>
			 <input type="password" name="pwd" />
		</p>
		<div class="clear"></div>
		<p class="gdlr-lms-lost-password" >
			<?php $login_url = empty($_GET['login'])? home_url(): $_GET['login']; ?>
			<a href="<?php echo wp_lostpassword_url($login_url); ?>" ><?php _e('Lost Your Password?','gdlr-lms'); ?></a>
		</p>
		<p>
			<input type="hidden" name="home_url"  value="<?php echo home_url(); ?>" />
			<input type="hidden" name="rememberme"  value="forever" />
			<input type="hidden" name="redirect_to" value="<?php echo esc_url(add_query_arg($_GET)) ?>" />
			<input type="submit" name="wp-submit" class="gdlr-lms-button" value="<?php _e('Sign In!', 'gdlr-lms'); ?>" />
		</p>
	</form>
	<h3 class="gdlr-lms-lightbox-title second-section"><?php _e('Not a member?', 'gdlr-lms'); ?></h3>
	<div class="gdlr-lms-lightbox-description"><?php _e('Please simply create an account before subscribing/booking any of our Talks.', 'gdlr-lms'); ?></div>
	<a class="gdlr-lms-button blue" href="<?php echo esc_url(add_query_arg('register', get_permalink(), home_url())); ?>"><?php _e('Create an account for free!', 'gdlr-lms'); ?></a>
</div>
<?php
	}

	function gdlr_lms_quiz_timeout_form($page = 0){
?>
<div class="gdlr-lms-lightbox-container quiz-timeout-form">
	<h3 class="gdlr-lms-lightbox-title"><?php _e('Time out!', 'gdlr-lms'); ?></h3>
	<div class="gdlr-lms-lightbox-quiz-timeout-content">
		<?php if( !empty($page) ){ ?>
		<div class="quiz-timeout-content"><?php
			_e('This part is timeout! press the button below to skip to next part', 'gdlr-lms');
		?></div>
		<a class="gdlr-lms-button blue submit-quiz-form" href="<?php echo esc_url(add_query_arg(array('course_type'=>'quiz', 'course_page'=> $page))); ?>" ><?php
			_e('Continue the quiz', 'gdlr-lms');
		?></a>
		<?php }else{ ?>
		<div class="quiz-timeout-content"><?php
			_e('This part is timeout! press the button to submit the quiz', 'gdlr-lms');
		?></div>
		<a class="gdlr-lms-button blue submit-quiz-timeout-form" ><?php
			_e('Submit the quiz', 'gdlr-lms');
		?></a>
		<?php } ?>
	</div>

</div>
<?php
	}

	function gdlr_lms_finish_quiz_form($redirect_url = ''){
?>
<div class="gdlr-lms-lightbox-container finish-quiz-form">
	<h3 class="gdlr-lms-lightbox-title"><?php _e('Quiz Complete!', 'gdlr-lms'); ?></h3>
	<div class="gdlr-lms-lightbox-finish-quiz-content">
		<div class="finish-quiz-content"><?php
			_e('You can check score in your Profile Dashboard', 'gdlr-lms');
		?></div>
		<a class="gdlr-lms-button cyan" href="<?php echo get_author_posts_url(get_current_user_id()); ?>?type=quiz-scores">View Quiz Score</a>
		<a class="gdlr-lms-button cyan" href="<?php echo empty($redirect_url)? get_permalink(): $redirect_url; ?>"><?php
			_e('Back to the talk-stack', 'gdlr-lms');
		?></a>
	</div>
</div>
<?php
	}

	function gdlr_lms_rating_form($course_id){
?>
<div class="gdlr-lms-lightbox-container rating-form">
	<div class="gdlr-lms-lightbox-close"><i class="fa fa-remove icon-remove"></i></div>

	<h3 class="gdlr-lms-lightbox-title"><?php echo __('Rate the Talk', 'gdlr-lms'); ?></h3>
	<div class="gdlr-lms-lightbox-sub-title"><?php echo get_the_title($course_id); ?></div>

	<form class="gdlr-lms-form gdlr-lms-lightbox-form" method="post" action="<?php echo esc_url(add_query_arg('type', 'attended-courses')); ?>">
		<div class="gdlr-rating-input">
			<span class="gdlr-rating-separator" data-value="0"></span>
			<i class="fa fa-star-o icon-star-empty" data-value="0.5"></i>
			<span class="gdlr-rating-separator" data-value="1"></span>
			<i class="fa fa-star-o icon-star-empty" data-value="1.5"></i>
			<span class="gdlr-rating-separator" data-value="2"></span>
			<i class="fa fa-star-o icon-star-empty" data-value="2.5"></i>
			<span class="gdlr-rating-separator" data-value="3"></span>
			<i class="fa fa-star-o icon-star-empty" data-value="3.5"></i>
			<span class="gdlr-rating-separator" data-value="4"></span>
			<i class="fa fa-star-o icon-star-empty" data-value="4.5"></i>
			<span class="gdlr-rating-separator" data-value="5"></span>
		</div>
		<input type="hidden" class="rating-input" name="rating" />
		<input type="hidden" name="course_id" value="<?php echo $course_id; ?>" />
		<input type="submit" class="gdlr-lms-button cyan" value="<?php echo esc_attr(__('Rate !', 'gdlr-lms')); ?>" />
	</form>

</div>
<?php
	}

	function gdlr_lms_payment_option_form(){
?>
<div class="gdlr-lms-lightbox-container payment-option-form">
	<div class="gdlr-lms-lightbox-close"><i class="fa fa-remove icon-remove"></i></div>

	<div class="gdlr-lms-payment-option-wrapper gdlr-lms-left">
		<div class="gdlr-lms-payment-option-inner">
			<h4 class="gdlr-lms-payment-option-head"><?php _e('Pay now.', 'gdlr-lms'); ?></h4>
			<a class="gdlr-lms-button cyan" data-rel="gdlr-lms-lightbox3" data-lb-open="buy-form" ><?php _e('Pay Now', 'gdlr-lms'); ?></a>
			<div class="gdlr-lms-payment-option-description"><?php
				_e('* You\'re not required to submit evidence of payment after you pay via PayPal.','gdlr-lms');
			?></div>
		</div>
	</div>
	<div class="gdlr-lms-payment-option-or"><?php _e('OR', 'gdlr-lms'); ?></div>
	<div class="gdlr-lms-payment-option-wrapper gdlr-lms-right">
		<div class="gdlr-lms-payment-option-inner">
			<h4 class="gdlr-lms-payment-option-head"><?php _e('Submit evidence of payment.', 'gdlr-lms'); ?></h4>
			<a class="gdlr-lms-button blue" data-rel="gdlr-lms-lightbox3" data-lb-open="evidence-form" ><?php _e('Continue', 'gdlr-lms'); ?></a>
			<div class="gdlr-lms-payment-option-description"><?php
				_e('* Noted that you must pay via method we provided before sumitting evidence.','gdlr-lms');
			?></div>
		</div>
	</div>
</div>
<?php
	}

	function gdlr_lms_evidence_lightbox_form($fix_val = array(), $close = 'close'){
?>
<div class="gdlr-lms-lightbox-container evidence-form">
	<?php
		if($close == 'close'){
			echo '<div class="gdlr-lms-lightbox-close"><i class="fa fa-remove icon-remove"></i></div>';
		}else if($close != 'none'){
			echo '<div class="gdlr-lms-lightbox-back gdlr-lms-button cyan" data-rel="gdlr-lms-lightbox3" data-lb-open="' . $close . '"><i class="fa fa-arrow-left icon-arrow-left"></i></div>';
		}
	?>
	<h3 class="gdlr-lms-lightbox-title"><?php echo $fix_val['title']; ?></h3>
	<form class="gdlr-lms-form gdlr-lms-lightbox-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url(add_query_arg($_GET)); ?>">
		<p>
			<span><?php _e('Additional Note', 'gdlr-lms'); ?></span>
			<textarea class="full-note" name="additional-note" ><?php echo $fix_val['additional_note'] ?></textarea>
		</p>
		<p>
			<span><?php _e('Select Attachment', 'gdlr-lms'); ?></span>
			<input type="file" name="attachment" />
		</p>
		<p>
			<span><?php _e('Total Price', 'gdlr-lms'); ?></span>
			<input type="text" value="<?php echo gdlr_lms_money_format($fix_val['price']); ?>" disabled />
		</p>
		<p>
			<input type="hidden" name="action" value="submit-evidence" />
			<input type="hidden" name="invoice" value="<?php echo $fix_val['id']; ?>">
			<input type="submit" class="gdlr-lms-button" value="<?php _e('Submit', 'gdlr-lms'); ?>" />
		</p>
	</form>
</div>
<?php
	}

	function gdlr_lms_purchase_lightbox_form($course_option, $type, $fix_val = array(), $close = 'close'){
		global $current_user, $lms_paypal, $lms_money_format, $gdlr_lms_option;
		if( !empty($fix_val) ){
			$disabled = 'disabled';
			$fix_val['amount'] = intval($fix_val['amount']);
			$fix_val['form-class'] = 'gdlr-check-form';
			$fix_val['return'] = get_permalink($fix_val['course-id']);
			$fix_val['coupon'] = empty($fix_val['coupon'])? '': $fix_val['coupon'];
			$fix_val['coupon-discount'] = empty($fix_val['coupon-discount'])? 0: $fix_val['coupon-discount'];
		}else{
			$user_info = get_userdata($current_user->data->ID);
			$user_meta = get_user_meta($current_user->data->ID);
			$disabled = '';
			$fix_val = array(
				'id' => '',
				'title' => get_the_title(),
				'first_name' => $user_meta['first_name'][0],
				'last_name' => $user_meta['last_name'][0],
				'email' => $user_info->data->user_email,
				'phone' => empty($user_meta['phone'])? '': $user_meta['phone'][0],
				'address' => empty($user_meta['address'])? '': $user_meta['address'][0],
				'additional_note' => '',
				'amount' => 1,
				'form-class' => '',
				'coupon' => '',
				'coupon-discount' => 0,
				'course-id'=> get_the_ID(),
				'return'=> get_permalink()
			);
		}

?>
<div class="gdlr-lms-lightbox-container <?php echo $type; ?>-form">
	<?php
		if($close == 'close'){
			echo '<div class="gdlr-lms-lightbox-close"><i class="fa fa-remove icon-remove"></i></div>';
		}else if($close != 'none'){
			echo '<div class="gdlr-lms-lightbox-back gdlr-lms-button cyan" data-rel="gdlr-lms-lightbox3" data-lb-open="' . $close . '"><i class="fa fa-arrow-left icon-arrow-left"></i></div>';
		}
	?>

	<h3 class="gdlr-lms-lightbox-title"><?php echo $fix_val['title']; ?></h3>
	<form class="gdlr-lms-form gdlr-lms-lightbox-form <?php echo $fix_val['form-class']; ?>" method="post" <?php
		if( $type == 'buy' ) echo 'action="' . $lms_paypal['url'] . '"'
	?> data-ajax="<?php echo admin_url('admin-ajax.php'); ?>?lang=<?php echo substr(get_locale(), 0, 2); ?>">
		<p class="gdlr-lms-half-left">
			<span><?php _e('Name', 'gdlr-lms'); ?></span>
			<input type="text" name="first_name" value="<?php echo $fix_val['first_name']; ?>" <?php echo $disabled; ?> />
		</p>
		<p class="gdlr-lms-half-right">
			 <span><?php _e('Lastname', 'gdlr-lms'); ?></span>
			 <input type="text" name="last_name" value="<?php echo $fix_val['last_name']; ?>" <?php echo $disabled; ?> />
		</p>
		<div class="clear"></div>
		<p class="gdlr-lms-half-left">
			<span><?php _e('Email', 'gdlr-lms'); ?></span>
			<input type="text" name="email" value="<?php echo $fix_val['email']; ?>" <?php echo $disabled; ?> />
		</p>
		<p class="gdlr-lms-half-right">
			 <span><?php _e('Phone', 'gdlr-lms'); ?></span>
			 <input type="text" name="phone" value="<?php echo $fix_val['phone']; ?>" <?php echo $disabled; ?> />
		</p>
		<!-- <div class="clear"></div>
		<p class="gdlr-lms-half-left">
			<span><?php _e('Address', 'gdlr-lms'); ?></span>
			<textarea name="address" <?php echo $disabled; ?>><?php echo $fix_val['address']; ?></textarea>
		</p>
		<p class="gdlr-lms-half-right">
			<span><?php _e('Additional Note', 'gdlr-lms'); ?></span>
			<textarea name="additional-note" <?php echo $disabled; ?>><?php echo $fix_val['additional_note'] ?></textarea>
		</p> -->
		<div class="clear"></div>
		<p class="gdlr-lms-half-left">
			<span><?php _e('Amount', 'gdlr-lms'); ?></span>
			<?php $amount_disabled = ($disabled == 'disabled' || $course_option['online-course'] == 'enable')? 'disabled': ''; ?>
			<input type="text" name="quantity" value="<?php echo $fix_val['amount']; ?>" />
		</p>
		<?php
			$price = empty($course_option['discount-price'])? $course_option['price']: $course_option['discount-price'];
			$price = floatval($price);
		?>
		<p class="gdlr-lms-half-right">
			 <span><?php _e('Total Price', 'gdlr-lms'); ?></span>
			 <input type="text" class="price-display" value="<?php echo gdlr_lms_money_format(($price * $fix_val['amount']) - $fix_val['coupon-discount']); ?>" disabled />
			 <input type="hidden" class="price" name="price" value="<?php echo (($price * $fix_val['amount']) - $fix_val['coupon-discount']); ?>" />
			 <input type="hidden" class="price-one" name="amount" value="<?php echo $price; ?>" />
			 <input type="hidden" class="format" value="<?php echo $lms_money_format; ?>" />
		</p>
		<div class="clear"></div>
		<div class="gdlr-lms-coupon-full-width">
			<span class="gdlr-lms-coupon-head"><?php _e('Coupon Code', 'gdlr-lms'); ?>
				<img class="gdlr-lms-coupon-loading" src="<?php echo plugins_url('../images/loading.gif', __FILE__); ?>" />
				<img class="gdlr-lms-coupon-correct" src="<?php echo plugins_url('../images/correct.png', __FILE__); ?>" />
				<img class="gdlr-lms-coupon-wrong" src="<?php echo plugins_url('../images/wrong.png', __FILE__); ?>" />
			</span>
			<div class="gdlr-lms-coupon-status" ></div>
			<input type="hidden" class="discount-amount" name="discount_amount" value="<?php echo $fix_val['coupon-discount']; ?>" />
			<input type="hidden" class="coupon-amount" name="coupon-amount" value="" />
			<input type="hidden" class="coupon-type" name="coupon-type" value="" />
			<input type="text" class="gdlr-lms-coupon-code" name="gdlr-lms-coupon-code" value="<?php echo $fix_val['coupon']; ?>" />
		</div>
		<?php 
			if( $type == "buy" ){ 
				if( empty($gdlr_lms_option['instant-payment-method']) ){
					$gdlr_lms_option['instant-payment-method'] = array('paypal', 'stripe', 'paymill', 'authorize');
				}
				
				if( sizeof($gdlr_lms_option['instant-payment-method']) > 1 ){
					echo '<div class="gdlr-payment-method" >';
					foreach( $gdlr_lms_option['instant-payment-method'] as $key => $payment_method ){
						echo '<label ' . (($key == 0)? 'class="gdlr-active"':'') . ' >';
						echo '<input type="radio" name="payment-method" value="' . $payment_method . '" ' . (($key == 0)? 'checked':'') . ' />';
						echo '<img src="' . plugins_url('../images/' . $payment_method . '.png', __FILE__) . '" alt="" />';
						echo '</label>';
					}
					echo '<div class="clear"></div>';
					echo '</div>';
				}else{
					echo '<input type="hidden" name="payment-method" value="' . $gdlr_lms_option['instant-payment-method'][0] . '" />';
					
				}
			}
		?>
		<p>
			<div class="gdlr-lms-notice"><?php _e('notice', 'gdlr-lms'); ?></div>
			<div class="gdlr-lms-loading"><?php _e('loading', 'gdlr-lms'); ?></div>
			<input type="hidden" name="rememberme"  value="forever" />
			<input type="hidden" name="course_id"  value="<?php echo $fix_val['course-id']; ?>" />
			<input type="hidden" name="course_code"  value="<?php echo $course_option['course-code']; ?>" />
			<input type="hidden" name="student_id"  value="<?php echo $current_user->data->ID; ?>" />
			<input type="hidden" name="action" value="<?php 
				echo empty($fix_val['form-class'])? 'gdlr_lms_form_purchase' : 'gdlr_lms_form_price_validate';
			?>" />
			<input type="hidden" name="action_type" value="<?php echo $type; ?>" />
			<input type="hidden" name="charset" value="utf-8"> 
			<input type="hidden" name="return" value="<?php echo $fix_val['return']; ?>">
			<?php if($type == "buy"){ ?>
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="invoice" value="<?php echo $fix_val['id']; ?>">
				<input type="hidden" name="business" value="<?php echo $lms_paypal['recipient']; ?>">
				<input type="hidden" name="item_name" value="<?php echo esc_attr($fix_val['title']); ?>" />
				<input type="hidden" name="currency_code" value="<?php echo $lms_paypal['currency_code']; ?>" />
				<input type="hidden" name="notify_url" value="<?php echo esc_url(add_query_arg(array('paypal'=>''), home_url('/'))); ?>">
			<?php } ?>
			<?php wp_nonce_field( 'gdlr_lms_purchase_form', 'gdlr_lms_purchase_form' ); ?>
			<input type="submit" class="gdlr-lms-button" value="<?php
				echo ($type == 'book')? __('Book Now!', 'gdlr-lms'): __('Pay Now!', 'gdlr-lms');
			?>" />
		</p>
	</form>
</div>
<?php
	}
	
	// action to validate coupon
	add_action( 'wp_ajax_lms_check_coupon_code', 'gdlr_lms_check_coupon_code' );
	add_action( 'wp_ajax_nopriv_lms_check_coupon_code', 'gdlr_lms_check_coupon_code' );	
	function gdlr_lms_coupon_discount($coupon_id, $course_id){
		global $wpdb;
		
		$ret = array();
		
		if(empty($coupon_id) || empty($course_id)){
			$ret['status'] = 'failed';
			$ret['message'] = __('An error occurs, please try again after refreshing the page.');
		}else{
			$sql  = "SELECT DISTINCT post_id FROM {$wpdb->postmeta} wpostmeta ";
			$sql .= "WHERE meta_key = 'gdlr-coupon-code' AND meta_value = '{$coupon_id}' ";
			$sql .= "ORDER BY post_id DESC";
			$query =  $wpdb->get_row($sql, OBJECT);

			if( empty($query) ){
				$ret['status'] = 'failed';
			}else{
				$post_option = gdlr_preventslashes(gdlr_stripslashes(get_post_meta($query->post_id, 'gdlr-lms-coupon-settings', true)));
				$post_option = json_decode(gdlr_decode_preventslashes($post_option), true);

				if( !empty($post_option) ){
					if( strcmp($post_option['coupon-expiry'], date('Y-m-d')) > 0 ){

						$count = get_post_meta($query->post_id, 'gdlr-coupon-amount', true);
						$coupon_amount = intval($post_option['coupon-amount']);
						if( $coupon_amount == -1 || $coupon_amount > $count ){
							if( empty($post_option['specify-course']) ){
								$ret['status'] = 'success';
							}else{
								$specify_course = array_map('trim', explode(',', $post_option['specify-course']));
								if( in_array($course_id, $specify_course) ){
									$ret['status'] = 'success';
								}else{
									$ret['status'] = 'failed';
									$ret['message'] = __('This coupon isn\'t valid for this Talk', 'gdlr-lms');
								}
							}
						}else{
							$ret['status'] = 'failed';
							$ret['message'] = __('This coupon code has been used up', 'gdlr-lms');
						}
					}else{
						$ret['status'] = 'failed';
						$ret['message'] = __('Coupon has been expired', 'gdlr-lms');
					}
					if( $ret['status'] == 'success' ){
						$ret['coupon-id'] = $query->post_id;
						$ret['amount'] = $post_option['coupon-discount-amount'];
						$ret['type'] = $post_option['coupon-discount-type'];
						
						$discount_text = '';
						if( $ret['type'] == 'percent' ){
							$discount_text =  $post_option['coupon-discount-amount'] . '%';
							
						}else{
							$discount_text = gdlr_lms_money_format($post_option['coupon-discount-amount']);
						}
						$ret['message'] = sprintf(__('You got %s discount', 'gdlr-lms'), $discount_text);							
					}
				}else{
					$ret['status'] = 'failed';
				}
			}
			
			return $ret;
		}		
	}
	function gdlr_lms_check_coupon_code(){
		$ret = gdlr_lms_coupon_discount($_POST['id'], $_POST['course_id']);
		die(json_encode($ret));
	}
	
	// action to validate price for booked form
	add_action( 'wp_ajax_gdlr_lms_form_price_validate', 'gdlr_lms_form_price_validate' );
	add_action( 'wp_ajax_nopriv_gdlr_lms_form_price_validate', 'gdlr_lms_form_price_validate' );
	function gdlr_lms_form_price_validate(){
		global $gdlr_lms_option;
		$ret = array();
		
		$course_options = gdlr_lms_get_course_options($_POST['course_id']);
		$course_price = $course_options['price-one'] * intval($_POST['quantity']);
		
		$coupon_code = empty($_POST['gdlr-lms-coupon-code'])?'': $_POST['gdlr-lms-coupon-code'];
		$coupon_val = gdlr_lms_coupon_discount($coupon_code, $_POST['course_id']);
		$coupon_discount = 0;
		if( !empty($coupon_val['type']) && $coupon_val['type'] == 'amount' ){
			$coupon_discount = intval($coupon_val['amount']);
		}else if( !empty($coupon_val['type']) && $coupon_val['type'] == 'percent' ){
			$coupon_discount = intval( $course_price * intval($coupon_val['amount']) / 100);
		}
		if( $course_price > $coupon_discount ){
			$course_price -= $coupon_discount;
		}else{
			$course_price = 0;
		}

		if( abs($course_options['price-one'] - floatval($_POST['amount'])) > 0.00001 || $_POST['discount_amount'] != $coupon_discount ){
			$ret['status'] = 'failed';
			$ret['message'] = __('An error is occurred, please refresh the page to try this again.', 'gdlr-lms');
		}else{
			$ret['id'] = $_POST['invoice'];
			$ret['status'] = 'success';
			if( empty($_POST['payment-method']) || $_POST['payment-method'] == 'paypal' ){
				$ret['message'] = __('Redirecting to paypal', 'gdlr-lms');
				$ret['redirect'] = true;
			}else if( $_POST['payment-method'] == 'stripe' ){
				$ret['message'] = __('Redirecting to stripe payment', 'gdlr-lms');
				$ret['redirect'] = add_query_arg(array('payment-method'=> 'stripe', 'invoice'=>$ret['id']), home_url());
			}else if( $_POST['payment-method'] == 'paymill' ){
				$ret['message'] = __('Redirecting to paymill payment', 'gdlr-lms');
				$ret['redirect'] = add_query_arg(array('payment-method'=> 'paymill', 'invoice'=>$ret['id']), home_url());
			}else if( $_POST['payment-method'] == 'authorize' ){
				$ret['message'] = __('Redirecting to authorize payment', 'gdlr-lms');
				$ret['redirect'] = add_query_arg(array('payment-method'=> 'authorize', 'invoice'=>$ret['id']), home_url());
			}else if( $_POST['payment-method'] == 'braintree' ){
				$ret['message'] = __('Booking complete, redirecting to braintree payment', 'gdlr-lms');
				$ret['redirect'] = add_query_arg(array('payment-method'=> 'braintree', 'invoice'=>$ret['id']), home_url());
			}else if( $_POST['payment-method'] == 'cloud' ){
				$ret['message'] = __('Booking complete, proceed to cloud payment', 'gdlr-lms');
				$ret['payment'] = 'cloud';
				$ret['ok_button'] = __('Ok', 'gdlr-lms');
				$ret['payment_failed_text'] = __('Payment Failed', 'gdlr-lms');
				$ret['data'] = array(
					'publicId' => $gdlr_lms_option['cloud-public-id'],
					'description' => '"' . get_the_title($_POST['course_id']) . '" ' . __('couse purchasing', 'gdlr-lms'),
					'amount' => floatval($course_price),
					'currency' => $gdlr_lms_option['cloud-currency-code'],
					'invoiceId' => $ret['id']
				);
			}
		}
		
		die(json_encode($ret));
	}

	// action when book form is submitted
	add_action( 'wp_ajax_gdlr_lms_form_purchase', 'gdlr_lms_form_purchase' );
	add_action( 'wp_ajax_nopriv_gdlr_lms_form_purchase', 'gdlr_lms_form_purchase' );
	function gdlr_lms_form_purchase(){
		$ret = array();

		if(wp_verify_nonce($_POST['gdlr_lms_purchase_form'], 'gdlr_lms_purchase_form')){

			global $wpdb, $gdlr_lms_option;

			if( empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) ){
				$ret['status'] = 'failed';
				$ret['message'] = __('Please fill all required fields.', 'gdlr-lms');

			}else if( gdlr_lms_payment_row_exists($_POST['course_id'], $_POST['student_id']) ){
				$ret['status'] = 'failed';
				$ret['message'] = __('You already booked this Talk, please proceed the payment via your profile page.', 'gdlr-lms');

			}else{
				$_POST['quantity'] = empty($_POST['quantity'])? 1: $_POST['quantity'];
			
				$course_options = gdlr_lms_get_course_options($_POST['course_id']);
				$course_price = $course_options['price-one'] * intval($_POST['quantity']);
				
				$coupon_code = empty($_POST['gdlr-lms-coupon-code'])?'': $_POST['gdlr-lms-coupon-code'];
				$coupon_val = gdlr_lms_coupon_discount($coupon_code, $_POST['course_id']);
				$coupon_discount = 0;
				if( !empty($coupon_val['type']) && $coupon_val['type'] == 'amount' ){
					$coupon_discount = intval($coupon_val['amount']);
				}else if( !empty($coupon_val['type']) && $coupon_val['type'] == 'percent' ){
					$coupon_discount = intval( $course_price * intval($coupon_val['amount']) / 100);
				}
				if( $course_price > $coupon_discount ){
					$course_price -= $coupon_discount;
				}else{
					$course_price = 0;
				}

				if( (abs($course_options['price-one'] - floatval($_POST['amount'])) > 0.00001) || 
					(abs($course_price - floatval($_POST['price'])) > 0.00001) ){
						
					$ret['status'] = 'failed';
					$ret['message'] = __('An error is occurred, please refresh the page to try this again.', 'gdlr-lms');
				}else{
					
					if( !empty($course_options['max-seat']) && $course_options['online-course'] == 'disable' &&
						intval($course_options['booked-seat']) + intval($_POST['quantity']) > intval($course_options['max-seat']) ){
						$ret['status'] = 'failed';
						$ret['message'] = __('Sorry, This Talk is already fully booked. Please try again later.', 'gdlr-lms');
					}else{
						$running_number = intval(get_post_meta($_POST['course_id'], 'student-booking-id', true));
						$running_number = empty($running_number)? 1: $running_number + 1;
						update_post_meta($_POST['course_id'], 'student-booking-id', $running_number);
						
						if( !empty($coupon_val['coupon-id']) ){
							$coupon_count = get_post_meta($coupon_val['coupon-id'], 'gdlr-coupon-amount', true);
							$coupon_count = intval($coupon_count) + 1;
							update_post_meta($coupon_val['coupon-id'], 'gdlr-coupon-amount', $coupon_count);
						}
						
						$code  = mb_substr($_POST['first_name'], 0, 1) . mb_substr($_POST['last_name'], 0, 1);
						$code .= $running_number . $_POST['course_code'] . $_POST['course_id'];		
						
						$data = serialize(array(
							'first_name' => $_POST['first_name'],
							'last_name' => $_POST['last_name'],
							'email' => $_POST['email'],
							'phone' => $_POST['phone'],
							'address' => $_POST['address'],
							'additional_note' => $_POST['additional-note'],
							'amount' => $_POST['quantity'],
							'price' => $_POST['price'],
							'coupon' => $coupon_code,
							'coupon-discount' => $coupon_discount,
							'code' => $code
						));
						
						if( $course_price == 0 ){
							$payment_status = 'paid';
						}else{
							$payment_status = 'pending';
						}
						
						// for free onsite course
						if(empty($course_options['price']) && empty($course_options['discount-price'])){
							$payment_status = 'reserved';
						}
						$temp_post = get_post($_POST['course_id']);
						$result = $wpdb->insert( $wpdb->prefix . 'gdlrpayment',
							array('course_id'=>$_POST['course_id'], 'student_id'=>$_POST['student_id'], 'author_id'=>$temp_post->post_author,
								'payment_date'=>date('Y-m-d H:i:s'), 'payment_info'=>$data, 'payment_status'=>$payment_status, 'price'=>$_POST['price'],
								'attendance'=>$course_options['start-date'], 'coupon_discount'=>$coupon_discount),
							array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s')
						);

						if( $result > 0 ){
							$ret['id'] = $wpdb->insert_id;
							$ret['status'] = 'success';
							if( $course_price == 0 ){
								$ret['message'] = __('Purchasing Complete', 'gdlr-lms');
								$ret['redirect'] = $_POST['return'];
							}else{
								if( $_POST['action_type'] == 'book' ){
									$ret['message'] = __('Booking complete', 'gdlr-lms');
								}else{
									if( empty($_POST['payment-method']) || $_POST['payment-method'] == 'paypal' ){
										$ret['message'] = __('Booking complete, redirecting to paypal', 'gdlr-lms');
										$ret['redirect'] = true;
									}else if( $_POST['payment-method'] == 'stripe' ){
										$ret['message'] = __('Booking complete, redirecting to stripe payment', 'gdlr-lms');
										$ret['redirect'] = add_query_arg(array('payment-method'=> 'stripe', 'invoice'=>$ret['id']), home_url());
									}else if( $_POST['payment-method'] == 'paymill' ){
										$ret['message'] = __('Booking complete, redirecting to paymill payment', 'gdlr-lms');
										$ret['redirect'] = add_query_arg(array('payment-method'=> 'paymill', 'invoice'=>$ret['id']), home_url());
									}else if( $_POST['payment-method'] == 'authorize' ){
										$ret['message'] = __('Booking complete, redirecting to authorize payment', 'gdlr-lms');
										$ret['redirect'] = add_query_arg(array('payment-method'=> 'authorize', 'invoice'=>$ret['id']), home_url());
									}else if( $_POST['payment-method'] == 'braintree' ){
										$ret['message'] = __('Booking complete, redirecting to braintree payment', 'gdlr-lms');
										$ret['redirect'] = add_query_arg(array('payment-method'=> 'braintree', 'invoice'=>$ret['id']), home_url());
									}else if( $_POST['payment-method'] == 'cloud' ){
										$ret['message'] = __('Booking complete, proceed to cloud payment', 'gdlr-lms');
										$ret['payment'] = 'cloud';
										$ret['ok_button'] = __('Ok', 'gdlr-lms');
										$ret['payment_failed_text'] = __('Payment Failed', 'gdlr-lms');
 										$ret['data'] = array(
											'publicId' => $gdlr_lms_option['cloud-public-id'],
											'description' => '"' . get_the_title($_POST['course_id']) . '" ' . __('couse purchasing', 'gdlr-lms'),
											'amount' => floatval($_POST['price']),
											'currency' => $gdlr_lms_option['cloud-currency-code'],
											'invoiceId' => $ret['id']
										);
									}
								}
							}

							// increase seat value
							$course_options['booked-seat'] = intval($course_options['booked-seat']) + intval($_POST['quantity']);
							update_post_meta($_POST['course_id'], 'gdlr-lms-course-settings', wp_slash(json_encode($course_options, JSON_UNESCAPED_UNICODE)));
						}else{
							$ret['status'] = 'failed';
							$ret['message'] = __('Transaction error, please contact the administrator', 'gdlr-lms');
						}
					}
				}
			}
		}else{
			$ret['status'] = 'failed';
			$ret['message'] = __('Session expired, please refresh the page and try this again', 'gdlr-lms');
		}

		die(json_encode($ret));
	}

	// action for cancel booking
	add_action( 'wp_ajax_gdlr_lms_delete_student', 'gdlr_lms_delete_student' );
	add_action( 'wp_ajax_nopriv_gdlr_lms_delete_student', 'gdlr_lms_delete_student' );
	function gdlr_lms_delete_student(){	
		global $wpdb;

		$sql  = 'SELECT * FROM ' . $wpdb->prefix . 'gdlrpayment ';
		$sql .= 'WHERE id=' . $_POST['id'];
		$booked_course = $wpdb->get_row($sql);
		if( !empty($booked_course) ){
			$payment_info = unserialize($booked_course->payment_info);

			$course_options = gdlr_lms_get_course_options($booked_course->course_id);
			$course_options['booked-seat'] = intval($course_options['booked-seat']) - intval($payment_info['amount']);
			update_post_meta($booked_course->course_id, 'gdlr-lms-course-settings', wp_slash(json_encode($course_options, JSON_UNESCAPED_UNICODE)));

			$wpdb->delete($wpdb->prefix . 'gdlrpayment', array('id'=>$_POST['id']), array('%d'));
			$wpdb->delete($wpdb->prefix . 'gdlrquiz', array('course_id'=>$booked_course->course_id, 'student_id'=>$booked_course->student_id), array('%d', '%d'));
		}	
	
		die("");
	}
	
	// action for cancel booking
	add_action( 'wp_ajax_gdlr_lms_cancel_booking', 'gdlr_lms_cancel_booking' );
	add_action( 'wp_ajax_nopriv_gdlr_lms_cancel_booking', 'gdlr_lms_cancel_booking' );
	function gdlr_lms_cancel_booking(){
		global $wpdb;

		$sql  = 'SELECT * FROM ' . $wpdb->prefix . 'gdlrpayment ';
		$sql .= 'WHERE id=' . $_POST['id'] . ' AND ';
		$sql .= '(payment_status=\'pending\' OR payment_status=\'submitted\' OR payment_status=\'reserved\')';
		$booked_course = $wpdb->get_row($sql);
		if( !empty($booked_course) ){
			$payment_info = unserialize($booked_course->payment_info);

			$course_options = gdlr_lms_get_course_options($booked_course->course_id);
			$course_options['booked-seat'] = intval($course_options['booked-seat']) - intval($payment_info['amount']);
			update_post_meta($booked_course->course_id, 'gdlr-lms-course-settings', wp_slash(json_encode($course_options, JSON_UNESCAPED_UNICODE)));

			$wpdb->delete( $wpdb->prefix . 'gdlrpayment', array('id'=>$_POST['id']), array('%d'));
		}
		die("");
	}

?>