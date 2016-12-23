<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'mm_post_types' );
function mm_post_types() {

  $labels = array(
    'name'                => __( 'Map Me', 'map-me' ),
    'singular_name'       => __( 'Map Me', 'map-me' ),
    'add_new'             => __( 'Add New Location', 'map-me' ),
    'add_new_item'        => __( 'Add New Location', 'map-me' ),
    'edit_item'           => __( 'Edit Location', 'map-me' ),
    'new_item'            => __( 'New Location', 'map-me' ),
    'all_items'           => __( 'Locations', 'map-me' ),
    'view_item'           => __( 'View Location', 'map-me' ),
    'search_items'        => __( 'Search Locations', 'map-me' ),
    'not_found'           => __( 'No Location found', 'map-me' ),
    'not_found_in_trash'  => __( 'No Location found in Trash', 'map-me' ),
    'menu_name'           => __( 'Map Me', 'map-me' ),
  );

  $supports = array( 'title' );  

  $args = array(
    'labels'              => $labels,
    'show_ui'             => true,
    'show_in_menu'        => 'mm_menu_page',
    'query_var'           => false,
    'rewrite'             => array( 'slug' => 'mm' ),
    'capability_type'     => 'post',
    'supports'            => $supports,
  );

  register_post_type( 'mm', $args );
}


//manage the columns of the `page` post type
function manage_columns_for_mm($columns){
    //add new columns
    $columns['country'] = __( 'Country', 'map-me' );
    $columns['city'] = __( 'City', 'map-me' );  
    $columns['featured'] = __( 'Featured', 'map-me' );
    return $columns;
}
add_action('manage_mm_posts_columns','manage_columns_for_mm');

//Populate custom columns for `page` post type
function mm_populate_page_columns($column,$post_id){

    //page content column
    if($column == 'country'){       
       $mm_country = get_post_meta($post_id, "mm_country", true);   
        echo $mm_country;       
    }
    if($column == 'city'){       
        $mm_city = get_post_meta($post_id, "mm_city", true);           
        echo $mm_city;     
    }
    if($column == 'featured'){       
        $mm_featured = get_post_meta($post_id, "mm_featured", true);           
        echo $mm_featured;     
    }
}
add_action('manage_mm_posts_custom_column','mm_populate_page_columns',10,2);


//sort columns
add_filter( 'manage_edit-mm_sortable_columns', 'mm_table_sorting' );
function mm_table_sorting( $columns ) {
  $columns['country'] = 'country';
  $columns['city'] = 'city';
  $columns['featured'] = 'featured';
  return $columns;
}

add_filter( 'request', 'mm_country_column_orderby' );
function mm_country_column_orderby( $vars ) {
    if ( isset( $vars['orderby'] ) && 'country' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 'mm_country',
            'orderby' => 'meta_value'
        ) );
    }
    return $vars;
}
add_filter( 'request', 'mm_city_column_orderby' );
function mm_city_column_orderby( $vars ) {
    if ( isset( $vars['orderby'] ) && 'city' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 'mm_city',
            'orderby' => 'meta_value'
        ) );
    }
    return $vars;
}
add_filter( 'request', 'mm_featured_column_orderby' );
function mm_featured_column_orderby( $vars ) {
    if ( isset( $vars['orderby'] ) && 'featured' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 'mm_featured',
            'orderby' => 'meta_value'
        ) );
    }
    return $vars;
}




/* Meta Boxes */

