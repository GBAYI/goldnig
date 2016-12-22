<?php
/*
Plugin Name: Contact Form 7 for MyMail
Plugin URI: http://rxa.li/mymail
Description: Create your Signup Forms with Contact Form 7 and allow users to signup to your newsletter
Version: 0.1.2
Author: revaxarts.com
Author URI: https://revaxarts.com
License: GPLv2 or later
*/

define('MYMAIL_CF7_VERSION', '0.1.2');
define('MYMAIL_CF7_REQUIRED_VERSION', '2.0');
define('MYMAIL_CF7_DOMAIN', 'mymail-cf7');

class CF7MyMail {

	private $plugin_path;
	private $plugin_url;

	public function __construct(){

		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_url = plugin_dir_url( __FILE__ );

		//register_activation_hook( __FILE__, array(&$this, 'activate') );
		//register_deactivation_hook( __FILE__, array(&$this, 'deactivate') );

		add_action( 'init', array( &$this, 'init') );
	}

	public function activate( $network_wide ){}

	public function deactivate( $network_wide ){}

	public function init(){

		add_action('wpcf7_validate', array( $this, 'validate'), 10, 2);
		add_filter('wpcf7_editor_panels', array( $this, 'panel'));
		add_filter('wpcf7_contact_form_properties', array( $this, 'form_properties'), 10, 2);
		add_action('wpcf7_save_contact_form', array( $this, 'save'));
		add_action('wpcf7_skip_mail', array( $this, 'skip_mail'), 10 , 2);

	}

	public function validate( $result, $tags ){

		if(!$result->is_valid()) return $result;

		if(!function_exists('mymail')) return $result;

		$submission = WPCF7_Submission::get_instance();

		if ( ! $submission || ! $posted_data = $submission->get_posted_data() )	return $result;

		$form = WPCF7_ContactForm::get_current();

		$properties = $form->get_properties();

		//no MyMail settings
		if(!isset($properties['mymail'])) return $result;
		$properties = $properties['mymail'];

		//not enabled
		if(!isset($properties['enabled'])) return $result;

		//checkbox defined but not checked
		if(isset($properties['checkbox']) && $properties['checkbox'] && empty($posted_data[$properties['checkboxfield']][0])) return $result;

		$userdata = array();

		foreach ($properties['fields'] as $field => $tag) {
			$userdata[$field] = is_array($posted_data[$tag]) ? $posted_data[$tag][0] : $posted_data[$tag];
		}

		$userdata['status'] = $properties['doubleoptin'] ? 0 : 1;

		$list_ids = isset($properties['lists']) ? $properties['lists'] : NULL;
		$overwrite = isset($properties['overwrite']);

		//add subscriber
		$subscriber_id = mymail('subscribers')->add($userdata, $overwrite);

		//no error
		if(!is_wp_error($subscriber_id)){

			if($list_ids) mymail('subscribers')->assign_lists($subscriber_id, $list_ids);

		//something went wrong
		}else{

			if($subscriber_id->get_error_code() == 'email_exists'){
				$message = __('You are already registered', MYMAIL_CF7_DOMAIN);
			}else{
				$message = __('There was a problem submitting the form', MYMAIL_CF7_DOMAIN);
			}

			$result->invalidate($tags[count($tags)-2], $message);
		}

		return $result;

	}

	public function save( $contact_form ){

		$properties['mymail'] = $_POST['mymail'];

		$properties['mymail']['fields'] = array_combine($properties['mymail']['fields'], $properties['mymail']['tags']);

		if(isset($properties['mymail']['fields'][-1])) unset($properties['mymail']['fields'][-1]);
		unset($properties['mymail']['tags']);

		$contact_form->set_properties( $properties );

	}

	public function form_properties( $properties, $form ){

		$properties['mymail'] = isset($properties['mymail']) ? $properties['mymail'] : array() ;

		return $properties;
	}

	public function skip_mail( $skip_mail, $contact_form ){

		$properties = $contact_form->get_properties();

		if(!isset($properties['mymail'])) return $skip_mail;
		$properties = $properties['mymail'];

		return isset($properties['skip_mail']) ? true : $skip_mail;

	}

	public function panel( $panels ){

		$panels['mymail'] = array(
			'title' => 'MyMail',
			'callback' => array( $this, 'editor_panel'),
		);

		return $panels;

	}


