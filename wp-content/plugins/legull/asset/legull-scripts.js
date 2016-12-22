jQuery(document).ready(function ($) {
	if( $('.legull_tos_accept').hasClass('legull_disable_submit') ){
		legull_disable_submit( true );
		$('.legull_tos_accept').on('click',function(){
			legull_disable_submit( !$(this).is(":checked") );
		});
	}
	if( $('.legull_tos_display').hasClass('legull_disable_submit') ){
		legull_disable_submit( true );
		$(".gform_body textarea.legull_tos_display").scroll(function(){
			if($(this).scrollTop()+$(this).height() >= $(this)[0].scrollHeight-10){
				legull_disable_submit( false );
			}
		});
		// todo add alert to submit button prior to submit if not accepted
		// .on('click',function(){
		// 	alert('please accept the terms before submitting.');
		// });
	}
});

function legull_disable_submit( should_disable ){
	jQuery(".gform_wrapper form input[type=submit]").attr("disabled", should_disable);
}