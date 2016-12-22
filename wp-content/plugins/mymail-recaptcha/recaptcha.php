<?php
/*
Plugin Name: MyMail reCaptcha™ for Forms
Plugin URI: http://rxa.li/mymail
Description: Adds a reCaptcha™ to your MyMail subscription forms
Version: 0.6
Author: revaxarts.com
Author URI: http://revaxarts.com
License: GPLv2 or later
*/


class MyMailreCaptcha {

	private $plugin_path;
	private $plugin_url;

	public function __construct(){

		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_url = plugin_dir_url( __FILE__ );

		register_activation_hook( __FILE__, array(&$this, 'activate') );
		register_deactivation_hook( __FILE__, array(&$this, 'deactivate') );

		load_plugin_textdomain( 'mymail_recaptcha', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

		add_action( 'init', array( &$this, 'init') );
	}

	public function activate( $network_wide ){

		global $mymail_options;

		$options = array(
			'reCaptcha_new' => true,
			'reCaptcha_public' => '',
			'reCaptcha_private' => '',
			'reCaptcha_error_msg' => __('Please solve the captcha!', 'mymail_recaptcha'),
			'reCaptcha_loggedin' => false,
			'reCaptcha_forms' => array(),
			'reCaptcha_theme' => 'red',
			'reCaptcha_language' => 'en',
		);

		//merge options with MyMail options (don't override)
		$mymail_options = wp_parse_args($mymail_options, $options);

		update_option( 'mymail_options', $mymail_options );

	}

	public function deactivate( $network_wide ){

	}

	public function init(){
		if(is_admin()){

			add_filter( 'mymail_setting_sections', array( &$this, 'settings_tab' ));

			add_action( 'mymail_section_tab_reCaptcha', array( &$this, 'settings' ));

		}
		add_filter( 'mymail_form_fields', array( &$this, 'form_fields'), 10, 3 );
		add_filter( 'mymail_submit', array( &$this, 'check_captcha'), 10, 1 );
	}

	public function settings_tab($settings) {

		$position = 4;
		$settings = array_slice($settings, 0, $position, true) +
					array('reCaptcha' => 'reCaptcha™') +
					array_slice($settings, $position, NULL, true);

		return $settings;
	}

	public function settings(){
?>
	<table class="form-table">
		<tr valign="top">
			<th scope="row">&nbsp;</th>
			<td><p class="description"><?php echo sprintf(__('You have to %s to get your public and private keys', 'mymail_recaptcha'), '<a href="https://www.google.com/recaptcha/admin" class="external">'.__('sign up', 'mymail_recaptcha').'</a>'); ?></p></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('new reCaptcha' ,'mymail_recaptcha') ?></th>
			<td><label><input type="checkbox" name="mymail_options[reCaptcha_new]" value="1" <?php checked(mymail_option('reCaptcha_new')); ?>> <?php _e('use the new reCaptcha™', 'mymail_recaptcha') ?></label></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Public Key' ,'mymail_recaptcha') ?></th>
			<td><p><input type="text" name="mymail_options[reCaptcha_public]" value="<?php echo esc_attr(mymail_option('reCaptcha_public')) ?>" class="large-text"></p></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Private Key' ,'mymail_recaptcha') ?></th>
			<td><p><input type="text" name="mymail_options[reCaptcha_private]" value="<?php echo esc_attr(mymail_option('reCaptcha_private')) ?>" class="large-text"></p></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Error Message' ,'mymail_recaptcha') ?></th>
			<td><p><input type="text" name="mymail_options[reCaptcha_error_msg]" value="<?php echo esc_attr(mymail_option('reCaptcha_error_msg')) ?>" class="large-text"></p></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Disable for logged in users' ,'mymail_recaptcha'); ?></th>
			<td><label><input type="checkbox" name="mymail_options[reCaptcha_loggedin]" value="1" <?php checked(mymail_option('reCaptcha_loggedin')); ?>> <?php _e('disable the reCaptcha™ for logged in users', 'mymail_recaptcha') ?></label></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Forms' ,'mymail_recaptcha') ?><p class="description"><?php _e('select forms which require a captcha', 'mymail_recaptcha'); ?></p></th>
			<td>
				<ul>
				<?php $forms = mymail('form')->get_all();
					$enabled = mymail_option('reCaptcha_forms', array());
					foreach($forms as $form){
						$form = (object) $form;
						$id = isset($form->ID) ? $form->ID : $form->id;
						echo '<li><label><input name="mymail_options[reCaptcha_forms][]" type="checkbox" value="'.$id.'" '.(checked(in_array($id, $enabled), true, false)).'>'.$form->name.'</label></li>';
					}

				?>
				</ul>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Theme' ,'mymail_recaptcha') ?></th>
			<td><select name="mymail_options[reCaptcha_theme]">
				<?php $themes = array('red' => 'Default', 'white' => 'White', 'blackglass' => 'BlackGlass', 'clean' => 'Clean');
					$current = mymail_option('reCaptcha_theme');
					foreach($themes as $key => $name){
						echo '<option value="'.$key.'" '.(selected($key, $current, false)).'>'.$name.'</option>';
					}

				?>
			</select> <span class="description"> <?php echo sprintf(__('This defines the look of the reCaptcha™. There are %s available', 'mymail_recaptcha'), '<a href="https://developers.google.com/recaptcha/docs/customization#Standard_Themes" class="external">'.__('four themes', 'mymail_recaptcha').'</a>'); ?></span></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Language' ,'mymail_recaptcha') ?></th>
			<td><select name="mymail_options[reCaptcha_language]">
				<?php $languages = array('ar' => 'Arabic', 'bg' => 'Bulgarian', 'ca' => 'Catalan', 'zh-CN' => 'Chinese (Simplified)', 'zh-TW' => 'Chinese (Traditional)', 'hr' => 'Croatian', 'cs' => 'Czech', 'da' => 'Danish', 'nl' => 'Dutch', 'en-GB' => 'English (UK)', 'en' => 'English (US)', 'fil' => 'Filipino', 'fi' => 'Finnish', 'fr' => 'French', 'fr-CA' => 'French (Canadian)', 'de' => 'German', 'de-AT' => 'German (Austria)', 'de-CH' => 'German (Switzerland)', 'el' => 'Greek', 'iw' => 'Hebrew', 'hi' => 'Hindi', 'hu' => 'Hungarain', 'id' => 'Indonesian', 'it' => 'Italian', 'ja' => 'Japanese', 'ko' => 'Korean', 'lv' => 'Latvian', 'lt' => 'Lithuanian', 'no' => 'Norwegian', 'fa' => 'Persian', 'pl' => 'Polish', 'pt' => 'Portuguese', 'pt-BR' => 'Portuguese (Brazil)', 'pt-PT' => 'Portuguese (Portugal)', 'ro' => 'Romanian', 'ru' => 'Russian', 'sr' => 'Serbian', 'sk' => 'Slovak', 'sl' => 'Slovenian', 'es' => 'Spanish', 'es-419' => 'Spanish (Latin America)', 'sv' => 'Swedish', 'th' => 'Thai', 'tr' => 'Turkish', 'uk' => 'Uk rainian', 'vi' => 'Vietnamese');
					$current = mymail_option('reCaptcha_language');
					foreach($languages as $key => $name){
						echo '<option value="'.$key.'" '.(selected($key, $current, false)).'>'.$name.'</option>';
					}

				?>
			</select></td>
		</tr>
	</table>

<?php
	}

	public function form_fields($fields, $formid, $form){

		if(is_user_logged_in() && mymail_option('reCaptcha_loggedin')) return $fields;

		if(!in_array($formid, mymail_option('reCaptcha_forms', array()))) return $fields;

		$position = count($fields)-1;
		$fields = array_slice($fields, 0, $position, true) +
					array('_recaptcha' => $this->get_field($form)) +
					array_slice($fields, $position, NULL, true);

		return $fields;

	}

	public function get_field($html){

		if(mymail_option('reCaptcha_new')){

			wp_register_script('mymail_recaptcha_script', 'https://www.google.com/recaptcha/api.js?hl='.mymail_option('reCaptcha_language'));
			wp_enqueue_script('mymail_recaptcha_script');

			$html = '<div class="mymail-wrapper mymail-_recaptcha-wrapper"><div class="g-recaptcha" data-sitekey="'.mymail_option('reCaptcha_public').'"></div></div>';

		}else{

			require_once $this->plugin_path.'/recaptcha/recaptchalib.php';
			$publickey = mymail_option('reCaptcha_public');
			$html = "<script type='text/javascript'>if(!RecaptchaOptions) var RecaptchaOptions = {theme : '".mymail_option('reCaptcha_theme')."', lang : '".mymail_option('reCaptcha_language')."'}</script>";

			$html .= recaptcha_get_html($publickey, NULL, is_ssl());

		}

		return $html;

	}

	public function check_captcha($object){

		if(is_user_logged_in() && mymail_option('reCaptcha_loggedin')) return $object;

		$formid = (isset($_POST['formid'])) ? intval($_POST['formid']) : 0;

		if(!in_array($formid, mymail_option('reCaptcha_forms', array()))) return $object;

		if(isset($_POST['g-recaptcha-response'])){

			if(!empty($_POST['g-recaptcha-response'])){
				$url = add_query_arg(array(
					'secret' => mymail_option('reCaptcha_private'),
					'response' => $_POST['g-recaptcha-response'],
				), 'https://www.google.com/recaptcha/api/siteverify');

				$response = wp_remote_get($url);

				if(is_wp_error($response)){
					$object['errors']['_recaptcha'] = $response->get_error_message();
				}else{
					$response = json_decode(wp_remote_retrieve_body( $response ));
					if(!$response->success)
						$object['errors']['_recaptcha'] = mymail_option('reCaptcha_error_msg');
				}

			}else{
				$object['errors']['_recaptcha'] = mymail_option('reCaptcha_error_msg');
			}


		}else{
			require_once $this->plugin_path.'/recaptcha/recaptchalib.php';
			$privatekey = mymail_option('reCaptcha_private');
			$resp = recaptcha_check_answer ($privatekey,
				$_SERVER["REMOTE_ADDR"],
				$_POST["recaptcha_challenge_field"],
				$_POST["recaptcha_response_field"]
			);

			if (!$resp->is_valid) {
				$object['errors']['_recaptcha'] = mymail_option('reCaptcha_error_msg');
			}
		}

		return $object;

	}


}
new MyMailreCaptcha();
