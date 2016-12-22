<?php
/**
 * The default template for displaying standard post format
 */
	global $gdlr_post_settings; 
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="gdlr-standard-style">
		<?php get_template_part('single/thumbnail', get_post_format()); ?>
		<header class="post-header">
			<h3 class="gdlr-blog-title"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h3>

			<?php echo gdlr_get_blog_info(array('date')); ?>		
			<div class="clear"></div>
		</header><!-- entry-header -->
		<div class="clear"></div>
	</div>
</article><!-- #post -->