<?php
if(!class_exists('Smile_Mailer_Mailchimp')){
	class Smile_Mailer_Mailchimp{
		function __construct(){
	
			add_action( 'wp_ajax_get_mailchimp_data', array($this,'get_mailchimp_data' ));
			add_action( 'wp_ajax_update_mailchimp_authentication', array($this,'update_mailchimp_authentication' ));
			add_action( 'wp_ajax_disconnect_mailchimp', array($this,'disconnect_mailchimp' ));
			add_action( 'wp_ajax_mailchimp_add_subscriber', array($this,'mailchimp_add_subscriber' ));
			add_action( 'wp_ajax_nopriv_mailchimp_add_subscriber', array($this,'mailchimp_add_subscriber' ));
		}

		/* 
		* retrieve mailer info 
		* @Since 1.0
		*/
		function get_mailchimp_data(){
			
			$connected = false;
			ob_start();
			$ac_api = get_option('activecampaign_api');

			$mc_api = get_option('mailchimp_api');
          
            if( $mc_api != '' ) 
            	$formstyle = 'style="display:none;"'; 
            else
            	$formstyle = '';
            ?>
			<div class="bsf-cnlist-form-row" <?php echo $formstyle; ?>>					
				<label for="cp-list-name" ><?php _e( "MailChimp API Key", "smile" ); ?></label>
            	<input type="text" autocomplete="off" id="mailchimp_api_key" name="mailchimp-auth-key" value="<?php echo esc_attr( $mc_api ); ?>"/>
	        </div>

            <div class="bsf-cnlist-form-row mailchimp-list">
	            <?php
				$mc_lists = cpGetMCLists($mc_api);
				if( !$mc_lists ) $mc_lists = get_option('mailchimp_lists');
				if( !empty( $mc_lists ) ){
					$connected = true;
					$html = '<label for="mailchimp-list">'.__( "Select List", "smile" ).'</label>';
					$html .= '<select id="mailchimp-list" class="bsf-cnlist-select" name="mailchimp-list">';
					foreach($mc_lists as $id => $name) {
						$html .= '<option value="'.$id.'">'.$name.'</option>';
					}
					$html .= '</select>';
					echo $html;
				}
	            ?>
            </div>

            <div class="bsf-cnlist-form-row">
	            <?php if( $mc_api == "" ) { ?>
	            	<button id="auth-mailchimp" class="button button-secondary" disabled><?php _e( "Authenticate MailChimp", "smile" ); ?></button><span class="spinner" style="float: none;"></span>
	            <?php } else { ?>
	            	<div id="disconnect-mailchimp" class="disconnect-mailer" data-mailerslug="Mailchimp" data-mailer="mailchimp"><span><?php _e( "Use different 'MailChimp' account?", "smile" ); ?></span></div><span class="spinner" style="float: none;"></span>
	            <?php } ?>
	        </div>

            <?php
            $content = ob_get_clean();

            $result['data'] = $content;
            $result['helplink'] = 'http://kb.mailchimp.com/accounts/management/about-api-keys';
            $result['isconnected'] = $connected;
            echo json_encode($result);
            die();

		}
			
		/* 
		* Add subscriber to mailchimp
		* @Since 1.0
		*/
		function mailchimp_add_subscriber(){
			
			$post = $_POST;
			$data = array();
			
			$api_key = get_option( 'mailchimp_api' );

			$on_success = isset( $post['message'] ) ? 'message' : 'redirect';
			$msg_wrong_email = ( isset( $post['msg_wrong_email']  )  && $post['msg_wrong_email'] !== '' ) ? $post['msg_wrong_email'] : __( 'Please enter correct email address.', 'smile' );

			$msg = isset( $_POST['message'] ) ? $_POST['message'] : __( 'Thanks for subscribing. Please check your mail and confirm the subscription.', 'smile' );

			if($on_success == 'message'){
				$action	= 'message';
				$url	= 'none';
			} else {
				$action	= 'redirect';
				$url	= $post['redirect'];
			}

			//	Check Email in MX records
			$email_status = apply_filters('cp_valid_mx_email', $_POST['email'] );
			if($email_status) {

				$status = 'success';

				$this->api_key = $api_key;
				$dash_position = strpos( $api_key, '-' );
		
				if( $dash_position !== false ) {
					$this->api_url = 'https://' . substr( $api_key, $dash_position + 1 ) . '.api.mailchimp.com/2.0/';
				}
							
				$method = 'lists/subscribe';
				$data['apikey'] = $this->api_key;
				$data['id'] = $post['list_id'];
				$data['email'] = array(
					'email' => $post['email']
				);

				if( isset( $post['name'] ) ){
					$data['merge_vars'] = array(
						'FNAME' => $post['name']
					);
				}
				
				$contact = array();
				$contact['name'] = isset( $_POST['name'] ) ? $_POST['name'] : '';
				$contact['email'] = $_POST['email'];
				$contact['date'] = date("j-n-Y");
				
				$url = $this->api_url . $method . '.json';
		
				$response = wp_remote_post( $url, array( 
					'body' => $data,
					'timeout' => 15,
					'headers' => array('Accept-Encoding' => ''),
					'sslverify' => false
					) 
				); 
		
				// test for wp errors
				if( is_wp_error( $response ) ) {
					
					print_r(json_encode(array(
						'action' => $action,
						'email_status' => $email_status,
						'status' => 'error',
						'message' => __( "Something went wrong. Please try again.", "smile" ),
						'url' => $url,
					)));
					die();
				}
					
				$body = wp_remote_retrieve_body( $response );
				$request = json_decode( $body );
				
				$style_id = $_POST['style_id'];
				$option = $_POST['option'];
				
				if( function_exists( "cp_add_subscriber_contact" ) ){
					$isuserupdated = cp_add_subscriber_contact( $_POST['option'] ,$contact );
				}

				if ( !$isuserupdated ) {  // if user is updated dont count as a conversion
						// update conversions 
						smile_update_conversions($style_id);
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

		/* 
		* Authentication
		* @Since 1.0
		*/
		function update_mailchimp_authentication(){
			$post = $_POST;
			$data = array();
			$api_key = $post['authentication_token'];
			$this->api_url = '';
			
			if( $api_key == "" ){
				print_r(json_encode(array(
					'status' => "error",
					'message' => __( "Please provide valid API Key for your mailchimp account.", "smile" )
				)));
				die();
			}
			
			$this->api_key = $api_key;
			$dash_position = strpos( $api_key, '-' );
	
			if( $dash_position !== false ) {
				$this->api_url = 'https://' . substr( $api_key, $dash_position + 1 ) . '.api.mailchimp.com/2.0/';
			}
			$method = 'lists/list';
			$data['apikey'] = $this->api_key;
			$url = $this->api_url . $method . '.json';
	
			$response = wp_remote_post( $url, array( 
				'body' => $data,
				'timeout' => 15,
				'headers' => array('Accept-Encoding' => ''),
				'sslverify' => false
				) 
			); 
	
			// test for wp errors
			if( is_wp_error( $response ) ) {
				
				print_r(json_encode(array(
					'status' => "error", 
					'message' => "HTTP Error: " . $response->get_error_message()
				)));
				die();
			}
			
			$body = wp_remote_retrieve_body( $response );
			$request = json_decode( $body );
			$lists = (array)$request->data;
			$mc_lists = array();
			$html = $query = '';
			$html .= '<label for="mailchimp-list">Select List</label>';
			$html .= '<select id="mailchimp-list" class="bsf-cnlist-select" name="mailchimp-list">';
			foreach($lists as $offset => $list) {
				$html .= '<option value="'.$list->id.'">'.$list->name.'</option>';
				$query .= $list->id.'|'.$list->name.',';
				$mc_lists[$list->id] = $list->name;
			}
			$html .= '</select>';
			$html .= '<input type="hidden" id="mailer-all-lists" value="'.$query.'"/>';
			$html .= '<input type="hidden" id="mailer-list-action" value="update_mailchimp_list"/>';
			$html .= '<input type="hidden" id="mailer-list-api" value="'.$this->api_key.'"/>';

			ob_start();
			?>
			<div class="bsf-cnlist-form-row">
				<div id="disconnect-mailchimp" class="disconnect-mailer" data-mailerslug="Mailchimp" data-mailer="mailchimp">
					<span>
						<?php _e( "Use different 'MailChimp' account?", "smile" ); ?>
					</span>
				</div>
				<span class="spinner" style="float: none;"></span>
			</div>
			<?php 
			$html .= ob_get_clean();

			update_option('mailchimp_api',$api_key);
			update_option('mailchimp_lists',$mc_lists);		
			
			print_r(json_encode(array(
				'status' => "success",
				'message' => $html
			)));
			
			die();
		}
		
		/* 
		* Disconnect mailchimp
		* @Since 1.0
		*/
		function disconnect_mailchimp(){
			delete_option( 'mailchimp_api' );
			delete_option( 'mailchimp_lists' );
			
			$smile_lists = get_option('smile_lists');			
			if( !empty( $smile_lists ) ){ 
				foreach( $smile_lists as $key => $list ) {
					$provider = $list['list-provider'];
					if( $provider == "Mailchimp" ){
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
	new Smile_Mailer_Mailchimp;
	if(!function_exists('searchForMailer')){
		function searchForMailer($id, $array) {
		   foreach ($array as $key => $val) {
			   if ($val['mailer'] === $id) {
				   return $key;
			   }
		   }
		   return null;
		}
	}
	
	if( !function_exists( 'cpGetMCLists' ) ){
		/* 
		* Get lists from mailchimp
		* @Since 1.0
		*/
		function cpGetMCLists( $api_key ) {
			$api_key = $api_key;
			$data = array();
			$dash_position = strpos( $api_key, '-' );
	
			if( $dash_position !== false ) {
				$api_url = 'https://' . substr( $api_key, $dash_position + 1 ) . '.api.mailchimp.com/2.0/';
			} else {
				return false;
			}
			$method = 'lists/list';
			$data['apikey'] = $api_key;
			$url = $api_url . $method . '.json';
	
			$response = wp_remote_post( $url, array( 
				'body' => $data,
				'timeout' => 15,
				'headers' => array('Accept-Encoding' => ''),
				'sslverify' => false
				) 
			); 
	
			// test for wp errors
			if( is_wp_error( $response ) ) {
				return false;
				exit;
			}
			
			$body = wp_remote_retrieve_body( $response );
			$request = json_decode( $body );
			$lists = (array)$request->data;
			$mc_lists = array();
			foreach($lists as $offset => $list) {
				$mc_lists[$list->id] = $list->name;
			}
			return $mc_lists;
		}
	}

}