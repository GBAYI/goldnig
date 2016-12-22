<?php
if(!class_exists('Smile_Mailer_Aweber')){
	class Smile_Mailer_Aweber{
		function __construct(){

			add_action( 'wp_ajax_get_aweber_data', array($this,'get_aweber_data' ));
			add_action( 'wp_ajax_update_aweber_authentication', array($this,'update_aweber_authentication' ));
			add_action( 'wp_ajax_aweber_add_subscriber', array($this,'aweber_add_subscriber' ));
			add_action( 'wp_ajax_nopriv_aweber_add_subscriber', array($this,'aweber_add_subscriber' ));
			add_action( 'wp_ajax_disconnect_aweber', array( $this, 'disconnect_aweber' ) );
	
		}

		// retrieve mailer info data
		function get_aweber_data(){
			
			$connected = false;
			ob_start();
			$appID = 'f6c84f48';
			$credentials = get_option('aweber_credentials');
			# prompt user to go to authorization URL

			?>
            	<div class="aweber-auth" style="display:<?php echo ( $credentials ) ? 'none' : 'block'; ?>">
	                <div class="bsf-cnlist-form-row">
	                	<button class="button button-secondary auth-aweber" onclick="window.open('https://auth.aweber.com/1.0/oauth/authorize_app/<?php echo $appID;?>','name','width=800,height=480')" ><?php _e( "Authenticate Aweber", "smile" ); ?></button>
	                </div>
	                <div class="bsf-cnlist-form-row">
		                <label for="authentication_token"><?php _e( "Enter the authorization  code:", "smile" ); ?></label>
		                <input type="text" autocomplete="off" id="authentication_token"/>
		            </div>
                </div>
                
                <div class="bsf-cnlist-form-row aweber-list">
	                <?php
	                if ( $credentials !== '' )
	                	$aweber_lists = cpGetAweberLists();

	                if( !$aweber_lists ) $aweber_lists = get_option('aweber_lists');
	                if( !empty( $aweber_lists ) ){
	                	$connected = true;
	                    $html = '<label for="aweber-list">'.__( "Select List", "smile" ).'</label>';
	                    $html .= '<select id="aweber-list" class="bsf-cnlist-select" name="aweber-list">';
	                    foreach($aweber_lists as $id => $name) {
	                        $html .= '<option value="'.$id.'">'.$name.'</option>';
	                    }
	                    $html .= '</select>';
	                    echo $html;
	                }
	                ?>
                </div>

                <div class="bsf-cnlist-form-row">
					<?php if( $credentials ) { ?>
						<div id="disconnect-aweber" class="disconnect-mailer" data-mailerslug="Aweber" data-mailer="aweber"><span><?php _e( "Use different 'Aweber' account?", "smile" ); ?></span></div>
					    <span class="spinner" style="float: none;"></span>
					<?php } else { ?>
						<button class="button button-secondary get_aweber_data" disabled><?php _e( "Connect to Aweber", "smile" ); ?></button>
					    <span class="spinner" style="float: none;"></span>
					<?php } ?>
				</div>

			<?php
            $content = ob_get_clean();
            
            $result['data'] = $content;
            $result['helplink'] = 'https://help.aweber.com/hc/en-us/articles/204031226-How-Do-I-Authorize-an-App';
            $result['isconnected'] = $connected;
            echo json_encode($result);
            die();

		}
				
		function aweber_add_subscriber(){
			$data = $_POST;
			require_once(AWEBER_API_URI.'aweber_api.php');
			$credentials = get_option('aweber_credentials');
			
			$consumerKey    = $credentials[0]; # put your credentials here
			$consumerSecret = $credentials[1]; # put your credentials here
			$accessKey      = $credentials[2]; # put your credentials here
			$accessSecret   = $credentials[3]; # put your credentials here
			$list_id        = $data['list_id']; # put the List ID here
			
			$aweber = new AWeberAPI($consumerKey, $consumerSecret);
			$account = $aweber->getAccount($accessKey, $accessSecret);
			$listURL = $account->url."/lists/{$list_id}";
			$on_success = isset( $post['message'] ) ? 'message' : 'redirect';
			$msg_wrong_email = ( isset( $post['msg_wrong_email']  )  && $post['msg_wrong_email'] !== '' ) ? $post['msg_wrong_email'] : __( 'Please enter correct email address.', 'smile' );
			$msg = isset( $data['message'] ) ? $data['message'] : __( 'Thanks for subscribing. Please check your mail and confirm the subscription.', 'smile' );	

			if($on_success == 'message'){
				$action	= 'message';
				$url	= 'none';
			} else {
				$action	= 'redirect';
				$url	= $data['redirect'];
			}

			$contact = array();
			$contact['name'] = isset( $data['name'] ) ? $data['name'] : '';
			$contact['email'] = $data['email'];
			$contact['date'] = date("j-n-Y");

			//	Check Email in MX records
			$email_status = apply_filters('cp_valid_mx_email', $data['email'] );
			if($email_status) {

				$status = 'success';
				try {

					$list = $account->loadFromUrl($listURL);
				
					# create a subscriber
					$name = isset( $data['name'] ) ? $data['name'] : '';
					$params = array(
						'email' => $data['email'],
						'name' => $name,
					);
					
					$subscribers = $list->subscribers;
					$new_subscriber = $subscribers->create($params);				
					
				} catch(AWeberAPIException $exc) {
					
					print_r(json_encode(array(
						'action' => $action,
						'email_status' => $email_status,
						'status' => 'error',
						'message' => __( "Something went wrong. Please try again.", "smile"),
						'url' => $url,
					)));
					die();
				}

				$style_id = $data['style_id'];
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

		function update_aweber_authentication(){
			$data = $_POST;

			require_once(AWEBER_API_URI.'aweber_api.php');
			// Replace with the keys of your application
			// NEVER SHARE OR DISTRIBUTE YOUR APPLICATIONS'S KEYS!
			$consumerKey    = "AkyOnVQuJi9x5qKleb3JRgAV";
			$consumerSecret = "djL9NIUkfau3rteOhg4grrwRBfqx1lYzGQxVIyCb";
			
			$code = $data['authentication_token'];
					
			try{
				$this->application = new AWeberAPI($consumerKey, $consumerSecret);
				$credentials = AWeberAPI::getDataFromAweberID($code);
				
				$account = $this->application->getAccount($credentials[2], $credentials[3]);
							
				$html = $query = '';
				$aweber_lists = array();
				$html .= '<label for="aweber-list">'.__( "Select List", "smile" ).'</label>';
				$html .= '<select id="aweber-list" class="bsf-cnlist-select" name="aweber-list">';
				foreach($account->lists as $offset => $list) {
					$html .= '<option value="'.$list->id.'">'.$list->name.'</option>';
					$query .= $list->id.'|'.$list->name.',';
					$aweber_lists[$list->id] = $list->name;
				}
				$html .= '</select>';
				$html .= '<input type="hidden" id="mailer-all-lists" value="'.$query.'"/>';
				$html .= '<input type="hidden" id="mailer-list-action" value="update_aweber_list"/>';

				ob_start();
				?>
				<div class="bsf-cnlist-form-row">
					<div id="disconnect-aweber" class="disconnect-mailer" data-mailerslug="Aweber" data-mailer="aweber">
						<span>
							<?php _e( "Use different 'Aweber' account?", "smile" ); ?></span>
					</div>
					<span class="spinner" style="float: none;"></span>
				</div>
				<?php 
				$html .= ob_get_clean();			

				update_option('aweber_credentials',$credentials);
				update_option('aweber_lists',$aweber_lists);
					
				print_r(json_encode(array(
					'status' => "success",
					'message' => $html
				)));
				
			} catch(AWeberAPIException $exc) {
				print_r(json_encode(array(
						'status'  => "error",
						'message' => __( "Please provide valid authorization code for connecting to Aweber.", "smile" ) 
				)));
			}
			die();
		}
	
		
		function disconnect_aweber(){
			delete_option( 'aweber_credentials' );
			delete_option( 'aweber_lists' );
			
			$smile_lists = get_option('smile_lists');			
			if( !empty( $smile_lists ) ){ 
				foreach( $smile_lists as $key => $list ) {
					$provider = $list['list-provider'];
					if( $provider == "Aweber" ){
						$smile_lists[$key]['list-provider'] = "Convert Plug";
					}
				}
				update_option( 'smile_lists', $smile_lists );
			}
			
			print_r(json_encode(array(
                'message' => 'disconnected'
			)));
			die();
		}
	}
	new Smile_Mailer_Aweber;
	if(!function_exists('searchForMailer')){
		function searchForMailer($id, $array) {
			if(is_array($array) && !empty($array)){
				foreach ($array as $key => $val) {
					if ($val['list-provider'] === $id) {
						return $key;
					}
				}
			} else {
				return false;
			}
		}
	}
	
	// function to retrieve latest lists from aweber
	if( !function_exists( "cpGetAweberLists" ) ){
		function cpGetAweberLists(){

			require_once(AWEBER_API_URI.'aweber_api.php');
			$consumerKey    = "AkyOnVQuJi9x5qKleb3JRgAV";
			$consumerSecret = "djL9NIUkfau3rteOhg4grrwRBfqx1lYzGQxVIyCb";

			$application = new AWeberAPI($consumerKey, $consumerSecret);
						
			$credentials = get_option('aweber_credentials');
			
			try{
				$account = $application->getAccount($credentials[2], $credentials[3]);
				$html = $query = '';
				$aweber_lists = array();
				foreach($account->lists as $offset => $list) {
					$aweber_lists[$list->id] = $list->name;
				}
				
				return $aweber_lists;
			} catch(AWeberAPIException $exc) {
				return false;
			}
						
		}
	}
}