function location_data_box($object){
  wp_nonce_field(basename(__FILE__), "meta-box-nonce1");
  $mm_address = get_post_meta($object->ID, "mm_address", true);
  $mm_city = get_post_meta($object->ID, "mm_city", true);
  $mm_zip = get_post_meta($object->ID, "mm_zip", true);
  $mm_country = get_post_meta($object->ID, "mm_country", true);

  $mm_longitude = get_post_meta($object->ID, 'mm_longitude', true);
  $mm_latitude = get_post_meta($object->ID, 'mm_latitude', true);

  $mm_url = get_post_meta($object->ID, "mm_url", true);

  $mm_description = get_post_meta(get_the_ID(), "mm_description", true);

  $mm_show_address = get_post_meta(get_the_ID(), "mm_show_address", true);
  $mm_show_city = get_post_meta(get_the_ID(), "mm_show_city", true);
  $mm_show_zip = get_post_meta(get_the_ID(), "mm_show_zip", true);
  $mm_show_country = get_post_meta(get_the_ID(), "mm_show_country", true);
  $mm_show_description = get_post_meta(get_the_ID(), "mm_show_description", true);
  $mm_show_url = get_post_meta(get_the_ID(), "mm_show_url", true);

  if ($mm_show_address == "") {
    $mm_show_address = "yes";
  }
  if ($mm_show_city == "") {
    $mm_show_city = "yes";
  }
  if ($mm_show_zip == "") {
    $mm_show_zip = "yes";
  }
  if ($mm_show_country == "") {
    $mm_show_country = "yes";
  }
  if ($mm_show_description == "") {
    $mm_show_description = "yes";
  }
  if ($mm_show_url == "") {
    $mm_show_url = "yes";
  }

?>
    <table id="mm_location">
      <tbody>
      <tr style="height:30px; font-size:1.1em;">
        <th colspan="2"><?php _e("Location Info: ", 'map-me' ); ?></th> 
        <th style="width:200px;"><?php _e("Show in info window: ", 'map-me'); ?></th>        
      </tr>
      <tr style="height:38px;">
          <td><strong><?php _e("Address: ", 'map-me' ); ?></strong></td>
          <td><input type="text" name="mm_address" value="<?php echo $mm_address; ?>" /></td>
          <td class="show-info"><input type="checkbox" id="mm_show_address" name="mm_show_address" value="yes" <?php if($mm_show_address == "yes"){echo 'checked';} ?> /></td>
        </tr>
      <tr>
        <tr>
          <td><strong><?php _e("City: ", 'map-me' ); ?></strong></td>
          <td><input type="text" name="mm_city" value="<?php echo $mm_city; ?>" /></td>
          <td class="show-info"><input type="checkbox" id="mm_show_city" name="mm_show_city" value="yes" <?php if($mm_show_city == "yes"){echo 'checked';} ?> /></td>
        </tr>
        <tr>
          <td><strong><?php _e("Zip Code: ", 'map-me' ); ?></strong></td>
          <td><input type="number" name="mm_zip" value="<?php echo $mm_zip; ?>" /></td>
          <td class="show-info"><input type="checkbox" id="mm_show_zip" name="mm_show_zip" value="yes" <?php if($mm_show_zip == "yes"){echo 'checked';} ?> /></td>
        </tr>
        <tr>
          <td><strong><?php _e("Country: ", 'map-me' ); ?></strong></td>
          <td><input type="text" name="mm_country" value="<?php echo $mm_country; ?>" /></td>
          <td class="show-info"><input type="checkbox" id="mm_show_country" name="mm_show_country" value="yes" <?php if($mm_show_country == "yes"){echo 'checked';} ?> /></td>
        </tr> 
        <tr>
          <td><strong><?php _e("Website: ", 'map-me' ); ?></strong></td>
          <td><input type="url" name="mm_url" value="<?php echo $mm_url; ?>" /></td>
          <td class="show-info"><input type="checkbox" id="mm_show_url" name="mm_show_url" value="yes" <?php if($mm_show_url == "yes"){echo 'checked';} ?> /></td>
        </tr> 

        <tr style="height:15px;"></tr>  
             
        <tr>          
          <td><strong><?php _e("Longitude: ", 'map-me' ); ?></strong></td>
          <td><input type="text" name="mm_longitude" value="<?php echo $mm_longitude; ?>" disabled style="cursor:no-drop;"/></td>
        </tr>
        <tr>
          <td><strong><?php _e("Latitude: ", 'map-me' ); ?></strong></td>
          <td><input type="text" name="mm_latitude" value="<?php echo $mm_latitude; ?>" disabled style="cursor:no-drop;"/></td>
        </tr>

        <tr style="height:15px;"></tr>  

        <tr>
          <td><strong><?php _e("Short Description: ", 'map-me' ); ?></td></strong></td>
          <td><textarea name="mm_description" rows="7" maxlength="255"><?php echo $mm_description; ?></textarea></td>
          <td class="show-info"><input type="checkbox" id="mm_show_description" name="mm_show_description" value="yes" <?php if($mm_show_description == "yes"){echo 'checked';} ?> /></td>
        </tr>        
      </tbody>
    </table>
           
<?php        
}

