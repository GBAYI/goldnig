<?php
/**
 * Default Events Template
 * This file is the basic wrapper template for all the views if 'Default Events Template' 
 * is selected in Events -> Settings -> Template -> Events Template.
 * 
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/default-template.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

get_header(); ?>
<?php
/*
 * The template for displaying a header title section
 */
	global $theme_option, $gdlr_post_option;
	$header_background = '';
	if( !empty($gdlr_post_option['header-background']) ){
		if( is_numeric($gdlr_post_option['header-background']) ){
			$image_src = wp_get_attachment_image_src($gdlr_post_option['header-background'], 'full');	
			$header_background = ' style="background-image: url(\'' . $image_src[0] . '\');" ';
		}else{
			$header_background = ' style="background-image: url(\'' . $gdlr_post_option['header-background'] . '\');" ';
		}
	}
?>
<?php if(!is_404() && !is_single() && !is_archive()){ ?>
<div class="gdlr-page-title-wrapper" <?php echo $header_background; ?> >
		<div class="gdlr-page-title-overlay"></div>
		<div class="gdlr-page-title-container container" >
			<h1 class="gdlr-page-title"><?php echo __("Events", "gdlr_translate"); ?></h1>
			<?php if( !empty($gdlr_post_option['page-caption']) ){ ?>
			<span class="gdlr-page-caption"><?php echo gdlr_text_filter($gdlr_post_option['page-caption']); ?></span>
			<?php } ?>
		</div>	
</div>	
<?php } //if not 404 ?>
<div id="tribe-events-pg-template">
	<?php tribe_events_before_html(); ?>
	<?php tribe_get_view(); ?>
	<?php tribe_events_after_html(); ?>
</div> <!-- #tribe-events-pg-template -->
<?php get_footer(); ?>