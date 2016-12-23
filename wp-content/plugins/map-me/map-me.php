<?php
 /*
 Plugin Name: Map Me
 Plugin URI: 
 Description: Google Maps Plugin. Easy, fast and efficient way to embed google map into your site.
 Author: Marin Matosevic
 Version: 1.1.4
 Author URI: http://marinmatosevic.com
 Text Domain: map-me
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
} 

function icons_path(){
  $folder =  plugin_dir_url( __FILE__ ) . 'assets/icons/';
  return $folder;
} 
function icons_dir_path(){
  $folder =  plugin_dir_path( __FILE__ ) . 'assets/icons/';
  return $folder;
} 
include 'admin/geocode.php'; 
include 'admin/plugin_menu_page.php';
include 'admin/add_locations.php';
include 'admin/help_menu_page.php';


function mm_styles_and_scripts() {   

  wp_enqueue_script('map-styles', plugins_url( '/assets/js/map_styles.js' , __FILE__ ), array('jquery'), '1.0', true);

  $options = get_option('mm_plugin_settings');
  $mm_api_key = isset($options['api_key']) ? $options['api_key'] : null;

  if ( $mm_api_key !== null ) {
    $map_url = '//maps.googleapis.com/maps/api/js?key='.$mm_api_key;    
  } else {
    $map_url = '//maps.googleapis.com/maps/api/js';   
  }

  wp_register_script('maps-api', $map_url, true);

  
  wp_register_script('init-script', plugins_url( '/assets/js/init.js' , __FILE__ ), array('jquery'), '1.0', true);  
}
add_action( 'wp_enqueue_scripts', 'mm_styles_and_scripts' );



function mm_location_styles() {
    global $post_type;
    if( 'mm' == $post_type ){     
      wp_enqueue_style( 'mm_custom_styles', plugins_url( '/assets/css/mm_custom_styles.css', __FILE__ )); 
      wp_enqueue_script( 'mm_custom_script', plugins_url( '/assets/js/mm_custom_script.js', __FILE__ ), array('jquery'), '1.0', true);  
    }
}
add_action( 'admin_print_scripts-post-new.php', 'mm_location_styles', 11 );
add_action( 'admin_print_scripts-post.php', 'mm_location_styles', 11 );



add_filter( 'plugin_action_links', 'mm_add_action_plugin', 10, 5 );
function mm_add_action_plugin( $actions, $plugin_file ){
  static $plugin;

  if (!isset($plugin))
    $plugin = plugin_basename(__FILE__);
  if ($plugin == $plugin_file) {

      $settings = array('settings' => '<a href="admin.php?page=mm_plugin_settings">' . __('Settings', 'General') . '</a>');      
    
        $actions = array_merge($settings, $actions);
      
    }
    
    return $actions;
}


// Map 
function mm_map(){ 

  wp_enqueue_script( 'maps-api' );
  wp_enqueue_script( 'init-script' );

  $map_settings = get_option('mm_plugin_settings');

  if (isset($map_settings['scroll'])){
    $scroll = true;
  } else {
    $scroll = false;
  }

  if (isset($map_settings['controls'])){
    $controls = true;
  } else {
    $controls = false;
  }

  if (!isset($map_settings['map_type'])){
    $map_type = null;
  } else {
    $map_type = $map_settings['map_type'];
  } 

  $map_options = [];   
  $map_options[] = [ 
      $map_settings['zoom'], 
      $scroll, $controls, 
      $map_settings['styles'],
      $map_type
    ];


  $locations_a = [];

  $args = array(
      'post_type'           => 'mm',
      'post_status'         => 'publish',
      'posts_per_page'      => -1
      );
    $loop = new WP_Query( $args );

    while ( $loop->have_posts() ) : $loop->the_post(); 

    $mm_title = get_the_title(); 

    $the_post_id = get_the_ID();     

    $mm_longitude = get_post_meta(get_the_ID(), 'mm_longitude', true);
    $mm_latitude = get_post_meta(get_the_ID(), 'mm_latitude', true);

    $mm_address = get_post_meta(get_the_ID(), "mm_address", true);
    $mm_city = get_post_meta(get_the_ID(), "mm_city", true);
    $mm_zip = get_post_meta(get_the_ID(), "mm_zip", true);
    $mm_country = get_post_meta(get_the_ID(), "mm_country", true);

    $mm_url = get_post_meta(get_the_ID(), "mm_url", true);
    $mm_featured = get_post_meta(get_the_ID(), "mm_featured", true);

    $mm_description = get_post_meta(get_the_ID(), "mm_description", true);

    $mm_icon = get_post_meta(get_the_ID(), "mm_icon", true);

    $mm_featured_animation = get_post_meta(get_the_ID(), "mm_featured_animation", true);


    $mm_info_window = get_post_meta(get_the_ID(), 'mm_info_window', true);

    $mm_show_address = get_post_meta(get_the_ID(), 'mm_show_address', true);
    $mm_show_zip = get_post_meta(get_the_ID(), 'mm_show_zip', true);
    $mm_show_city = get_post_meta(get_the_ID(), 'mm_show_city', true);    
    $mm_show_country = get_post_meta(get_the_ID(), 'mm_show_country', true);
    $mm_show_description = get_post_meta(get_the_ID(), 'mm_show_description', true);
    $mm_show_url = get_post_meta(get_the_ID(), 'mm_show_url', true);

    $mm_info_window_data = "";
    $mm_info_window_data .= '<strong>' . $mm_title . '</strong>' . '</br>';
    if($mm_address !== "" && $mm_show_address == "yes"){
      $mm_info_window_data .= $mm_address . '</br>';
    }
    if($mm_zip !== "" && $mm_show_zip == "yes"){
      $mm_info_window_data .= $mm_zip . '</br>';
    }
    if($mm_city !== "" && $mm_show_city == "yes"){
      $mm_info_window_data .= $mm_city . '</br>';
    }
    if($mm_country !== "" && $mm_show_country == "yes"){
      $mm_info_window_data .= $mm_country . '</br>';
    }
    if($mm_description !== "" && $mm_show_description == "yes"){
      $mm_info_window_data .= '<h6>' . $mm_description . '</h6> </br>';
    }
    if($mm_url !== "" && $mm_show_url == "yes"){
      $mm_info_window_data .= '<a href="' . $mm_url . '" class="mm_location_url" target="_blank">Website</a>';
    }

    $locations_a[] = [
        $mm_longitude, 
        $mm_latitude,      
        $mm_featured, 
        $mm_icon,
        $mm_featured_animation,
        $mm_info_window,
        $mm_info_window_data
      ];
    endwhile;

    wp_reset_postdata();


    $option_check = get_option('mm_plugin_center_check');

    if( $option_check['city'] != $map_settings['city'] ||
        $option_check['zip'] != $map_settings['zip'] ||
        $option_check['country'] != $map_settings['country'] ||
        $option_check['address'] != $map_settings['address'] ) {

      $center_at = geocode($map_settings['zip'].', '.$map_settings['city'].', '.$map_settings['country'].', '.$map_settings['address']);

      update_option( 'mm_center_longitude', $center_at[0] );
      update_option( 'mm_center_latitude', $center_at[1] );

      $option_check['zip'] = $map_settings['zip'];
      $option_check['city'] = $map_settings['city'];
      $option_check['country'] = $map_settings['country'];
      $option_check['address'] = $map_settings['address'];

      update_option( 'mm_plugin_center_check', $option_check); 

    } else {

      $center_at = array(
        get_option('mm_center_longitude'),
        get_option('mm_center_latitude')
        );

    }      
 
?>
<script>  
  var location_marker = <?php echo json_encode($locations_a); ?>;
  var map_options = <?php echo json_encode($map_options); ?>; 
  var center_at = <?php echo json_encode($center_at); ?>; 
</script>
<?php

  $mm_shortcode = '<div id="googleMap" style="width:100%;height:'.$map_settings['height'].'px;"></div>';

  return $mm_shortcode;
}
add_shortcode("mm_map", "mm_map"); 
?>