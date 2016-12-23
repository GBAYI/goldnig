<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('admin_menu', 'mm_help_page');
 
function mm_help_page(){       
    add_submenu_page( 
       	'mm_menu_page', 
       	'Help', 
       	'Help', 
       	'administrator', 
       	'mm_menu_page_handle', 
       	'mm_help_menu_display'
    	);

}

function mm_help_menu_display($object) { ?>

  <div class="wraper">

	<h1>Help Menu</h1>
	<hr>
	<p>To include map on the page or the post simply add this shortcode <code><strong>[mm_map]</strong></code> </p>

  <hr>
  <p>If you want to call map from the template then you need to echo shortcode like this: <code>echo do_shortcode('[mm_map]');</code></p>
  <p>Even better is to check if shortcode exists and if so, then print it out:</p>
  <p><pre><code>if ( shortcode_exists( 'mm_map' ) ) {
      echo do_shortcode('[mm_map]'); 
    }</code></pre></p>
  

  </div>

<?php
}

?>