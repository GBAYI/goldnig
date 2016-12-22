<?php
/*
Template Name: Profile Backend
Author: Somefun Oluwasegun. 2016
*/
?>

<?php
if ( is_user_logged_in() ) {
	wp_safe_redirect( get_author_posts_url(get_current_user_id()) ); exit;
} else {
    wp_safe_redirect( home_url() ); exit;
};
?>



