(function() {
	if( gdlr_lms_shortcodes && typeof(tinyMCE) != "undefined" && typeof(tinyMCE.majorVersion) != "undefined" && tinyMCE.majorVersion >= 4 ){
		tinymce.PluginManager.add('gdlr_lms', function( editor, url ) {
			var list = [];
			for(var i in gdlr_lms_shortcodes){
				if( gdlr_lms_shortcodes[i] ){
					var item = {};
					item.text = gdlr_lms_shortcodes[i].title;
					item.value = gdlr_lms_shortcodes[i].value;
					item.onclick = function() {
						editor.insertContent(this.value());
					}
					list.push(item);
				}
			}
		
			editor.addButton( 'gdlr_lms', {
				text: 'LMS Shortcode',
				type: 'menubutton',
				icon: false,
				menu: list
			});
		});
	}else if( gdlr_lms_shortcodes ){
		tinymce.create('tinymce.plugins.gdlr_lms', {
		
			init : function(ed, url) { },
			createControl : function(n, cm) {
		
				if(n=='gdlr_lms'){
					var mlb = cm.createListBox('gdlr_lms', {
						 title : 'Shortcode',
						 onselect : function(v) {
							if(tinyMCE.activeEditor.selection.getContent() == ''){
								tinyMCE.activeEditor.selection.setContent( v );
							}
						 }
					});
				
					for(var i in gdlr_lms_shortcodes){
						mlb.add(gdlr_lms_shortcodes[i].title, gdlr_lms_shortcodes[i].value);
					}
					
					return mlb;
				}
				return null;
			}
		
		
		});
		tinymce.PluginManager.add('gdlr_lms', tinymce.plugins.gdlr_lms);
	}
})();