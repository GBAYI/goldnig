<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Legull_GravityForms {
	private $fields = array();

	function __construct(){
		$this->fields[] = (object) array(
			'id' => 'legull_tos_display',
			'label' => __('Display Terms', 'legull'),
			'class' => 'button'
			);
		$this->fields[] = (object) array(
			'id' => 'legull_tos_accept',
			'label' => __('Accept Terms', 'legull'),
			'class' => 'button'
			);
		$this->fields[] = (object) array(
			'id' => 'legull_link',
			'label' => __('Link to Terms', 'legull'),
			'class' => 'button'
			);
		add_filter( 'gform_add_field_buttons', array( $this, 'add_field_button' ) );
		add_filter( 'gform_field_type_title', array( $this, 'field_type_title' ) );
		add_filter( 'gform_field_content', array( $this, 'field_content' ), 10, 5);
		add_action( 'gform_field_standard_settings' , array( $this, 'field_standard_settings' ), 10, 2 );
		add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
		add_action( 'gform_editor_js', array( $this, 'editor_js' ) );
		add_filter( "gform_pre_render", array( $this, "pre_render" ) );

	
	}
	function add_field_button( $field_groups ){
		foreach( $field_groups as &$group ){
			if( $group["name"] == "advanced_fields" ){
				foreach( $this->fields as $field ){
					$group["fields"][] = array(
						"class"=> $field->class,
						"value" => $field->label,
						"onclick" => sprintf( "StartAddField('%s');",  $field->id )
					);	
				}
				break;
			}
		}
		return $field_groups;
	}

	function field_type_title( $type ){
		$labels = wp_list_pluck( $this->fields, 'label', 'id' );
		if( array_key_exists( $type, $labels ) ){
			return $labels[ $type ];
		}
	}

	function field_content($field_content, $field, $value, $lead_id, $form_id){
		$labels = wp_list_pluck( $this->fields, 'label', 'id' );
		if( array_key_exists( $field['type'], $labels ) ){
			$required_div = IS_ADMIN || rgar($field, "isRequired") ? sprintf("<span class='gfield_required'>%s</span>", $field["isRequired"] ? "*" : "") : "";
			$field_display = sprintf( "<label class='gfield_label' for='input_{$field["id"]}'>%s%s</label><div class='ginput_container'>%s</div><div class='gfield_description'>%s</div>",
				$this->field_label( $field, $form_id ),
				$required_div,
				$this->field_container( $field, $form_id ),
				$this->field_description( $field, $form_id ));

			if( is_admin() ){
				if( RGForms::get("view") == 'entry' ){

				} else {
					$field_content = sprintf("<div class='gfield_admin_icons'><div class='gfield_admin_header_title'>%s</div><a class='field_delete_icon' id='gfield_delete_{$field["id"]}' title='%s' href='#' onclick='StartDeleteField(this); return false;'><i class='fa fa-times fa-lg'></i></a><a class='field_duplicate_icon' id='gfield_duplicate_{$field["id"]}' title='%s' href='#' onclick='StartDuplicateField(this); return false;'><i class='fa fa-files-o fa-lg'></i></a><a class='field_edit_icon edit_icon_collapsed' title='%s'><i class='fa fa-caret-down fa-lg'></i></a></div>%s",
						$labels[ $field['type'] ] . ' : ' . __( 'Field ID', 'legull' ) . ' ' . $field["id"],
						__( 'Click to delete this field', 'legull' ),
						__( 'Click to duplicate this field', 'legull' ),
						__( 'Click to expand and edit the options for this field', 'legull' ),
						$field_display
						);
				}
			} else {
				$field_content = sprintf( "<li id='field_%s' class='gfield'>%s</li>", 
					$form_id . '_' . $field['id'],
					$field_display
					);
			}
		}
	    return $field_content;
	}

	function field_standard_settings( $position, $form_id ){
		// Create settings on position 50 (right after Field Label)
		if( $position == 50 ){
		?>
		<li class="legull_disable_submit field_setting">
			<input type="checkbox" id="field_legull_disable_submit" value="accepted" onclick="SetFieldProperty('legull_disable_submit', this.checked);" />
			<label for="field_legull_disable_submit" class="inline">
				<?php _e("Disable submit button unless terms are accepted", "legull"); ?>
				<?php gform_tooltip("form_field_legull_disable_submit"); ?>
			</label>
		</li>
		<?php
		}
	}

	function tooltips($tooltips){
		$tooltips["form_field_legull_disable_submit"] = "<h6>Disable Submit Button</h6>Check the box if you would like to disable the submit button.";
		return $tooltips;
	}

	function editor_js(){
		wp_enqueue_script( 'legull-admin-gravityforms', LEGULL_URL . 'asset/legull-admin-gravityforms.js', array( 'jquery' ), '1.0', true );
	}

	function field_label( $field, $form_id, $field_content = '' ){
		// print_r($field);
		switch( $field['type'] ){
			case 'legull_tos_display':
				$field_content = __( 'Terms & Conditions.', 'legull' );
				break;
		}
		return $field_content;
	}

	function field_container( $field, $form_id, $field_content = '' ){
		$cssClasses = $field["type"];
		if( isset( $field['cssClass'] ) ){
			$cssClasses .= ' ' . esc_attr($field['cssClass']);
		}
		if( !empty( $field["legull_disable_submit"] ) && $field['legull_disable_submit'] == 'accepted' ){
			$cssClasses .= ' legull_disable_submit';
		}
		$input_name = $form_id .'_' . $field["id"];
		switch( $field['type'] ){
			case 'legull_tos_display':
				$tab_index = GFCommon::get_tabindex();
				$cssClasses .= $field['size'] . ' textarea';
				$field_content = sprintf( "<textarea readonly class='%s' $tab_index  cols='50' rows='10'>%s</textarea>", 
						$cssClasses,
						legull_get_terms_content( true ) );
				break;
			case 'legull_tos_accept':
				$attestation = __( 'I have read and agree to the', 'legull' );
				if( is_admin() ){
					$field_content = sprintf( "<input disabled='disabled' type='checkbox' name='input_%s' id='%s' class='%s' /> %s %s.", 
							$field["id"], 
							$field['type'] . '-' . $field['id'] , 
							$cssClasses, 
							$attestation,
							legull_get_terms_link() );
				} else {
					$tab_index = GFCommon::get_tabindex();
					$field_content = sprintf( "<label><input type='checkbox' name='input_%s' id='%s' class='%s' $tab_index /> %s %s.</label>", 
							$field["id"], 
							$field['type'] . '-' . $field['id'] , 
							$cssClasses, 
							$attestation,
							legull_get_terms_link() );
				}
				break;
			case 'legull_link':
				$field_content = sprintf( "%s %s.", 
					__( 'Read the site', 'legull' ), 
					legull_get_terms_link() );
				break;
		}
		return $field_content;
	}

	function field_description( $field, $form_id, $field_content = '' ){
		switch( $field['type'] ){
			case 'legull_tos_display':
				if( !empty( $field['legull_disable_submit'] ) && $field['legull_disable_submit'] ) {
					$field_content = __('In order to accept, you must read the entire Terms & Conditions.','legull');	
				}
				break;
		}
		return $field_content;
	}


	function pre_render( $form ){
// 		if( !is_admin() ){
// 			// foreach($form['fields'] as &$field){
// echo '<pre>';
// print_r($form);
// echo '</pre>';
// 		}
		return $form;
	}

}