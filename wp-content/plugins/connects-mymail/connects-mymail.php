<?php
/**
* Plugin Name: Connects - MyMail Addon
* Plugin URI: 
* Description: Use this plugin to integrate MyMail with Connects.
* Version: 2.0.3
* Author: Brainstorm Force
* Author URI: https://www.brainstormforce.com/
* License: http://themeforest.net/licenses
*/

if(!class_exists('Smile_Mailer_Mymail')){
	class Smile_Mailer_Mymail{
	
		//Class variables
		private $slug;
		private $setting;
		
		/*
		 * Function Name: __construct
		 * Function Description: Constructor
		 */
		
		function __construct(){
			add_action( 'admin_init', array( $this,'init' ) );
			add_action( 'wp_ajax_get_mymail_data', array($this,'get_mymail_data' ));
			add_action( 'wp_ajax_mymail_add_subscriber', array($this,'mymail_add_subscriber' ));
			add_action( 'wp_ajax_nopriv_mymail_add_subscriber', array($this,'mymail_add_subscriber' ));
			$this->setting  = array(
				'name' => 'MyMail',
				'parameters' => array(),
				'where_to_find_url' => admin_url( 'edit.php?post_type=newsletter&page=mymail_lists' ),
				'logo_url' => plugins_url('images/logo.png', __FILE__)
			);
			$this->slug = 'mymail';
			add_action( 'admin_head', array( $this, 'hook_css' ) );
		}

		/*
		 * Function Name: hook_css
		 * Function Description: Adds background style script for mailer logo.
		 */


		function hook_css() {
			if( isset( $this->setting['logo_url'] ) ) {
				if( $this->setting['logo_url'] != '' ) {
					$style = '<style>table.bsf-connect-optins td.column-provider.'.$this->slug.'::after {background-image: url("'.$this->setting['logo_url'].'");}.bend-heading-section.bsf-connect-list-header .bend-head-logo.'.$this->slug.'::before {background-image: url("'.$this->setting['logo_url'].'");}</style>';
					echo $style;
				}
			}
			
		}
		
		
		/*
		 * Function Name: get_mymail_data
		 * Function Description: Get mymail input fields
		 */
		 
		function get_mymail_data(){
		
			$connected = false;
			ob_start();

			$lists = mymail('lists')->get();
            ?>
           <div class="bsf-cnlist-form-row <?php echo $this->slug; ?>-list">
		   	<?php
			if( !empty( $lists ) ) {
			?>
				<label for="<?php echo $this->slug; ?>-list"><?php echo __( "Select List", "smile" ); ?></label>
				<select id="<?php echo $this->slug; ?>-list" class="bsf-cnlist-select" name="<?php echo $this->slug; ?>-list">
			<?php
				foreach( $lists as $l ) {
			?>
					<option value="<?php echo $l->ID; ?>"><?php echo $l->name; ?></option>
			<?php
				}
			?>
				</select>
			<?php
			} else {
			?>
				<label for="<?php echo $this->slug; ?>-list"><?php echo __( "You need at least one list added in " . $this->setting['name'] . " before proceeding.", "smile" ); ?></label>
			<?php
			}
			?>
		   </div>
		   <div class="bsf-cnlist-form-row"> </div>

            <?php
            $content = ob_get_clean();
            $result['data'] = $content;
            $result['helplink'] = $this->setting['where_to_find_url'];
            $result['isconnected'] = $connected;
            echo json_encode($result);
            exit();
        }
		
		
		/*
		 * Function Name: mymail_add_subscriber
		 * Function Description: Add subscriber
		 */
		
		function mymail_add_subscriber(){
			$ret = true;
			$email_status = false;
            $style_id = isset( $_POST['style_id'] ) ? $_POST['style_id'] : '';
            $contact = $_POST['param'];
            $contact['source'] = ( isset( $_POST['source'] ) ) ? $_POST['source'] : '';
            $msg = isset( $_POST['message'] ) ? $_POST['message'] : __( 'Thanks for subscribing. Please check your mail and confirm the subscription.', 'smile' );

            //get double optin var
            $optinvar   = get_option( 'convert_plug_settings' );
            $d_optin    = isset($optinvar['cp-double-optin']) ? $optinvar['cp-double-optin'] : 1;
            
			//	Check Email in MX records
			if( isset( $_POST['param']['email'] ) ) {
                $email_status = ( !( isset( $_POST['only_conversion'] ) ? true : false ) ) ? apply_filters('cp_valid_mx_email', $_POST['param']['email'] ) : false;
            }

			if( $email_status ) {
				if( function_exists( "cp_add_subscriber_contact" ) ){
					$isuserupdated = cp_add_subscriber_contact( $_POST['option'] , $contact );
				}

				if ( !$isuserupdated ) {  // if user is updated dont count as a conversion
					// update conversions 
					smile_update_conversions( $style_id );
				}
				if( isset( $_POST['param']['email'] ) ) {
					$status = 'success';

					foreach( $_POST['param'] as $key => $p ) {
                        if( $key != 'user_id' && $key != 'date' ){
                        	$customfields[$key] = $p;
                        }
                    }

                    //set double opt-in
					$customfields['status'] = 1; 
					if( $d_optin == 1 ){
					   $customfields['status'] = 0;//force to send confirmation msg
					}

					$subscriber_id = mymail('subscribers')->add( $customfields );

					if( !is_wp_error( $subscriber_id ) ) {
						$lists = mymail('lists')->get();

						$does_exists = $this->check_if_list_exists( $_POST['list_id'], $lists );
						if( $does_exists ) {
							//list is present
							$success = mymail('subscribers')->assign_lists($subscriber_id, $_POST['list_id'], $remove_old = true);
						} else {
							// list is not present
							$success = false;
						}
						
						if( !$success ) {
							//error
							if( isset( $_POST['source'] ) ) {
				        		return false;
				        	} else {
				        		print_r(json_encode(array(
									'action' => ( isset( $_POST['message'] ) ) ? 'message' : 'redirect',
									'email_status' => $email_status,
									'status' => 'error',
									'message' => __( "Something went wrong. Please try again.", "smile" ),
									'url' => ( isset( $_POST['message'] ) ) ? 'none' : $_POST['redirect'],
								)));
								exit();
				        	}
							
						}

					} else {
						if( $subscriber_id->get_error_code() == 'email_exists' ) {
							///	Show message for already subscribed users
							$optinvar =	get_option( 'convert_plug_settings' );				
					    	$msg = ( $optinvar['cp-default-messages'] ) ? isset( $optinvar['cp-already-subscribed']) ? stripslashes( $optinvar['cp-already-subscribed'] ) : __( 'Already Subscribed!', 'smile' ) : __( 'Already Subscribed!', 'smile' );
						} else {
							$ret = false;
							$status = false;
							$msg = "Something went wrong. Please try again.";
						}
					}
				}
			} else {
				if( isset( $_POST['only_conversion'] ) ? true : false ){
					// update conversions 
					$status = 'success';
					smile_update_conversions( $style_id );
					$ret = true;
				} else {
					$msg = ( isset( $_POST['msg_wrong_email']  )  && $_POST['msg_wrong_email'] !== '' ) ? $_POST['msg_wrong_email'] : __( 'Please enter correct email address.', 'smile' );
					$status = 'error';
					$ret = false;
				}
			}

			if( isset( $_POST['source'] ) ) {
        		return $ret;
        	} else {
        		print_r(json_encode(array(
					'action' => ( isset( $_POST['message'] ) ) ? 'message' : 'redirect',
					'email_status' => $email_status,
					'status' => $status,
					'message' => $msg,
					'url' => ( isset( $_POST['message'] ) ) ? 'none' : $_POST['redirect'],
				)));

				exit();
        	}
		}


		/*
		 * Function Name: check_if_list_exists
		 * Function Description: Check if list id is present in given list
		 */

		function check_if_list_exists( $list_id = 0, $list = array() ) {
			if( !empty( $list ) && $list_id != 0 ) {
				foreach( $list as $l ) {
					if( $l->ID == $list_id ) {
						return true;
					}
				}
			}
			return false;
		}

		/*
		 * Function Name: mymail_error_notice
		 * Function Description: Admin notice for MyMail
		 */

		function mymail_error_notice() {
			$msg = '<strong>Connects - '.$this->setting['name'].' Addon</strong> requires <strong>"MyMail - Email Newsletter Plugin for WordPress"</strong> installed and activated.';
			echo"<div class=\"error\"> <p>" . $msg . "</p></div>"; 
		}
		
		/*
		 * Function Name: init
		 * Function Description: Register addon to ConvertPlug
		 */
		
		function init(){
			if( function_exists( 'mymail' ) ){
				if( function_exists( 'cp_register_addon' ) ) {
					cp_register_addon( $this->slug, $this->setting );
				}
			} else {
				add_action( 'admin_notices', array( $this, 'mymail_error_notice' ) ); 
			}
		}
	}
	new Smile_Mailer_Mymail;	
}

$bsf_core_version_file = realpath(dirname(__FILE__).'/admin/bsf-core/version.yml');
if(is_file($bsf_core_version_file)) {
	global $bsf_core_version, $bsf_core_path;
	$bsf_core_dir = realpath(dirname(__FILE__).'/admin/bsf-core/');
	$version = file_get_contents($bsf_core_version_file);
	if(version_compare($version, $bsf_core_version, '>')) {
		$bsf_core_version = $version;
		$bsf_core_path = $bsf_core_dir;
	}
}
add_action('init', 'bsf_core_load', 999);
if(!function_exists('bsf_core_load')) {
	function bsf_core_load() {
		global $bsf_core_version, $bsf_core_path;
		if(is_file(realpath($bsf_core_path.'/index.php'))) {
			include_once realpath($bsf_core_path.'/index.php');
		}
	}
}
?>