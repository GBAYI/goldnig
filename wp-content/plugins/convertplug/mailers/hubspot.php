<?php
if(!class_exists('Smile_Mailer_Hubspot')){
	class Smile_Mailer_Hubspot{
		function __construct(){

			require_once('api/hubspot/class.lists.php');
			require_once('api/hubspot/class.contacts.php');
			add_action( 'wp_ajax_get_hubspot_data', array($this,'get_hubspot_data' ));
			add_action( 'wp_ajax_update_hubspot_authentication', array($this,'update_hubspot_authentication' ));
			add_action( 'wp_ajax_disconnect_hubspot', array($this,'disconnect_hubspot' ));
			add_action( 'wp_ajax_hubspot_add_subscriber', array($this,'hubspot_add_subscriber' ));
			add_action( 'wp_ajax_nopriv_hubspot_add_subscriber', array($this,'hubspot_add_subscriber' ));
		}

		// retrieve mailer info data
		function get_hubspot_data(){
			
			$connected = false;
			ob_start();
			$hubspot_api = get_option('hubspot_api');
			if( $hubspot_api != '' ) 
            	$formstyle = 'style="display:none;"'; 
            else
            	$formstyle = '';
            ?>
			
			<div class="bsf-cnlist-form-row" <?php echo $formstyle; ?>>
				<label for="cp-list-name"><?php _e( "HubSpot API Key", "smile" ); ?></label>
	            <input type="text" autocomplete="off" id="hubspot_api_key" name="hubspot-api-key" value="<?php echo esc_attr( $hubspot_api ); ?>"/>
	        </div>

            <div class="bsf-cnlist-form-row hubspot-list">
	            <?php
	            if($hubspot_api != '') 
					$hs_lists = cpGetHSLists($hubspot_api);
				else
					$hs_lists = '';

				if( !$hs_lists ) $hs_lists = get_option('hubspot_lists');
				if( !empty( $hs_lists ) ){
					$connected = true;
					$html = '<label for="hubspot-list">'.__( "Select List", "smile" ).'</label>';
					$html .= '<select id="hubspot-list" class="bsf-cnlist-select" name="hubspot-list">';
					foreach($hs_lists as $id => $name) {
						$html .= '<option value="'.$id.'">'.$name.'</option>';
					}
					$html .= '</select>';
					echo $html;
				}
	            ?>
            </div>

            <div class="bsf-cnlist-form-row">
	            <?php if( $hubspot_api == "" ) { ?>
	            	<button id="auth-hubspot" class="button button-secondary" disabled><?php _e( "Authenticate Hubspot", "smile" ); ?></button><span class="spinner" style="float: none;"></span>
	            <?php } else { ?>
	            	<div id="disconnect-hubspot" class="disconnect-mailer" data-mailerslug="Hubspot" data-mailer="hubspot"><span><?php _e( "Use different 'Hubspot' account?", "smile" ); ?></span></div><span class="spinner" style="float: none;"></span>
	            <?php } ?>
	        </div>

            <?php
            $content = ob_get_clean();
            
            $result['data'] = $content;
            $result['helplink'] = 'http://help.hubspot.com/articles/KCS_Article/Integrations/How-do-I-get-my-HubSpot-API-key';
            $result['isconnected'] = $connected;
            echo json_encode($result);
            die();

		}
				
		function hubspot_add_subscriber(){
			$post = $_POST;
			$data = array();
			
			$this->api_key = get_option('hubspot_api');
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
		
				$contacts = new HubSpot_Contacts($this->api_key);
			    //Create Contact
			    $params =  array('email' => $email, 'firstname' => $name );

			    try {

				    $createdContact = $contacts->create_contact($params);

				    if(isset($createdContact->{'status'}) && $createdContact->{'status'} == 'error'){
				    	$contactProfile = $createdContact->identityProfile;
				    	$contactID = $contactProfile->vid;
				    	$contacts->update_contact($contactID,$params);				    	
				    } else {
				    	$contactID = $createdContact->{'vid'};
				    }   

				    $lists = new HubSpot_Lists($this->api_key);
				   	$contacts_to_add = array($contactID);
				   	$lists->add_contacts_to_list($contacts_to_add,$list);

				} catch (Exception $e) {

					print_r(json_encode(array(
						'action' => $action,
						'email_status' => $email_status,
						'status' => 'error',
						'message' => __( "Something went wrong. Please try again.", "smile" ),
						'url' => $url,
					)));
					die();
				}
				
				$style_id = $_POST['style_id'];
				$option = $_POST['option'];

				if( function_exists( "cp_add_subscriber_contact" ) ){
					$isuserupdated = cp_add_subscriber_contact( $option ,$contact );
				}

				if ( !$isuserupdated ) {  // if user is updated dont count as a conversion
						// update conversions 
						smile_update_conversions($style_id);
				}

			} else {
				$msg = $msg_wrong_email;
				$status = 'error';
			}
			
			if($on_success == 'message'){
				$action	= 'message';
				$url	= 'none';
			} else {
				$action	= 'redirect';
				$url	= $post['redirect'];
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

		function update_hubspot_authentication(){
			$post = $_POST;

			$data = array();
			$HAPIKey = $post['api_key'];

			$listsObj = new HubSpot_Lists($HAPIKey);
			$lists = $listsObj->get_static_lists(null);

			if( is_null($lists) ) {
				print_r(json_encode(array(
					'status' => "error",
					'message' => __( "Failed to authenticate. Please check API Key", "smile" )
				)));
				die();
			}
			
			if( is_array( $lists->lists ) && empty( $lists->lists ) ) {
				print_r(json_encode(array(
					'status' => "error",
					'message' => __( "You have zero static lists in your HubSpot account. You must have at least one static list before integration." , "smile" )
				)));
				die();
			}
        	
			$hs_lists = array();
			$html = $query = '';
			$html .= '<label for="hubspot-list"  >Select List</label>';
			$html .= '<select id="hubspot-list" class="bsf-cnlist-select" name="hubspot-list">';
			foreach($lists->lists as $offset => $list) {
				$html .= '<option value="'.$list->listId.'">'.$list->name.'</option>';
				$query .= $list->listId.'|'.$list->name.',';
				$hs_lists[$list->listId] = $list->name;
			}
			$html .= '</select>';
			$html .= '<input type="hidden" id="mailer-all-lists" value="'.$query.'"/>';
			$html .= '<input type="hidden" id="mailer-list-action" value="update_hubspot_list"/>';
			$html .= '<input type="hidden" id="mailer-list-api" value="'.$HAPIKey.'"/>';

			ob_start();
			?>
			<div class="bsf-cnlist-form-row">
				<div id='disconnect-hubspot' class='disconnect-mailer' data-mailerslug='Hubspot' data-mailer='hubspot'>
					<span>
						<?php echo _e( "Use different 'Hubspot' account?", "smile" ); ?>
					</span>
				</div>
				<span class='spinner' style='float: none;'></span>
			</div>
			<?php 
			$html .= ob_get_clean();
			update_option('hubspot_api',$HAPIKey);
			update_option('hubspot_lists',$hs_lists);	

			print_r(json_encode(array(
				'status' => "success",
				'message' => $html
			)));
			
			die();
		}
		
		
		function disconnect_hubspot(){
			delete_option( 'hubspot_api' );
			delete_option( 'hubspot_lists' );
			
			$smile_lists = get_option('smile_lists');			
			if( !empty( $smile_lists ) ){ 
				foreach( $smile_lists as $key => $list ) {
					$provider = $list['list-provider'];
					if( $provider == "hubspot" ){
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
	new Smile_Mailer_Hubspot;
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

	if( !function_exists( 'cpGetHSLists' ) ){
		function cpGetHSLists( $api_key) {

			$listsObj = new HubSpot_Lists($api_key);

			$lists = $listsObj->get_static_lists(null);

			$hs_lists = array();
			foreach($lists->lists as $offset => $list) {
				$hs_lists[$list->listId] = $list->name;
			}
			return $hs_lists;
		}
	}
	
}