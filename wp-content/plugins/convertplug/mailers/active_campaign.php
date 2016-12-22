<?php
if(!class_exists('Smile_Mailer_ActiveCampaign')){
	class Smile_Mailer_ActiveCampaign{
		function __construct(){

			require_once('api/activecamp_api/ActiveCampaign.class.php');
			require_once('api/activecamp_api/Auth.class.php');
			add_action( 'wp_ajax_get_activecampaign_data', array($this,'get_activecampaign_data' ));
			add_action( 'wp_ajax_update_activecampaign_authentication', array($this,'update_activecampaign_authentication' ));
			add_action( 'wp_ajax_disconnect_activecampaign', array($this,'disconnect_activecampaign' ));
			add_action( 'wp_ajax_activecampaign_add_subscriber', array($this,'activecampaign_add_subscriber' ));
			add_action( 'wp_ajax_nopriv_activecampaign_add_subscriber', array($this,'activecampaign_add_subscriber' ));
	
		}

		// retrieve mailer info data
		function get_activecampaign_data(){
			
			$connected = false;
			ob_start();
			$ac_api = get_option('activecampaign_api');
			$ac_url = get_option('activecampaign_url');

			if( $ac_api != '' ) 
            	$formstyle = 'style="display:none;"'; 
            else
            	$formstyle = '';
            ?>
			
			<div class="bsf-cnlist-form-row" <?php echo $formstyle; ?>>
				<label for="activecampaign-list-name"><?php _e( "Active Campaign API URL", "smile" ); ?></label>
	            <input type="text" autocomplete="off" id="activecampaign_url" name="activecampaign-client-id" value="<?php echo esc_attr( $ac_url ); ?>"/>
	        </div>

            <div class="bsf-cnlist-form-row" <?php echo $formstyle; ?>>
	            <label for="activecampaign-list-name"><?php _e( "Active Campaign API Key", "smile" ); ?></label>
	            <input type="text" autocomplete="off" id="activecampaign_api_key" name="activecampaign-auth-key" value="<?php echo esc_attr( $ac_api ); ?>"/>
	        </div>

            <div class="bsf-cnlist-form-row activecampaign-list">
	            <?php
	   	        if($ac_api) 
				 	$ac_lists = cpGetACLists($ac_api,$ac_url);
				else
					$ac_lists = '';

				if( !$ac_lists ) $ac_lists = get_option('activecampaign_lists');
				if( !empty( $ac_lists ) ){
					$connected = true;
					$html = '<label for="activecampaign-list">'.__( "Select List", "smile" ).'</label>';
					$html .= '<select id="activecampaign-list" class="bsf-cnlist-select" name="activecampaign-list">';
					foreach($ac_lists as $id => $name) {
						$html .= '<option value="'.$id.'">'.$name.'</option>';
					}
					$html .= '</select>';
					echo $html;
				}
	            ?>
            </div>

            <div class="bsf-cnlist-form-row">
	            <?php if( $ac_api == "" ) { ?>
	            	<button id="auth-activecampaign" class="button button-secondary" disabled><?php _e( "Authenticate Active Campaign","smile" ); ?></button><span class="spinner" style="float: none;"></span>
	            <?php } else { ?>
	            	<div id="disconnect-activecampaign" class="disconnect-mailer" data-mailerslug="Active Campaign" data-mailer="activecampaign"><span><?php _e( "Use different 'Active Campaign' account?", "smile" ); ?></span></div><span class="spinner" style="float: none;"></span>
	            <?php } ?>
	        </div>

            <?php
            $content = ob_get_clean();
            
            $result['data'] = $content;
            $result['helplink'] = 'http://www.activecampaign.com/help/using-the-api/';
            $result['isconnected'] = $connected;
            echo json_encode($result);
            die();

		}
				
		function activecampaign_add_subscriber(){
			
			$post = $_POST;			
			$this->api_key = get_option('activecampaign_api');
			$campurl = get_option('activecampaign_url');			 
			$name = isset( $_POST['name'] ) ? $_POST['name'] : '';		
			$email = $post['email'];			
			$list = $post['list_id'];	
			$on_success = isset( $post['message'] ) ? 'message' : 'redirect';
			$msg_wrong_email = ( isset( $post['msg_wrong_email']  )  && $post['msg_wrong_email'] !== '' ) ? $post['msg_wrong_email'] : __( 'Please enter correct email address.', 'smile' );
			$msg = isset( $_POST['message'] ) ? $_POST['message'] : __( 'Thanks for subscribing. Please check your mail and confirm the subscription.', 'smile' );

			if($on_success == 'message'){
				$action	= 'message';
				$url	= 'none';
			} else {
				$action	= 'redirect';
				$url	= $data['redirect'];
			}

			$contact = array();
			$contact['name'] = $name;
			$contact['email'] = $email;
			$contact['date'] = date("j-n-Y");
			
			$data = array(
				"email"           => $email,
				"first_name"      => $name,
				"last_name"       => "",
				"p[{$list}]"      => $list,
				"status[{$list}]" => 1, // "Active" status
			);
			
			//	Check Email in MX records
			$email_status = apply_filters('cp_valid_mx_email', $_POST['email'] );
			if($email_status) {

				$status = 'success';
				
				// Add user to contacts if MX rexord is valid
				$ac = new ActiveCampaign($campurl, $this->api_key);

				// sync contacts with mailer
				$contact_sync = $ac->api("contact/sync", $data);

				if( !is_object($contact_sync) || ( is_object($contact_sync) && !(int)$contact_sync->success ) ) { 

					print_r(json_encode(array(
						'action' => $action,
						'email_status' => $email_status,
						'status' => 'error',
						'message' => __( "Something went wrong. Please try again.", "smile"),
						'url' => $url,
					)));
					die();

				} else {
				
					$style_id = $_POST['style_id'];
					$option = $_POST['option'];

					// add user to central contacts database
					if( function_exists( "cp_add_subscriber_contact" ) ){
						$isuserupdated = cp_add_subscriber_contact( $option ,$contact );
					}

					if ( !$isuserupdated ) {  // if user is updated dont count as a conversion
							// update conversions 
							smile_update_conversions($style_id);
					}
				}	

			} else {
				$msg = $msg_wrong_email;
				$status = 'error';
			}

			print_r(json_encode(array(
				'action' => $action,
				'email_status' => $email_status,
				'status' => $status,
				'message' => $msg,
				'url' => $url,
			)));

			die();
		}

		function update_activecampaign_authentication(){
			$post = $_POST;
			$data = array();
			$this->api_key = $post['authentication_token'];
			$campurl = $_POST['campaingURL'];
			
			$ac = new ActiveCampaign($campurl, $this->api_key);

			if (!(int)$ac->credentials_test()) {

				print_r(json_encode(array(
					'status' => "error",
					'message' => __( "Access denied: Invalid credentials (URL and/or API key).", "smile" )
				)));
				die();
			}

			$param = array(
				"api_action" => "list_list",
				"api_key"    => $this->api_key,
				"ids"   => "all",
				"full" => 0				
			);

			$lists = $ac->api("list/list_", $param);	

			if( $lists->result_code == 0 ) { 
				print_r(json_encode(array(
					'status' => "error",
					'message' => __( "You have zero lists in your Active Campaign account. You must have at least one list before integration." , "smile" )
				)));
				die();
			}

			$ac_lists = array();
			$html = $query = '';
			$html .= '<label for="activecampaign-list">'.__( "Select List", "smile" ).'</label>';
			$html .= '<select id="activecampaign-list" class="bsf-cnlist-select" name="activecampaign-list">';
			foreach( $lists as $offset => $list ) {				
				if( isset($list->id) ) {
					$html .= '<option value="'.$list->id.'">'.$list->name.'</option>';
					$query .= $list->id.'|'.$list->name.',';
					$ac_lists[$list->id] = $list->name;
				}
			}
	
			$html .= '</select>';
			$html .= '<input type="hidden" id="mailer-all-lists" value="'.esc_attr($query).'"/>';
			$html .= '<input type="hidden" id="mailer-list-action" value="update_activecampaign_list"/>';
			$html .= '<input type="hidden" id="mailer-list-api" value="'.esc_attr( $this->api_key ).'"/>';

			ob_start();
			?>
			<div class="bsf-cnlist-form-row">
				<div id="disconnect-activecampaign" class="disconnect-mailer" data-mailerslug="Active Campaign" data-mailer="activecampaign">
					<span>
						<?php _e( "Use different 'Active Campaign' account?", "smile" ); ?>
					</span>
				</div>
				<span class="spinner" style="float: none;"></span>
			</div>
			<?php 
			$html .= ob_get_clean();

			update_option('activecampaign_url',$campurl);
			update_option('activecampaign_api',$this->api_key);
			update_option('activecampaign_lists',$ac_lists);		
			
			print_r(json_encode(array(
				'status' => "success",
				'message' => $html
			)));
			
			die();
		}	
		
		function disconnect_activecampaign(){
			delete_option( 'activecampaign_api' );
			delete_option( 'activecampaign_url' );
			delete_option( 'activecampaign_lists' );
			
			$smile_lists = get_option('smile_lists');			
			if( !empty( $smile_lists ) ){ 
				foreach( $smile_lists as $key => $list ) {
					$provider = $list['list-provider'];
					if( $provider == "ActiveCampaign" ){
						$smile_lists[$key]['list-provider'] = "Convert Plug";
					}
				}
				update_option( 'smile_lists', $smile_lists );
			}
			
			print_r(json_encode(array(
                'message' => "disconnected",
			)));
			die();
		}
	}
	new Smile_Mailer_ActiveCampaign;
	if(!function_exists('searchForMailer')){
		function searchForMailer($id, $array) {
		   foreach ($array as $key => $val) {
			   if ($val['list-provider'] === $id) {
				   return $key;
			   }
		   }
		   return null;
		}
	}


	if( !function_exists( 'cpGetACLists' ) ){
		function cpGetACLists( $api_key,$url ) {
	
			$ac = new ActiveCampaign($url, $api_key);
			$param = array(
				"api_action" => "list_list",
				"api_key"    => $api_key,
				"ids"   => "all",
				"full" => 0				
			);

			$lists = $ac->api("list/list_", $param);

			$ac_lists = array();
			foreach($lists as $offset => $list) {
				if(isset($list->id))
					$ac_lists[$list->id] = $list->name;
			}
			return $ac_lists;
		}
	}
}