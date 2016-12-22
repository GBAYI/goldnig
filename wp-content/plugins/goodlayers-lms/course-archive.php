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
			<div class="gdlr-lms-item" >
			<?php
				if( $gdlr_lms_option['archive-course-style'] == 'grid' ){
					gdlr_lms_print_course_grid($wp_query, 
						$gdlr_lms_option['archive-course-thumbnail-size'], 
						$gdlr_lms_option['archive-course-size']);
				}else if( $gdlr_lms_option['archive-course-style'] == 'grid-2' ){
					gdlr_lms_print_course_grid2($wp_query, 
						$gdlr_lms_option['archive-course-thumbnail-size'], 
						$gdlr_lms_option['archive-course-size']);
				}else if( $gdlr_lms_option['archive-course-style'] == 'medium' ){
					gdlr_lms_print_course_medium($wp_query, $gdlr_lms_option['archive-course-thumbnail-size']);
				}else if( $gdlr_lms_option['archive-course-style'] == 'full' ){
					gdlr_lms_print_course_full($wp_query, $gdlr_lms_option['archive-course-thumbnail-size']);
				}
				
				$paged = (get_query_var('paged'))? get_query_var('paged') : 1;
				echo gdlr_lms_get_pagination($wp_query->max_num_pages, $paged);	
			?>
			</div>
		</div><!-- gdlr-lms-container -->
	</div><!-- gdlr-lms-content -->
</div>
</div>
<?php 
if( !empty($gdlr_lms_option['show-sidebar']) && $gdlr_lms_option['show-sidebar'] == 'enable' ){ 
	get_sidebar( 'content' );
	get_sidebar();
} 

get_footer(); ?>