function marker_data_box($object){
   wp_nonce_field(basename(__FILE__), "meta-box-nonce2");

   $mm_info_window = get_post_meta($object->ID, 'mm_info_window', true);

   $mm_featured = get_post_meta($object->ID, "mm_featured", true);
   $mm_featured_animation = get_post_meta($object->ID, "mm_featured_animation", true);

   if($mm_info_window == ""){
      $mm_info_window = "show";
   }

   if ($mm_featured_animation == "") {
      $mm_featured_animation = "DROP";
    }

   if($mm_featured == "yes"){
      $show = 'grid';
    } else {
      $show = 'none';
    }
?>

<table id="mm_location_settings">
    <tbody>
      <tr style="height:30px; font-size:1.1em;">      
        <td style="width:200px;"><strong><?php _e("Featured on Map: ", 'map-me' ); ?></strong></td>
        <td><input type="checkbox" id="mm_featured" name="mm_featured" value="yes" <?php if($mm_featured == "yes"){echo 'checked';} ?> /></td>                  
      </tr>
      <tr style="height:45px;">          
        <td class="mm_set_marker_animation" style="width:200px; display:<?php echo $show; ?>;"><strong><?php _e("Marker Animation: ", 'map-me' ); ?></strong></td>
        <td class="mm_set_marker_animation" style="display:<?php echo $show; ?>;">
          <span class="mm_marker_animation"><strong>Bounce: </strong></span><input type="radio" name="mm_featured_animation" value="BOUNCE" <?php if($mm_featured_animation == "BOUNCE"){echo 'checked';} ?> />
          </br>
          <span class="mm_marker_animation"><strong>Drop: </strong></span><input type="radio" name="mm_featured_animation" value="DROP" <?php if($mm_featured_animation == "DROP"){echo 'checked';} ?> />
          </td>
          
        </tr>
      <tr>   
      <tr>
        <td><strong><?php _e("Show Info Window: ", 'map-me'); ?></strong></td>
        <td><input type="checkbox" id="mm_info_window" name="mm_info_window" value="show" <?php if($mm_info_window == "show"){echo 'checked';} ?> /></td>
      </tr>
   </tbody>
</table>

<?php
}

function marker_icon_data_box($object){ 
  wp_nonce_field(basename(__FILE__), "meta-box-nonce3");

  $mm_icon = get_post_meta(get_the_ID(), "mm_icon", true);
  ?>

    <div id="mm_icon_set">

    <?php       
      $directory = icons_dir_path();
      $folder = icons_path();

      ?>

      <h3>Standard Icons</h3>

      <?php

      $files_standard = glob($directory . "standard/*.png");      

      foreach($files_standard as $filename){ 
        $location_icon = $folder.'standard/'.basename($filename);
      ?>

      <span class="mm_icon_group<?php if($mm_icon == $location_icon ){echo ' icon_checked';} ?>">
        <input type="radio" name="mm_icon" class="mm_icon_group_radio" value="<?php echo $location_icon; ?>" <?php if($mm_icon == $location_icon ){echo 'checked';} ?> />
        <img src="<?php echo $location_icon; ?>" class="mm_icon" /> 
      </span>

      <?php } 

      ?> 
      
      <h3>Other Icons</h3>

      <?php

      $files = glob($directory . "*.png");

      foreach($files as $filename){ 
        $location_icon = $folder.basename($filename);
      ?>

      <span class="mm_icon_group<?php if($mm_icon == $location_icon ){echo ' icon_checked';} ?>">
        <input type="radio" name="mm_icon" class="mm_icon_group_radio" value="<?php echo $location_icon; ?>" <?php if($mm_icon == $location_icon ){echo 'checked';} ?> />
        <img src="<?php echo $location_icon; ?>" class="mm_icon" /> 
      </span>

      <?php } ?>

    </div>    
    <?php

}

function mm_add_settings_box(){    
    add_meta_box("location_box", "Location", "location_data_box", "mm", "normal", "high", null);
    add_meta_box("marker_settings_box", "Marker Settings", "marker_data_box", "mm", "normal", "high", null);
    add_meta_box("marker_icon_box", "Choose icon", "marker_icon_data_box", "mm", "normal", "high", null);
}
add_action("add_meta_boxes", "mm_add_settings_box");


