<?php
if(!class_exists('Smile_Mailer_Emma')){
	class Smile_Mailer_Emma{
		function __construct(){

			require_once('api/emma/Emma.php');
			add_action( 'wp_ajax_get_emma_data', array($this,'get_emma_data' ));
			add_action( 'wp_ajax_update_emma_authentication', array($this,'update_emma_authentication' ));
			add_action( 'wp_ajax_disconnect_emma', array($this,'disconnect_emma' ));
			add_action( 'wp_ajax_emma_add_subscriber', array($this,'emma_add_subscriber' ));
			add_action( 'wp_ajax_nopriv_emma_add_subscriber', array($this,'emma_add_subscriber' ));
	
		}

		// retrieve mailer info data
		function get_emma_data(){
			
			$connected = false;
			ob_start();
			$emma_pub_api = get_option('emma_public_key');
			$emma_priv_api = get_option('emma_priv_key');
			$emma_acc_id = get_option('emma_acc_id');

			if( $emma_pub_api != '' ) 
            	$formstyle = 'style="display:none;"'; 
            else
            	$formstyle = '';
            ?>
			
			<div class="bsf-cnlist-form-row" <?php echo $formstyle; ?>>
	            <label for="cp-list-name" ><?php _e( "Public API key", "smile" ); ?></label>
	            <input type="text" autocomplete="off" id="emm_pub_api" name="emma-pub-key" value="<?php echo esc_attr( $emma_pub_api ); ?>"/>
	        </div>

	        <div class="bsf-cnlist-form-row" <?php echo $formstyle; ?>>
	            <label for="cp-email"><?php _e( "Private API key", "smile" ); ?></label>            
	            <input type="text" autocomplete="off" id="emm_priv_api" name="emma-priv-key" value="<?php echo esc_attr( $emma_priv_api ); ?>"/>
	        </div>

	        <div class="bsf-cnlist-form-row" <?php echo $formstyle; ?>>
	            <label for="cp-password"><?php _e( "Account ID", "smile" ); ?></label>            
	            <input type="text" autocomplete="off" id="emm_acc_id" name="emma-acc-id" value="<?php echo esc_attr( $emma_acc_id ); ?>"/>
	        </div>

            <div class="bsf-cnlist-form-row emma-list">
	            <?php
	            if($emma_pub_api != '')
				 	$emma_lists = cpGetEMMALists($emma_acc_id,$emma_pub_api,$emma_priv_api);
				else
					$emma_lists = '';

				if( !$emma_lists ) $emma_lists = get_option('emma_lists');
				if( !empty( $emma_lists ) ){
					$connected = true;
					$html = '<label for="emma-list">'.__( "Select List", "smile" ).'</label>';
					$html .= '<select id="emma-list" class="bsf-cnlist-select" name="emma-list">';
					foreach($emma_lists as $id => $name) {
						$html .= '<option value="'.$id.'">'.$name.'</option>';
					}
					$html .= '</select>';
					echo $html;
				}
	            ?>
            </div>

            <div class="bsf-cnlist-form-row">
	            <?php if( $emma_pub_api == "" ) { ?>
	            	<button id="auth-emma" class="button button-secondary" disabled><?php _e( "Authenticate MyEmma", "smile" ); ?></button><span class="spinner" style="float: none;"></span>
	            <?php } else { ?>
	            	<div id="disconnect-emma" class="disconnect-mailer" data-mailerslug="Emma" data-mailer="emma"><span><?php _e( "Use different 'MyEmma' account?", "smile" ); ?></span></div><span class="spinner" style="float: none;"></span>
	            <?php } ?>
	        </div>

            <?php
            $content = ob_get_clean();
            $result['data'] = $content;
            $result['helplink'] = 'http://api.myemma.com/_images/api_key.png';
            $result['isconnected'] = $connected;
            echo json_encode($result);
            die();

		}
				
		function emma_add_subscriber(){		
			$post = $_POST;
			$data = array();	

			$public_key = get_option('emma_public_key');
			$private_key = get_option('emma_priv_key');
			$account_id = get_option('emma_acc_id' );	
					 
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

			//	Check Email in MX records
			$email_status = apply_filters('cp_valid_mx_email', $_POST['email'] );
			if($email_status) {

				$status = 'success';
				try { 

					// Give the API your information
					$member = array();
					$member['email'] = $email;
					$member['fields'] = array('first_name' => $name, 'last_name' => '');
					$member['status_to'] = 'a';
					
					$emma = new Emma($account_id, $public_key, $private_key);

					// Add member to contacts
					$res = $emma->membersAddSingle($member);

					$memberData = json_decode($res);
					$memberID = $memberData->member_id;

					// Add Member to group
					$group = array('group_ids' => array($list));

					$emma->membersGroupsAdd($memberID,$group);

				} catch(Emma_Invalid_Response_Exception $e) {

					print_r(json_encode(array(
						'action' => $action,
						'email_status' => $email_status,
						'status' => 'error',
						'message' => __( "Something went wrong. Please try again.", "smile" ),
						'url' => $url,
					)));
					die();
				}	

				$contact = array();
				$contact['name'] = $name;
				$contact['email'] = $email;
				$contact['date'] = date("j-n-Y");			
		
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

		function update_emma_authentication(){

			$public_key = $_POST['public_key'];
			$private_key = $_POST['priv_key'];
			$account_id = $_POST['accID'];

			if($public_key == '' || $private_key === '' || $account_id === '') {
				print_r(json_encode(array(
					'status'  => "error",
					'message' => __( "Please enter all credentails from above fields", "smile" )
				)));
				die();
			}
			
			// Give the API your information
			$emma = new Emma($account_id, $public_key, $private_key);

			// Returns an array of all members
			try {

				$result = $emma->myGroups();	

			} catch( Emma_Invalid_Response_Exception $e ) {

				print_r(json_encode(array(
					'status'  => "error",
					'message' => __( "Invalid credentials .", "smile" )
				)));
				die();
			}	

			$lists = json_decode($result);	
			$emma_lists = array();
			$html = $query = '';
			$html .= '<label for="emma-list">Select List</label>';
			$html .= '<select id="emma-list" class="bsf-cnlist-select" name="emma-list">';
			foreach($lists as $offset => $list) {
				$html .= '<option value="'.$list->member_group_id.'">'.$list->group_name.'</option>';
				$query .= $list->member_group_id.'|'.$list->group_name.',';
				$emma_lists[$list->member_group_id] = $list->group_name;
			}
			$html .= '</select>';
			$html .= '<input type="hidden" id="mailer-all-lists" value="'.$query.'"/>';
			$html .= '<input type="hidden" id="mailer-list-action" value="update_emma_list"/>';
			$html .= '<input type="hidden" id="mailer-list-api" value="'.$public_key.'"/>';

			ob_start();
			?>
			<div class="bsf-cnlist-form-row">
				<div id="disconnect-emma" class="disconnect-mailer" data-mailerslug="Emma" data-mailer="emma">
					<span>
						<?php _e( "Use different 'MyEmma' account?", "smile" ); ?>
					</span>
				</div>
				<span class="spinner" style="float: none;"></span>
			</div>
			<?php 
			$html .= ob_get_clean();

			update_option('emma_public_key',$public_key);
			update_option('emma_priv_key',$private_key);
			update_option('emma_acc_id',$account_id);
			update_option('emma_lists', $emma_lists );		
			
			print_r(json_encode(array(
				'status' => "success",
				'message' => $html
			)));
			
			die();
		}
		
		
		function disconnect_emma(){
			delete_option( 'emma_public_key' );
			delete_option( 'emma_priv_key' );
			delete_option( 'emma_acc_id' );
			delete_option( 'emma_lists' );
			
			$smile_lists = get_option('smile_lists');			
			if( !empty( $smile_lists ) ){ 
				foreach( $smile_lists as $key => $list ) {
					$provider = $list['list-provider'];
					if( $provider == "emma" ){
						$smile_lists[$key]['list-provider'] = "Convert Plug";
					}
				}
				update_option( 'smile_lists', $smile_lists );
			}
			
			print_r(json_encode(array(
                'message' => "disconnected"
			)));
			die();
		}
	}
	new Smile_Mailer_Emma;
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

	if( !function_exists( 'cpGetEMMALists' ) ){
		function cpGetEMMALists( $account_id,$public_key,$private_key ) {
			
			if($account_id == '' || $public_key === '' || $private_key === '') {
				return false;
				exit();
			}
			
			// Give the API your information
			$emma = new Emma($account_id, $public_key, $private_key);
			
			if(!is_object($emma)) {
				return false;
				exit();
			}

			// Returns an array of all members
			$result = $emma->myGroups();	

			$lists = json_decode($result);	

			$emma_lists = array();
			foreach($lists as $offset => $list) {
				$emma_lists[$list->member_group_id] = $list->group_name;
			}
			return $emma_lists;
		}
	}
	
}