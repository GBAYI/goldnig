<?php
if(!class_exists('Smile_Mailer_MadMimi')){
	class Smile_Mailer_MadMimi{
		function __construct(){

			require_once('api/madmimi/MadMimi.class.php');
			add_action( 'wp_ajax_get_madmimi_data', array($this,'get_madmimi_data' ));
			add_action( 'wp_ajax_update_madmimi_authentication', array($this,'update_madmimi_authentication' ));
			add_action( 'wp_ajax_disconnect_madmimi', array($this,'disconnect_madmimi' ));
			add_action( 'wp_ajax_madmimi_add_subscriber', array($this,'madmimi_add_subscriber' ));
			add_action( 'wp_ajax_nopriv_madmimi_add_subscriber', array($this,'madmimi_add_subscriber' ));
		}
		
		/* 
		* retrieve mailer info data
		* @Since 1.0
		*/
		function get_madmimi_data(){
			
			$connected = false;
			ob_start();
			$mm_api = get_option('madmimi_api');
			$mm_email = get_option('madmimi_email');
			if( $mm_api != '' ) 
            	$formstyle = 'style="display:none;"'; 
            else
            	$formstyle = '';
            ?>
            <div class="bsf-cnlist-form-row" <?php echo $formstyle; ?>>
				<label for="cp-email"><?php _e( "Email OR Username", "smile" ); ?></label>            
	            <input type="text" autocomplete="off" id="madmimi_email" name="madmimi-username" value="<?php echo esc_attr( $mm_email ); ?>"/>
	        </div>

            <div class="bsf-cnlist-form-row" <?php echo $formstyle; ?>>
	            <label for="cp-list-name" ><?php _e( "Mad Mimi API Key", "smile" ); ?></label>
	            <input type="text" autocomplete="off" id="madmimi_api_key" name="madmimi-auth-key" value="<?php echo esc_attr( $mm_api ); ?>"/>
	        </div>

            <div class="bsf-cnlist-form-row madmimi-list">
            <?php
            if($mm_api != '')
				$mm_lists = cpGetMMLists($mm_email,$mm_api);
			else
				$mm_lists = '';

			if( !$mm_lists ) $mm_lists = get_option('madmimi_lists');
			if( !empty( $mm_lists ) ){
				$connected = true;
				$html = '<label for="madmimi-list">'.__( "Select List", "smile" ).'</label>';
				$html .= '<select id="madmimi-list" class="bsf-cnlist-select" name="madmimi-list">';
				foreach($mm_lists as $id => $name) {
					$html .= '<option value="'.$id.'">'.$name.'</option>';
				}
				$html .= '</select>';
				echo $html;
			}
            ?>
            </div>

            <div class="bsf-cnlist-form-row">
	            <?php if( $mm_api == "" ) { ?>
	            	<button id="auth-madmimi" class="button button-secondary" disabled><?php _e( "Authenticate Mad Mimi", "smile" ); ?></button><span class="spinner" style="float: none;"></span>
	            <?php } else { ?>
	            	<div id="disconnect-madmimi" class="disconnect-mailer" data-mailerslug="Mad Mimi" data-mailer="madmimi"><span><?php _e( "Use different 'Mad Mimi' account?", "smile" ); ?></span></div><span class="spinner" style="float: none;"></span>
	            <?php } ?>
	        </div>

            <?php
            $content = ob_get_clean();
            $result['data'] = $content;
            $result['helplink'] = 'http://help.madmimi.com/where-can-i-find-my-api-key/';
            $result['isconnected'] = $connected;
            echo json_encode($result);
            die();
        }
				

		/* 
		* Add subscriber to list 
		* @Since 1.0
		*/
		function madmimi_add_subscriber(){		
			$post = $_POST;
			$data = array();
			$mailer_lists = get_option('smile_lists');			
			$key = searchForMailer('Madmimi',$mailer_lists);			
			$mailer = $mailer_lists[$key];
			
			$this->api_key = get_option('madmimi_api');			 
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
				$madmimiUser = get_option('madmimi_email');
				$mimi = new MadMimi($madmimiUser, $this->api_key);				
				$addData = array(
						'first_name' => $name
				);

				$result = $mimi->AddMembership($list,$email,$addData);	
			
				if( $result == 'Member could not be added to your audience' ) {
					
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
		function update_madmimi_authentication(){
			$post = $_POST;
			$email = $_POST['email'];
			$data = array();
			$this->api_key = $post['authentication_token'];

			$mimi = new MadMimi($email, $this->api_key);

			if( $mimi->Promotions() === 'Unable to authenticate' ) {
				print_r(json_encode(array(
					'status' => "error",
					'message' => __( "Unable to authenticate. Please check Username and API key", "smile" )
				)));
				die();
			}				

			$listsEncoded = $mimi->Lists();
			
			$lists = json_decode( $listsEncoded );

			if( empty($lists) ) {
				print_r(json_encode(array(
					'status' => "error",
					'message' => __( "You have zero lists in your Mad Mimi account. You must have at least one list before integration." , "smile" )
				)));
				die();
			}

			$mm_lists = array();
			$html = $query = '';
			$html .= '<label for="madmimi-list" >'.__( "Select List", "smile" ).'</label>';
			$html .= '<select id="madmimi-list" class="bsf-cnlist-select" name="madmimi-list">';
			foreach($lists as $offset => $list) {
				$html .= '<option value="'.$list->id.'">'.$list->name.'</option>';
				$query .= $list->id.'|'.$list->name.',';
				$mm_lists[$list->id] = $list->name;
			}
			$html .= '</select>';
			$html .= '<input type="hidden" id="mailer-all-lists" value="'.$query.'"/>';
			$html .= '<input type="hidden" id="mailer-list-action" value="update_madmimi_list"/>';
			$html .= '<input type="hidden" id="mailer-list-api" value="'.$this->api_key.'"/>';

			ob_start();
			?>
			<div class="bsf-cnlist-form-row">
				<div id="disconnect-madmimi" class="disconnect-mailer" data-mailerslug="Mad Mimi" data-mailer="madmimi">
					<span>
						<?php _e( "Use different 'Mad Mini' account?", "smile" ); ?>
					</span>
				</div>
				<span class="spinner" style="float: none;"></span>
			</div>
			<?php 
			$html .= ob_get_clean();
			update_option('madmimi_api',$this->api_key);
			update_option('madmimi_email',$email);
			update_option('madmimi_lists',$mm_lists);		
			
			print_r(json_encode(array(
				'status' => "success",
				'message' => $html
			)));
			
			die();
		}
		
		/* 
		* Disconnect Mailer
		* @Since 1.0
		*/
		function disconnect_madmimi(){
			delete_option( 'madmimi_api' );
			delete_option( 'madmimi_email' );
			delete_option( 'madmimi_lists' );
			
			$smile_lists = get_option('smile_lists');			
			if( !empty( $smile_lists ) ){ 
				foreach( $smile_lists as $key => $list ) {
					$provider = $list['list-provider'];
					if( $provider == "Madmimi" ){
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
	new Smile_Mailer_MadMimi;
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
	

	if( !function_exists( 'cpGetMMLists' ) ){

		/* 
		* Get lists from Mad Mimi
		* @Since 1.0
		*/
		function cpGetMMLists( $email,$api_key ) {
			
			$mimi = new MadMimi($email, $api_key);
			$listsEncoded = $mimi->Lists();
			
			$lists = json_decode( $listsEncoded );

			if( $lists === NULL ) {
				return false;
				exit;
			}

			$mm_lists = array();
			foreach($lists as $offset => $list) {
				$mm_lists[$list->id] = $list->name;
			}
			return $mm_lists;
		}
	}
	
}