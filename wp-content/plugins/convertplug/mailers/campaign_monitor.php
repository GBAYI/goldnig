<?php
if(!class_exists('Smile_Mailer_CampaignMonitor')){
	class Smile_Mailer_CampaignMonitor{
		function __construct(){

			require_once('api/campaign_api/csrest_general.php');
			require_once('api/campaign_api/csrest_clients.php');
			require_once('api/campaign_api/csrest_subscribers.php');
			add_action( 'wp_ajax_get_campaignmonitor_data', array($this,'get_campaignmonitor_data' ));
			add_action( 'wp_ajax_update_campaignmonitor_authentication', array($this,'update_campaignmonitor_authentication' ));
			add_action( 'wp_ajax_disconnect_campaignmonitor', array($this,'disconnect_campaignmonitor' ));
			add_action( 'wp_ajax_campaignmonitor_add_subscriber', array($this,'campaignmonitor_add_subscriber' ));
			add_action( 'wp_ajax_nopriv_campaignmonitor_add_subscriber', array($this,'campaignmonitor_add_subscriber' ));
	
		}

		// retrieve mailer info data
		function get_campaignmonitor_data(){
			
			$connected = false;
			ob_start();
			$cm_api = get_option('campaignmonitor_api');
			$cm_client_id = get_option('campaignmonitor_client_id');

			if( $cm_api != '' ) 
            	$formstyle = 'style="display:none;"'; 
            else
            	$formstyle = '';
            ?>
			
			<div class="bsf-cnlist-form-row" <?php echo $formstyle; ?>>
				<label for="campaignmonitor_client_id"><?php _e( "Client ID", "smile" ); ?></label>
	            <input type="text" autocomplete="off" id="campaignmonitor_client_id" name="campaignmonitor-client-id" value="<?php echo esc_attr( $cm_client_id ); ?>"/>
	        </div>

            <div class="bsf-cnlist-form-row" <?php echo $formstyle; ?>>
				<label for="campaignmonitor_api_key"><?php _e( "Campaign Monitor API Key", "smile" ); ?></label>
				<input type="text" autocomplete="off" id="campaignmonitor_api_key" name="campaignmonitor-auth-key" value="<?php echo esc_attr( $cm_api ); ?>"/>
			</div>

            <div class="bsf-cnlist-form-row campaignmonitor-list">
            <?php
            if($cm_api != '') 
				$cm_lists = cpGetCMLists($cm_api,$cm_client_id);
			else
				$cm_lists = '';

			if( !$cm_lists ) $cm_lists = get_option('campaignmonitor_lists');
			if( !empty( $cm_lists ) ){
				$connected = true;
				$html = '<label for="campaignmonitor-list">'.__( "Select List", "smile" ).'</label>';
				$html .= '<select id="campaignmonitor-list" class="bsf-cnlist-select" name="campaignmonitor-list">';
				foreach($cm_lists as $id => $name) {
					$html .= '<option value="'.$id.'">'.$name.'</option>';
				}
				$html .= '</select>';
				echo $html;
			}
            ?>
            </div>

            <div class="bsf-cnlist-form-row">
	            <?php if( $cm_api == "" ) { ?>
	            	<button id="auth-campaignmonitor" class="button button-secondary" disabled><?php _e( "Authenticate Campaign Monitor", "smile" ); ?></button><span class="spinner" style="float: none;"></span>
	            <?php } else { ?>
	            	<div id="disconnect-campaignmonitor" class="disconnect-mailer" data-mailerslug="Campaign Monitor" data-mailer="campaignmonitor"><span><?php _e( "Use different 'Campaign Monitor' account?", "smile" ); ?></span></div><span class="spinner" style="float: none;"></span>
	            <?php } ?>
	        </div>

            <?php
            $content = ob_get_clean();            
            $result['data'] = $content;
            $result['helplink'] = 'https://www.campaignmonitor.com/api/getting-started/?&_ga=1.18810747.338212664.1439118258#clientid';
            $result['isconnected'] = $connected;
            echo json_encode($result);
            die();

		}
				
		function campaignmonitor_add_subscriber(){
			$post = $_POST;
			$data = array();
			
			$this->api_key = get_option('campaignmonitor_api');
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
				$url	= $post['redirect'];
			}

			$contact = array();
			$contact['name'] = $name;
			$contact['email'] = $email;
			$contact['date'] = date("j-n-Y");

			//	Check Email in MX records
			$email_status = apply_filters('cp_valid_mx_email', $_POST['email'] );
			if($email_status) {

				$status = 'success';
		
				$auth = array('api_key' => $this->api_key);
				$wrap = new CS_REST_Subscribers($list, $auth);

				$result = $wrap->add(array(
				    'EmailAddress' => $email,
				    'Name' => $name,
				    'Resubscribe' => true
				));

				if (!$result->was_successful()) {

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

		function update_campaignmonitor_authentication(){
			$post = $_POST;

			$data = array();
			$api_key = $post['authentication_token'];
			$client_id = $_POST['clientID'];

			$this->api_key = $api_key;
			
			$auth = array('api_key' => $this->api_key);
			$wrap = new CS_REST_General($auth);
			$result = $wrap->get_clients();

			if(!$result->was_successful()) {
				print_r(json_encode(array(
					'status' => "error",
					'message' => __( "Unable to authenticate. Please check client ID and API key.", "smile" ) 
				)));
				die();
			}		

			$wrap = new CS_REST_Clients($client_id, $auth);

			$lists = $wrap->get_lists();
		
			$lists = $lists->response;

			$cm_lists = array();
			$html = $query = '';
			$html .= '<label for="campaignmonitor-list">Select List</label>';
			$html .= '<select id="campaignmonitor-list" class="bsf-cnlist-select" name="campaignmonitor-list">';
			foreach($lists as $offset => $list) {
				$html .= '<option value="'.$list->ListID.'">'.$list->Name.'</option>';
				$query .= $list->ListID.'|'.$list->Name.',';
				$cm_lists[$list->ListID] = $list->Name;
			}
			$html .= '</select>';
			$html .= '<input type="hidden" id="mailer-all-lists" value="'.$query.'"/>';
			$html .= '<input type="hidden" id="mailer-list-action" value="update_campaignmonitor_list"/>';
			$html .= '<input type="hidden" id="mailer-list-api" value="'.$this->api_key.'"/>';

			ob_start();
			?>
			<div class="bsf-cnlist-form-row">
				<div id="disconnect-campaignmonitor" class="disconnect-mailer" data-mailerslug="Campaign Monitor" data-mailer="campaignmonitor">
					<span>
						<?php _e( "Use different 'Campaign Monitor' account?", "smile" ); ?>
					</span>
				</div>
				<span class="spinner" style="float: none;"></span>
			</div>
			<?php 
			$html .= ob_get_clean();

			update_option('campaignmonitor_client_id',$client_id);
			update_option('campaignmonitor_api',$api_key);
			update_option('campaignmonitor_lists',$cm_lists);	
			
			print_r(json_encode(array(
				'status' => "success",
				'message' => $html
			)));
			
			die();
		}
	
		
		function disconnect_campaignmonitor(){
			delete_option( 'campaignmonitor_api' );
			delete_option( 'campaignmonitor_client_id' );
			delete_option( 'campaignmonitor_lists' );
			
			$smile_lists = get_option('smile_lists');			
			if( !empty( $smile_lists ) ){ 
				foreach( $smile_lists as $key => $list ) {
					$provider = $list['list-provider'];
					if( $provider == "CampaignMonitor" ){
						$smile_lists[$key]['list-provider'] = "Convert Plug";
					}
				}
				update_option( 'smile_lists', $smile_lists );
			}
			
			print_r(json_encode(array(
                'message' => __( "disconnected", "smile" )
			)));
			die();
		}
	}
	new Smile_Mailer_CampaignMonitor;
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

	if( !function_exists( 'cpGetCMLists' ) ){
		function cpGetCMLists( $api_key,$client_id ) {
	
			$auth = array('api_key' => $api_key);
			$wrap = new CS_REST_General($auth);

			$result = $wrap->get_clients();

			if(!$result->was_successful()) {
				print_r(json_encode(array(
					'status' => "error",
					'message' => __( "Unable to authenticate.", "smile" )
				)));
				die();
			}

			$wrap = new CS_REST_Clients($client_id, $auth);

			$lists = $wrap->get_lists();		
			$lists = $lists->response;

			$cm_lists = array();
			foreach($lists as $offset => $list) {
				$cm_lists[$list->ListID] = $list->Name;
			}
			return $cm_lists;
		}
	}
	
}