	public function editor_panel( $post ){

		//check if MyMail is enabled
		if(!function_exists('mymail')){

			$all_plugins = get_plugins();

			if(isset($all_plugins['myMail/myMail.php'])){

				echo '<div class="error inline"><p>Please enable the <a href="plugins.php#mymail-email-newsletter-plugin-for-wordpress">MyMail Newsletter Plugin</a> to get access to this tab</p></div>';

			}else{

				echo '<div class="error inline"><p>You need the <a href="http://rxa.li/mymail?utm_source=Contact+Form+7+for+MyMail+Newsletter">MyMail Newsletter Plugin for WordPress</a> to use your Form with MyMail</p></div>';

			}

			return;
		}

		$s = wp_parse_args($post->prop( 'mymail' ), array(
			'enabled' => 0,
			'checkbox' => 0,
			'doubleoptin' => 1,
			'overwrite' => 0,
			'skip_mail' => 0,
			'checkboxfield' => 'your-checkbox',
			'lists' => array(),
			'fields' => array(
				'email' => 'your-email',
				'firstname' => 'your-name',
			),
		));

		if(empty($s['fields']))
			$s['fields'] = array(
				'email' => 'your-email',
			);

		wp_enqueue_script( 'cf7-mymail', $this->plugin_url. '/assets/js/script.js', array('jquery') , MYMAIL_CF7_VERSION, true );
		wp_enqueue_style( 'cf7-mymail', $this->plugin_url. '/assets/css/style.css', array() , MYMAIL_CF7_VERSION );

		$tags = $post->form_scan_shortcode();
		$simpletags = wp_list_pluck( $tags, 'name' );
		$checkboxes = array();

		foreach ($tags as $tag) {
			if($tag['basetype'] == 'checkbox') $checkboxes[] = $tag['name'];
		}

	?>


<table class="form-table" id="mymail-cf7-settings">
	<tr>
	<th scope="row">&nbsp;</th>
	<td>
		<input type="checkbox" name="mymail[enabled]" value="1" <?php checked( $s['enabled'] ); ?>> enabled this form for MyMail
	</td>
	</tr>
	<tr>
	<th scope="row">
		<label><?php _e('Map Fields', 'mymail-cf7') ?></label>
	</th>
	<td>
		<p class="description"><?php _e('define which field represents which value from your MyMail settings', 'mymail-cf7') ?></p>
		<?php
		$fields = array(
			'email' => mymail_text('email'),
			'firstname' => mymail_text('firstname'),
			'lastname' => mymail_text('lastname'),
		);

		if ($customfields = mymail()->get_custom_fields()) {
			foreach ($customfields as $field => $data) {
				$fields[$field] = $data['name'];
			}
		}

		echo '<ul id="mymail-map">';
		foreach($s['fields'] as $field => $tag){
			echo '<li> <label>'.$this->get_tags_dropdown($simpletags, $tag, 'mymail[tags][]').'</label> ➨ <select name="mymail[fields][]">';
				echo '<option value="-1">'.__('not mapped', 'mymail-cf7').'</option>';
				echo '<option value="-1">--------</option>';
				foreach($fields as $id => $name){
					echo '<option value="'.$id.'" '.selected($id, $field, false).'>'.$name.'</option>';
				}
			echo '</select> <a class="cf7-mymail-remove-field" href="#">x</a></li>';
		}
		echo '</ul>';

		?>
		<a class="cf7-mymail-add-field button button-small" href="#">add field</a>
		</td>
	</tr>
	<?php if(!empty($checkboxes)) : ?>
	<tr>
	<th scope="row">
		<label>Conditional Check</label>
	</th>
	<td>
		<label><input type="hidden" name="mymail[checkbox]" value="0"><input type="checkbox" name="mymail[checkbox]" value="1" <?php checked( $s['checkbox'] ); ?>> user must check field <?php echo $this->get_tags_dropdown($checkboxes, $s['checkboxfield'], 'mymail[checkboxfield]') ?> to get subscribed</label>
	</td>
	</tr>
	<?php endif; ?>
	<tr>
	<th scope="row">
		<label>Double-opt-In</label>
	</th>
	<td>
		<label>
		<input type="hidden" name="mymail[doubleoptin]" value="0"><input type="checkbox" name="mymail[doubleoptin]" value="1" <?php checked( $s['doubleoptin'] ); ?>> user have to confirm their subscription</label>
	</td>
	</tr>
	<tr>
	<th scope="row">
		<label>Overwrite</label>
	</th>
	<td>
		<label><input type="checkbox" name="mymail[overwrite]" value="1" <?php checked( $s['overwrite'] ); ?>> Overwrite user if exists</label>
	</td>
	</tr>
	<tr>
	<th scope="row">
		<label>Lists</label>
	</th>
	<td>
	<ul id="mymail-lists">
	<?php
		$lists = mymail('lists')->get();
		foreach ($lists as $list) { ?>
		<li><label><input type="checkbox" name="mymail[lists][]" value="<?php echo $list->ID ?>" <?php checked( in_array($list->ID, $s['lists'] )); ?>> <?php echo esc_html($list->name) ?></label></li>
	<?php } ?>
	</ul>
	</td>
	</tr>
	<tr>
	<th scope="row">
		<label>Skip Mail</label>
	</th>
	<td>
		<label><input type="checkbox" name="mymail[skip_mail]" value="1" <?php checked( $s['skip_mail'] ); ?>> Skip the Mail from this Contact Form 7</label>
	</td>
	</tr>

</table>
	<?php

	}

	private function get_tags_dropdown($tags, $selected, $name ){

		$tagsdropdown = '<select name="'.$name.'">>';
		$tagsdropdown .= '<option value="0">'.__('choose tag', MYMAIL_CF7_DOMAIN).'</option>';
		foreach ($tags as $tag) {
			if(!empty($tag))
			$tagsdropdown .= '<option value="'.esc_attr($tag).'" '.selected( $selected, $tag, false ).'>['.$tag.']</option>';
		}
		$tagsdropdown .= '<select>';

		return $tagsdropdown;
	}


}
new CF7MyMail();
