<?php
/*
 * The template for displaying a header title section
 */
	
function gdlr_lms_get_header(){
	if( !empty($_GET['login']) || !empty($_GET['register']) ){ ?>
	<div class="gdlr-lms-title-wrapper">
		<h1 class="gdlr-lms-title"><?php
			if( !empty($_GET['login']) ){
				_e('Login', 'gdlr-lms');
			}else if( !empty($_GET['register']) ){
				_e('Register', 'gdlr-lms');
			}
		?></h1>
	</div>	
<?php }else if( !empty($_POST['payment-method']) && $_POST['payment-method'] == 'stripe' ){ ?>
	<div class="gdlr-lms-title-wrapper">
		<h1 class="gdlr-lms-title"><?php
			if( $_GET['payment-method'] == 'stripe' ){
				_e('Stripe Payment', 'gdlr_translate');
			}else if( $_GET['payment-method'] == 'paymill' ){
				_e('Paymill Payment', 'gdlr_translate');
			}else if( $_GET['payment-method'] == 'authorize' ){
				_e('Authorize Payment', 'gdlr_translate');
			}else if( $_GET['payment-method'] == 'braintree' ){
				_e('Braintree Payment', 'gdlr_translate');
			}
		?></h1>
	</div>	
<?php }else if( is_single() ){ ?>
	<div class="gdlr-lms-title-wrapper" >
		<h1 class="gdlr-lms-title"><?php echo get_the_title(); ?></h1>
	</div>	
<?php }else if( is_archive() || is_search() ){
	if( is_tax('course_category') ){
		$title = single_cat_title('', false);
	}else if( is_author() ){
		$author_id = get_query_var('author');
		$author = get_user_by('id', $author_id);
	
		$title = get_user_meta($author_id, 'first_name', true) . ' ' . get_user_meta($author_id, 'last_name', true);
		$caption = $author->roles[0];					
	}else{
		$title = get_the_title();
	}
?>
	<div class="gdlr-lms-title-wrapper" >
		<h1 class="gdlr-lms-title"><?php echo $title; ?></h1>
	</div>		
<?php } 
} ?>