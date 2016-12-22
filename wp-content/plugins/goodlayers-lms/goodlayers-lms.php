<?php
/**
 * Plugin Name: Goodlayers LMS
 * Plugin URI: http://goodlayers.com/
 * Description: 
 * Version: 1.2.4
 * Author: Goodlayers
 * Author URI: http://goodlayers.com/
 * License: 
 */
	if( !defined('JSON_UNESCAPED_UNICODE') ){
		define(JSON_UNESCAPED_UNICODE, 0);
	}
	
	$gdlr_lms_option = get_option('gdlr_lms_admin_option', array());
	if( empty($gdlr_lms_option) ){
		$gdlr_lms_option = array('date-format'=>'', 'money-format'=>'', 'paypal-recipient'=>'', 
			'paypal-recipient-email'=>'', 'paypal-action-url'=>'', 'paypal-currency-code'=>'');
	}
	
 	$lms_date_format = $gdlr_lms_option['date-format'];
	$lms_money_format = $gdlr_lms_option['money-format'];
	$lms_paypal = array(
		'recipient_name'=> $gdlr_lms_option['paypal-recipient'],
		'recipient'=> $gdlr_lms_option['paypal-recipient-email'],
		'url'=> $gdlr_lms_option['paypal-action-url'],
		'currency_code'=> $gdlr_lms_option['paypal-currency-code']
	);

	if( is_admin() ){
		include_once('framework/plugin-option.php');
		include_once('framework/plugin-option/statement.php');
		include_once('framework/plugin-option/transaction.php');
		include_once('framework/plugin-option/commission.php');
		include_once('framework/plugin-option/payment-evidence.php');
	}
	
	include_once('framework/gdlr-theme-sync.php');
	include_once('framework/meta-template.php');
	include_once('framework/course-option.php');
	include_once('framework/gdlr-course-content-bkup.php');
	include_once('framework/gdlr-coupon-option.php');
	include_once('framework/certificate-option.php');
	include_once('framework/quiz-option.php');
	
	include_once('framework/user.php');
	include_once('framework/table-management.php');
	
	include_once('lms-header.php');
	include_once('include/login-form.php');
	include_once('include/utility.php');
	include_once('include/misc.php');
	include_once('include/shortcode.php');
	include_once('include/lightbox-form.php');
	include_once('include/course-item.php');
	include_once('include/certificate-item.php');
	include_once('include/instructor-item.php');
	include_once('include/gdlr-payment-query.php');
	
	include_once('include/cloud-payment.php');
	
	include_once('include/stripe-payment.php');
	include_once('include/payment-api/stripe-php/lib/Stripe.php');
	
	include_once('include/paymill-payment.php');
	include_once('include/payment-api/paymill-php/autoload.php');

	include_once('include/payment-api/authorize-php/autoload.php');
	
	if( !empty($_GET['payment-method']) && $_GET['payment-method'] == 'braintree' ){
		include_once('include/payment-api/braintree-php/Braintree.php');
	}
	
	include_once('framework/plugin-option/recent-course-widget.php');
	include_once('framework/plugin-option/popular-course-widget.php');
	include_once('framework/plugin-option/course-category-widget.php');
	include_once('framework/plugin-option/upcoming-course-widget.php');

	// include paypal action
	add_action('init', 'gdlr_lms_include_paypal');
	function gdlr_lms_include_paypal(){
		include_once('include/paypal-ipn.php');
	}
	
	// add action for user roles upon activation
	register_activation_hook(__FILE__, 'gdlr_lms_plugin_activation');
	function gdlr_lms_plugin_activation(){
		gdlr_lms_add_user_role();
		gdlr_lms_create_user_table();
		
		$lms_option = get_option('gdlr_lms_admin_option', array());
		if( empty($lms_option) ){
			$option_file = dirname(__FILE__) . '/default-options.txt';
			$options = unserialize(file_get_contents($option_file));
			update_option('gdlr_lms_admin_option', $options);
		}
	}
	
	// include script for front end
	add_action( 'wp_enqueue_scripts', 'gdlr_lms_include_script' );
	function gdlr_lms_include_script(){
		global $wp_styles, $gdlr_lms_option;
		
		
		if( !empty($gdlr_lms_option['new-font-awesome']) && $gdlr_lms_option['new-font-awesome'] == 'enable'){
			wp_enqueue_style('font-awesome', plugins_url('font-awesome-new/css/font-awesome.min.css', __FILE__) );
		}else{
			wp_enqueue_style('font-awesome', plugins_url('font-awesome/css/font-awesome.min.css', __FILE__) );
			wp_enqueue_style('font-awesome-ie7', plugins_url('font-awesome-ie7.min.css', __FILE__) );
			$wp_styles->add_data( 'font-awesome-ie7', 'conditional', 'lt IE 8');
		}
		
		gdlr_lms_include_jquery_ui_style();
		wp_enqueue_style('lms-style', plugins_url('lms-style.css', __FILE__) );
		$multisite = get_current_blog_id();
		if( empty($multisite) || $multisite == 1 ){
			wp_enqueue_style('lms-style-custom', plugins_url('lms-style-custom.css', __FILE__) );
		}else{
			wp_enqueue_style('lms-style-custom', plugins_url('lms-style-custom' . $multisite . '.css', __FILE__) );
		}
			
		wp_enqueue_script('jquery-ui-datepicker');	
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('lms-script', plugins_url('lms-script.js', __FILE__), array(), '1.0.0', true );
	}
	function gdlr_lms_include_jquery_ui_style(){
		global $gdlr_lms_option;
		
		if( empty($gdlr_lms_option['jqueryui-style']) || $gdlr_lms_option['jqueryui-style'] == 'enable' ){
			wp_enqueue_style('gdlr-date-picker', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		}
	}
	add_action( 'admin_enqueue_scripts', 'gdlr_lms_add_user_scripts');
	function gdlr_lms_add_user_scripts( $hook ) {
		if( ($hook == 'profile.php' || $hook == 'user-edit.php') && function_exists('wp_enqueue_media') ){
			wp_enqueue_media();
		}
	}
	
	// action to loaded the plugin translation file
	add_action('plugins_loaded', 'gdlr_lms_textdomain_init');
	if( !function_exists('gdlr_lms_textdomain_init') ){
		function gdlr_lms_textdomain_init() {
			load_plugin_textdomain('gdlr-lms', false, dirname(plugin_basename( __FILE__ ))  . '/languages/'); 
		}
	}	
	
	// export option
	// $default_file = dirname(__FILE__) . '/default-options.txt';
	// $file_stream = @fopen($default_file, 'w');
	// fwrite($file_stream, serialize($gdlr_lms_option));
	// fclose($file_stream);	
	
?>