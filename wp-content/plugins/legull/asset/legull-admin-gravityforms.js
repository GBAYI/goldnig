jQuery(document).ready(function() {
	fieldSettings["legull_tos_display"] = ".legull_disable_submit,.rules_setting,.admin_label_setting, .error_message_setting, .css_class_setting, .visibility_setting, .size_setting";
	fieldSettings["legull_tos_accept"] = ".legull_disable_submit,.rules_setting,.admin_label_setting, .error_message_setting, .css_class_setting, .visibility_setting";
	fieldSettings["legull_link"] = ".css_class_setting, .visibility_setting";
	jQuery(document).bind("gform_load_field_settings", function(event, field, form){
		jQuery("#field_legull_disable_submit").attr("checked", field["legull_disable_submit"] == true);
		jQuery("#field_legull_disable_submit_value").val(field["legull_disable_submit"]);
	});
});