function mm_save_settings_box($post_id, $post, $update){
    if( (!isset($_POST["meta-box-nonce1"]) || !wp_verify_nonce($_POST["meta-box-nonce1"], basename(__FILE__))) && 
        (!isset($_POST["meta-box-nonce2"]) || !wp_verify_nonce($_POST["meta-box-nonce2"], basename(__FILE__))) && 
        (!isset($_POST["meta-box-nonce3"]) || !wp_verify_nonce($_POST["meta-box-nonce3"], basename(__FILE__))) ){
         return $post_id;
      }

    if(!current_user_can("edit_post", $post_id)){
        return $post_id;
      }

    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE){
        return $post_id;
      }

    $slug = "mm";
    if($slug != $post->post_type){
        return $post_id;
      }



    $meta_mm_address = "";  
    $meta_mm_city = "";
    $meta_mm_zip = "";
    $meta_mm_country = ""; 
    $meta_mm_longitude = ""; 
    $meta_mm_latitude = ""; 
    $meta_mm_url = "";
    $meta_mm_featured = "";
    $meta_mm_featured_animation = "";
    $meta_mm_description = "";
    $meta_mm_icon = ""; 
    $meta_mm_info_window = "";

    $meta_mm_show_address = "";
    $meta_mm_show_city = "";
    $meta_mm_show_zip = "";
    $meta_mm_show_country = "";
    $meta_mm_show_description = "";
    $meta_mm_show_url = "";


    if(isset($_POST["mm_address"])){
        $meta_mm_address = $_POST["mm_address"]; 
        update_post_meta($post_id, "mm_address", $meta_mm_address);
        $geo_address = $meta_mm_address;         
    }
    if(isset($_POST["mm_city"])){
        $meta_mm_city = $_POST["mm_city"]; 
        update_post_meta($post_id, "mm_city", $meta_mm_city); 
        $geo_city = $meta_mm_city;          
    } 
    if(isset($_POST["mm_zip"])){
        $meta_mm_zip = $_POST["mm_zip"]; 
        update_post_meta($post_id, "mm_zip", $meta_mm_zip);  
        $geo_zip = $meta_mm_zip;         
    }   
    if(isset($_POST["mm_country"])){
        $meta_mm_country = $_POST["mm_country"]; 
        update_post_meta($post_id, "mm_country", $meta_mm_country); 
        $geo_country = $meta_mm_country;          
    }

      $address = $geo_zip .', '. $geo_city .', '. $geo_address .', '. $geo_country;
      $lat_lon = geocode($address);

      if($lat_lon){
        update_post_meta($post_id, "mm_longitude", $lat_lon[0]);  
        update_post_meta($post_id, "mm_latitude", $lat_lon[1]);
      }
    

    if(isset($_POST["mm_url"])){
        $meta_mm_url = $_POST["mm_url"]; 
        update_post_meta($post_id, "mm_url", $meta_mm_url);          
    }
    if(isset($_POST["mm_featured"])){
        $meta_mm_featured = $_POST["mm_featured"]; 
        update_post_meta($post_id, "mm_featured", $meta_mm_featured);          
    } else {
        update_post_meta($post_id, "mm_featured", 'no'); 
    }
    if(isset($_POST["mm_featured_animation"])){
        $meta_mm_featured_animation = $_POST["mm_featured_animation"]; 
        update_post_meta($post_id, "mm_featured_animation", $meta_mm_featured_animation);          
    }   
    if(isset($_POST["mm_info_window"])){
        $meta_mm_info_window = $_POST["mm_info_window"]; 
        update_post_meta($post_id, "mm_info_window", $meta_mm_info_window);          
    } else {
        update_post_meta($post_id, "mm_info_window", 'no'); 
    }

    if(isset($_POST["mm_description"])){
        $meta_mm_description = $_POST["mm_description"]; 
        update_post_meta($post_id, "mm_description", $meta_mm_description);          
    }

    if(isset($_POST["mm_icon"])){
        $meta_mm_icon = $_POST["mm_icon"]; 
        update_post_meta($post_id, "mm_icon", $meta_mm_icon);          
    }


    if(isset($_POST["mm_show_address"])){
        $meta_mm_show_address = $_POST["mm_show_address"]; 
        update_post_meta($post_id, "mm_show_address", $meta_mm_show_address);          
    } else {
        update_post_meta($post_id, "mm_show_address", 'no'); 
    }
    if(isset($_POST["mm_show_city"])){
        $meta_mm_show_city = $_POST["mm_show_city"]; 
        update_post_meta($post_id, "mm_show_city", $meta_mm_show_city);          
    } else {
        update_post_meta($post_id, "mm_show_city", 'no'); 
    }
    if(isset($_POST["mm_show_zip"])){
        $meta_mm_show_zip = $_POST["mm_show_zip"]; 
        update_post_meta($post_id, "mm_show_zip", $meta_mm_show_zip);          
    } else {
        update_post_meta($post_id, "mm_show_zip", 'no'); 
    }
    if(isset($_POST["mm_show_country"])){
        $meta_mm_show_country = $_POST["mm_show_country"]; 
        update_post_meta($post_id, "mm_show_country", $meta_mm_show_country);          
    } else {
        update_post_meta($post_id, "mm_show_country", 'no'); 
    }
    if(isset($_POST["mm_show_description"])){
        $meta_mm_show_description = $_POST["mm_show_description"]; 
        update_post_meta($post_id, "mm_show_description", $meta_mm_show_description);          
    } else {
        update_post_meta($post_id, "mm_show_description", 'no'); 
    }
    if(isset($_POST["mm_show_url"])){
        $meta_mm_show_url = $_POST["mm_show_url"]; 
        update_post_meta($post_id, "mm_show_url", $meta_mm_show_url);          
    } else {
        update_post_meta($post_id, "mm_show_url", 'no'); 
    }
  
       
}

add_action("save_post", "mm_save_settings_box", 10, 3);

?>