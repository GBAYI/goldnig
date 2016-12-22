<?php get_header(); ?>
<div class="gdlr-content">

	<?php 
		global $gdlr_sidebar, $theme_option;
		if( empty($gdlr_post_option['sidebar']) || $gdlr_post_option['sidebar'] == 'default-sidebar' ){
			$gdlr_sidebar = array(
				'type'=>$theme_option['post-sidebar-template'],
				'left-sidebar'=>$theme_option['post-sidebar-left'], 
				'right-sidebar'=>$theme_option['post-sidebar-right']
			); 
		}else{
			$gdlr_sidebar = array(
				'type'=>$gdlr_post_option['sidebar'],
				'left-sidebar'=>$gdlr_post_option['left-sidebar'], 
				'right-sidebar'=>$gdlr_post_option['right-sidebar']
			); 				
		}
		$gdlr_sidebar = gdlr_get_sidebar_class($gdlr_sidebar);
	?>
	<div class="with-sidebar-wrapper <?php echo 'gdlr-type-' . $gdlr_sidebar['type']; ?>">
		<div class="with-sidebar-container container">
			<div class="with-sidebar-left <?php echo $gdlr_sidebar['outer']; ?> columns">
				<div class="with-sidebar-content <?php echo $gdlr_sidebar['center']; ?> columns">
					<div class="gdlr-item gdlr-blog-full gdlr-item-start-content">
					<?php while ( have_posts() ){ the_post(); ?>
					
						<!-- get the content based on post format -->
						<?php get_template_part('single/content'); ?>
						
						<nav class="gdlr-single-nav">
							<?php previous_post_link('<div class="previous-nav">%link</div>', '<i class="fa fa-long-arrow-left icon-long-arrow-left"></i><span>%title</span>', true); ?>
							<?php next_post_link('<div class="next-nav">%link</div>', '<span>%title</span><i class="fa fa-long-arrow-right icon-long-arrow-right"></i>', true); ?>
							<div class="clear"></div>
						</nav><!-- .nav-single -->
						
						<?php gdlr_get_social_shares(); ?>
						
						<!-- abou author section -->
						<?php if($theme_option['single-post-author'] != "disable"){ ?>
							<!-- <div class="gdlr-post-author">
							
								<h3 class="post-author-title" ><?php echo __('About Post Author', 'gdlr_translate'); ?></h3>
								<div class="gdlr-post-author-inner">
									<div class="post-author-avartar"><?php echo get_avatar(get_the_author_meta('ID'), 90); ?></div>
									<div class="post-author-content">
									<h4 class="post-author"><?php the_author_posts_link(); ?></h4>
									<?php echo get_the_author_meta('description'); ?>
									</div>
									<div class="clear"></div>
								</div>
							</div> -->
						<?php } ?>		

						<?php comments_template( '', true ); ?>		
						
					<?php } ?>
					</div>
				</div>
				<?php get_sidebar('left'); ?>
				<div class="clear"></div>
			</div>
			<?php get_sidebar('right'); ?>
			<div class="clear"></div>
		</div>				
	</div>				

</div><!-- gdlr-content -->
<?php get_footer(); ?>