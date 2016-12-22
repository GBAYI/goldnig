<?php 

// for reset password section
if( isset($_GET['action']) && $_GET['action'] == 'reset_pass' ){

	list($rp_path) = explode('?', wp_unslash( $_SERVER['REQUEST_URI'] ));
	$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
	if( isset($_GET['key']) ){
		$value = sprintf( '%s:%s', wp_unslash($_GET['login']), wp_unslash($_GET['key']) );
		setcookie($rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true);
		wp_safe_redirect(remove_query_arg(array('key')));
		exit;
	}
	
	if( isset($_COOKIE[$rp_cookie]) && 0 < strpos( $_COOKIE[$rp_cookie], ':' ) ) {
		list($rp_login, $rp_key) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );
		$user = check_password_reset_key( $rp_key, $rp_login );
	}else{
		$user = false;
	}

	if( !$user || is_wp_error($user) ){
		setcookie($rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true);
		$args = array('login'=>home_url(), 'action'=>'lost_password');
		
		if ( $user && $user->get_error_code() === 'expired_key' )
			$args['status'] = 'expiredkey';
		else
			$args['status'] = 'invalidkey';
			
		wp_redirect(esc_url(add_query_arg($args, home_url())));	
		exit;
	}
	
	$rp_errors = new WP_Error();
	if( isset($_POST['pass1']) && $_POST['pass1'] != $_POST['pass2'] ){
		$rp_errors->add('password_reset_mismatch', __('The passwords do not match.', 'gdlr-lms'));
	}
	
	do_action( 'validate_password_reset', $rp_errors, $user );
	
	if( (!$rp_errors->get_error_code()) && isset($_POST['pass1']) && !empty($_POST['pass1']) ){
		reset_password($user, $_POST['pass1']);
		setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		
		wp_redirect(esc_url(add_query_arg(array('login'=>home_url(), 'status'=>'rp_complete'), home_url())));	
		exit();
	}
	
}

get_header(); ?>
<div id="primary" class="content-area gdlr-lms-primary-wrapper">
<div id="content" class="site-content" role="main">
<?php
	if( function_exists('gdlr_lms_get_header') && !empty($gdlr_lms_option['show-header']) && $gdlr_lms_option['show-header'] == 'enable' ){
		gdlr_lms_get_header();
	}
?>
	<div class="gdlr-lms-content">
		<div class="gdlr-lms-container gdlr-lms-container">
			<div class="gdlr-lms-item">	

<!-- lost password form -->			
<?php if( isset($_GET['action']) && $_GET['action'] == 'lost_password' ){ ?>
	<?php
		if( empty($_GET['status']) ){
			echo '<div class="gdlr-lms-success">';
			_e('Please enter your username or email address. You will receive a link to create a new password via email.', 'gdlr-lms');
			echo '</div>';
		}else{
			echo '<div class="gdlr-lms-error">';
			if( $_GET['status'] == 'no_password_reset' ){
				_e('Password reset is not allowed for this user', 'gdlr-lms');
			}else if( $_GET['status'] == 'expiredkey' ){
				_e('Sorry, that key has expired. Please try again.', 'gdlr-lms');
			}else if( $_GET['status'] == 'invalidkey' ){
				_e('Sorry, that key does not appear to be valid.', 'gdlr-lms');
			}else{
				_e('Invalid username or e-mail.', 'gdlr-lms');
			}
			echo '</div>';
		}
	?>
	
	<form name="lostpasswordform" class="gdlr-lms-form" action="<?php echo esc_url( network_site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ); ?>" method="post">
		<p class="gdlr-lms-half-left">
			<label><?php _e('Username or E-mail:') ?></label>
			<input type="text" name="user_login" class="input" value="<?php echo esc_attr($user_login); ?>" size="20" />
		</p>
		<div class="clear"></div>
		<?php do_action( 'lostpassword_form' ); ?>
		<p>
			<input type="hidden" name="login" value="<?php echo $_GET['login']; ?>" />
			<input type="submit" class="gdlr-lms-button" value="<?php _e('Get New Password', 'gdlr-lms'); ?>" />
		</p>
	</form>

<!-- reset password form -->		
<?php }else if( isset($_GET['action']) && $_GET['action'] == 'reset_pass' ){ 
	if( is_wp_error($rp_errors) && $rp_errors->get_error_code() ){
		echo '<div class="gdlr-lms-error">';
		echo $rp_errors->get_error_message();
		echo '</div>';
	}
?>

<form class="gdlr-lms-form" method="post" >
	<p>
		<?php _e('<strong>Hint:</strong> The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; ).', 'gdlr-lms'); ?>
	</p>
	
	<p class="gdlr-lms-half-left">
		<label for="pass1"><?php _e('New password') ?></label>
		<input type="password" name="pass1" value="" autocomplete="off" />
	</p>
	<p class="gdlr-lms-half-right">
		<label for="pass2"><?php _e('Confirm new password') ?></label>
		<input type="password" name="pass2" value="" autocomplete="off" />
	</p>
	<div class="clear" ></div>

	<?php do_action( 'resetpass_form', $user ); ?>
	<p>
		<input type="submit" class="gdlr-lms-button" value="<?php _e('Reset Password', 'gdlr-lms'); ?>" />
	</p>
</form>
	
<!-- login form -->	
<?php }else{ ?>
	<?php if(!empty($_GET['status']) && $_GET['status'] == 'login_incorrect'){ ?>
		<div class="gdlr-lms-error">
		<?php _e('The login credentials is incorrect. Please try again.', 'gdlr-lms'); ?>
		</div>
	<?php }else if(!empty($_GET['status']) && $_GET['status'] == 'forgot_password_confimation'){ ?>
		<div class="gdlr-lms-success">
		<?php _e('Please check your e-mail for the confirmation link.', 'gdlr-lms'); ?>
		</div>
	<?php }else if(!empty($_GET['status']) && $_GET['status'] == 'rp_complete'){ ?>
		<div class="gdlr-lms-success">
		<?php _e('Your password has been reset.', 'gdlr-lms'); ?>
		</div>
	<?php } ?>
	
	<form class="gdlr-lms-form" id="loginform" method="post" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>">
		<p class="gdlr-lms-half-left">
			<label><?php _e('Username', 'gdlr-lms'); ?></label>
			<input type="text" name="log" />
		</p>
		<p class="gdlr-lms-half-right">
			 <label><?php _e('Password', 'gdlr-lms'); ?></label>
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
			<input type="hidden" name="redirect_to" value="<?php echo $_GET['login'] ?>" />
			<input type="submit" name="wp-submit" class="gdlr-lms-button" value="<?php _e('Sign In!', 'gdlr-lms'); ?>" />
		</p>
	</form>
<?php } ?>
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