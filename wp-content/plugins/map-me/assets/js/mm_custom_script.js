 jQuery(document).ready(function(){

	 jQuery(".mm_icon_group").click(function(){

	   jQuery(this).find(":radio").attr('checked', true);

	   jQuery('.mm_icon_group').not(this).removeClass('icon_checked');
	   jQuery(this).toggleClass('icon_checked');
	   
	   jQuery('.mm_icon').not(this).removeClass('mm_icon_checked');
	   jQuery(this).find(".mm_icon").toggleClass('mm_icon_checked');	   

	 });

	 jQuery("#mm_featured").click(function(){
	    if(document.getElementById('mm_featured').checked) {
		    jQuery(".mm_set_marker_animation").fadeIn();
		} else {
		    jQuery(".mm_set_marker_animation").fadeOut();
		}	 	
	 });



 });