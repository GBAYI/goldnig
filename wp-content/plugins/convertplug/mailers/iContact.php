<?php
if(!class_exists('Smile_Mailer_iContact')){
	class Smile_Mailer_iContact{
		function __construct(){

			require_once('api/icontact/iContactApi.php');
			add_action( 'wp_ajax_get_icontact_data', array($this,'get_icontact_data' ));
			add_action( 'wp_ajax_update_icontact_authentication', array($this,'update_icontact_authentication' ));
			add_action( 'wp_ajax_disconnect_icontact', array($this,'disconnect_icontact' ));
			add_action( 'wp_ajax_icontact_add_subscriber', array($this,'icontact_add_subscriber' ));
			add_action( 'wp_ajax_nopriv_icontact_add_subscriber', array($this,'icontact_add_subscriber' ));
	
		}

		/* 
		* retrieve mailer info
		* @Since 1.0
		*/
		function get_icontact_data(){
			
			$connected = false;
			ob_start();
			$ic_app_id = get_option('icontact_app_id');
			$ic_app_user = get_option('icontact_app_user');
			$ic_app_pass = get_option('icontact_app_pass');

			if( $ic_app_id != '' ) 
            	$formstyle = 'style="display:none;"'; 
            else
            	$formstyle = '';
            ?>
			
			<div class="bsf-cnlist-form-row" <?php echo $formstyle; ?>>
	            <label for="cp-list-name" ><?php _e( "iContact App ID", "smile" ); ?></label>
	            <input type="text" autocomplete="off" id="icontact_app_id" name="icontact-auth-key" value="<?php echo esc_attr( $ic_app_id ); ?>"/>
	        </div>

            <div class="bsf-cnlist-form-row" <?php echo $formstyle; ?>>
	            <label for="cp-email"><?php _e( "iContact App Username", "smile" ); ?></label>            
	            <input type="text" autocomplete="off" id="icontact_email" name="icontact-username" value="<?php echo esc_attr( $ic_app_user ); ?>"/>
	        </div>

	        <div class="bsf-cnlist-form-row" <?php echo $formstyle; ?>>
	            <label for="cp-password"><?php _e( "iContact App Password", "smile" ); ?></label>            
	            <input type="password" autocomplete="off" id="icontact_pass" name="icontact-password" value="<?php echo esc_attr( $ic_app_pass ); ?>"/>
	        </div>

	        <div class="bsf-cnlist-form-row">
	            <div class="icontact-list">
	            <?php
	            if($ic_app_id != '')
				 	$ic_lists = cpGetICLists($ic_app_id,$ic_app_user,$ic_app_pass);
				else
					$ic_lists = '';

				if( !$ic_lists ) $ic_lists = get_option('icontact_lists');
				if( !empty( $ic_lists ) ){
					$connected = true;
					$html = '<label for="icontact-list">'.__( "Select List", "smile" ).'</label>';
					$html .= '<select id="icontact-list" class="bsf-cnlist-select" name="icontact-list">';
					foreach($ic_lists as $id => $name) {
						$html .= '<option value="'.$id.'">'.$name.'</option>';
					}
					$html .= '</select>';
					echo $html;
				}
	            ?>
	            </div>
	        </div>

	        <div class="bsf-cnlist-form-row">
	            <?php if( $ic_app_id == "" ) { ?>
	            	<p><button id="auth-icontact" class="button button-secondary" disabled><?php _e( "Authenticate iContact", "smile" ); ?></button><span class="spinner" style="float: none;"></span></p>
	            <?php } else { ?>
	            	<p><div id="disconnect-icontact" class="disconnect-mailer" data-mailerslug="iContact" data-mailer="icontact"><span><?php _e( "Use different 'iContact' account?", "smile" ); ?></span></div><span class="spinner" style="float: none;"></span></p>
	            <?php } ?>
	        </div>

            <?php
            $content = ob_get_clean();       
            $result['data'] = $content; 
            $result['helplink'] = 'http://www.icontact.com/developerportal/documentation/register-your-app/';
            $result['isconnected'] = $connected;      
            echo json_encode($result);
            die();

		}
				
		function icontact_add_subscriber(){		
			$post = $_POST;
			$data = array();	
			$appID = get_option('icontact_app_id');
			$appPass = get_option('icontact_app_pass');
			$appUsername = get_option('icontact_app_user' );	
					 
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
				// Give the API your information
				iContactApi::getInstance()->setConfig(array(
					'appId'       => $appID, 
					'apiPassword' => $appPass, 
					'apiUsername' => $appUsername
				));

				$oiContact = iContactApi::getInstance();

				try {

					$result = $oiContact->addContact($email, null, null, $name, '');
					$contact = $result->contactId;			
					$result = $oiContact->subscribeContactToList($contact, $list, 'normal');
				} catch (Exception $oException) {

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
			
			print_r(json_encode(array(
				'action' => $action,
				'email_status' => $email_status,
				'status' => $status,
				'message' => $msg,
				'url' => $url,
			)));
			die();
		}

		function update_icontact_authentication(){

			$appID = $_POST['appID'];
			$appUsername = $_POST['appUser'];
			$appPass = $_POST['appPass'];

			if($appID == '' || $appUsername === '' || $appPass === '') {
				print_r(json_encode(array(
					'status' => "error",
					'message' => __( "Please enter all credentails from above fields", "smile" )
				)));
				die();
			}
			
			// Give the API your information
			iContactApi::getInstance()->setConfig(array(
				'appId'       => $appID, 
				'apiPassword' => $appPass, 
				'apiUsername' => $appUsername
			));

			$oiContact = iContactApi::getInstance();

			try {
				// try to get all lists
				$lists = $oiContact->getLists();

			} catch (Exception $oException) { // Catch any exceptions
				// Dump errors

				$errors = $oiContact->getErrors();
				print_r(json_encode(array(
					'status' => "error",
					'message' => $errors[0]
				)));

				die();
				
			}

			$ic_lists = array();
			$html = $query = '';
			$html .= '<label for="icontact-list"  >Select List</label>';
			$html .= '<select id="icontact-list" class="bsf-cnlist-select" name="icontact-list">';
			foreach($lists as $offset => $list) {
				$html .= '<option value="'.$list->listId.'">'.$list->name.'</option>';
				$query .= $list->listId.'|'.$list->name.',';
				$ic_lists[$list->listId] = $list->name;
			}
			$html .= '</select>';
			$html .= '<input type="hidden" id="mailer-all-lists" value="'.$query.'"/>';
			$html .= '<input type="hidden" id="mailer-list-action" value="update_icontact_list"/>';
			$html .= '<input type="hidden" id="mailer-list-api" value="'.$appID.'"/>';

			ob_start();
			?>
			<div class="bsf-cnlist-form-row">
				<div id="disconnect-icontact" class="disconnect-mailer" data-mailerslug="iContact" data-mailer="icontact">
					<span>
						<?php _e( "Use different 'iContact' account?", "smile" ); ?>
					</span>
				</div>
				<span class="spinner" style="float: none;"></span>
			</div>
			<?php 
			$html .= ob_get_clean();

			update_option('icontact_app_id',$appID);
			update_option('icontact_app_user',$appUsername);
			update_option('icontact_app_pass',$appPass);
			update_option('icontact_lists', $ic_lists );		
			
			print_r(json_encode(array(
				'status' => "success",
				'message' => $html
			)));
			
			die();
		}
	
		
		function disconnect_icontact(){
			delete_option( 'icontact_app_id' );
			delete_option( 'icontact_app_user' );
			delete_option( 'icontact_app_pass' );
			delete_option( 'icontact_lists' );
			
			$smile_lists = get_option('smile_lists');			
			if( !empty( $smile_lists ) ){ 
				foreach( $smile_lists as $key => $list ) {
					$provider = $list['list-provider'];
					if( $provider == "iContact" ){
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
	new Smile_Mailer_iContact;
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

	if( !function_exists( 'cpGetICLists' ) ){
		function cpGetICLists( $ic_app_id,$ic_app_user,$ic_app_pass ) {
			
			// Give the API your information
			iContactApi::getInstance()->setConfig(array(
				'appId'       => $ic_app_id, 
				'apiPassword' => $ic_app_pass, 
				'apiUsername' => $ic_app_user
			));

			$oiContact = iContactApi::getInstance();

			try {
				// try to get all lists
				$lists = $oiContact->getLists();

			} catch (Exception $oException) { // Catch any exceptions
				// Dump errors
				return false;
				exit();
			}

			$ic_lists = array();
			foreach($lists as $offset => $list) {
				$ic_lists[$list->listId] = $list->name;
			}
			return $ic_lists;
		}
	}
	
}