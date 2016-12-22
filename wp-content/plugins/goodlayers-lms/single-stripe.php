<?php get_header(); ?>
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
				<form action="" method="POST" id="payment-form" class="gdlr-lms-form" data-ajax="<?php echo admin_url('admin-ajax.php'); ?>" data-invoice="<?php echo empty($_GET['invoice'])? 0: $_GET['invoice']; ?>" >
					<div class="gdlr-lms-error payment-errors" style="display: none;"></div>
				
					<p class="gdlr-lms-half-left">
						<label><span><?php _e('Card Holder Name', 'gdlr-lms'); ?></span></label>
						<input type="text" size="20" data-stripe="name"/>
					</p>
					<div class="clear" ></div>
					
					<p class="gdlr-lms-half-left">
						<label><span><?php _e('Card Number', 'gdlr-lms'); ?></span></label>
						<input type="text" size="20" data-stripe="number"/>
					</p>
					<div class="clear" ></div>

					<p class="gdlr-lms-half-left">
						<label><span><?php _e('CVC', 'gdlr-lms'); ?></span></label>
						<input type="text" size="4" data-stripe="cvc"/>
					</p>
					<div class="clear" ></div>

					<p class="gdlr-lms-half-left gdlr-lms-expiration">
						<label><span><?php _e('Expiration (MM/YYYY)', 'gdlr-lms'); ?></span></label>
						<input type="text" size="2" data-stripe="exp-month"/>
						<span class="gdlr-separator" >/</span>
						<input type="text" size="4" data-stripe="exp-year"/>
					</p>
					<div class="clear" ></div>
					<div class="gdlr-lms-loading gdlr-lms-instant-payment-loading"><?php _e('loading', 'gdlr-lms'); ?></div>
					<div class="gdlr-lms-notice gdlr-lms-instant-payment-notice"></div>
					<input type="submit" class="gdlr-lms-button cyan" value="<?php _e('Submit Payment', 'gdlr-lms'); ?>" >
				</form>
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