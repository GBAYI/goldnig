(function($){

	// responsive video
	$.fn.gdlr_lms_fluid_video = function(){
		$(this).find('iframe[src^="http://www.youtube.com"], iframe[src^="https://www.youtube.com"], iframe[src^="//www.youtube.com"],'  +
					 'iframe[src^="http://player.vimeo.com"], iframe[src^="https://player.vimeo.com"], iframe[src^="//player.vimeo.com"]').each(function(){
			if( ($(this).is('embed') && $(this).parent('object').length) || $(this).parent('.fluid-width-video-wrapper').length ){ return; } 
			if( !$(this).attr('id') ){ $(this).attr('id', 'gdlr-video-' + Math.floor(Math.random()*999999)); }
					 
			// ignore if inside layerslider
			if( $(this).closest('.ls-container').length <= 0 ){ 
				var ratio = $(this).height() / $(this).width();
				$(this).removeAttr('height').removeAttr('width');
				$(this).wrap('<div class="gdlr-fluid-video-wrapper"></div>').parent().css('padding-top', (ratio * 100)+"%");
			}
		
		});	
		
		$(window).trigger('resize');
	}

	// create the alert message
	function gdlr_lms_confirm(options){
        var settings = $.extend({
			text: 'Are you sure you want to do this ?',
			sub: '',
			yes: 'Yes',
			no: 'No',
			success:  function(){}
        }, options);

		var confirm_button = $('<a class="gdlr-lms-button blue">' + settings.yes + '</a>');
		var decline_button = $('<a class="gdlr-lms-button red">' + settings.no + '</a>');
		var confirm_box = $('<div class="gdlr-lms-confirm-wrapper"></div>');
		var confirm_overlay = $('<div class="gdlr-lms-confirm-overlay"></div>');
		
		confirm_box.append('<span class="head">' + settings.text + '</span>');			
		if( settings.sub != '' ){
			confirm_box.append('<span class="sub">' + settings.sub + '</span>');	
		}
		confirm_box.append(confirm_button);
		confirm_box.append(decline_button);

		$('body').append(confirm_overlay).append(confirm_box);
		
		// center the alert box position
		confirm_box.css({ 'margin-left': -(confirm_box.outerWidth() / 2), 'margin-top': -(confirm_box.outerHeight() / 2)});
				
		// animate the alert box
		confirm_box.animate({opacity:1},{duration: 200});
		confirm_overlay.animate({opacity:0.75},{duration: 150});
		
		confirm_button.click(function(){
			if(typeof(settings.success) == 'function'){ settings.success(); }
			confirm_box.fadeOut(200, function(){ $(this).remove(); });
			confirm_overlay.fadeOut(200, function(){ $(this).remove(); });
		});
		decline_button.click(function(){
			confirm_box.fadeOut(200, function(){ $(this).remove(); });
			confirm_overlay.fadeOut(200, function(){ $(this).remove(); });
		});
	}	
	function gdlr_lms_notice(options){
        var settings = $.extend({
			text: 'Please try again',
			sub: '',
			ok: 'Ok',
			success:  function(){}
        }, options);

		var confirm_button = $('<a class="gdlr-lms-button blue">' + settings.ok + '</a>');
		var confirm_box = $('<div class="gdlr-lms-confirm-wrapper"></div>');
		var confirm_overlay = $('<div class="gdlr-lms-confirm-overlay"></div>');
		
		confirm_box.append('<span class="head">' + settings.text + '</span>');			
		if( settings.sub != '' ){
			confirm_box.append('<span class="sub">' + settings.sub + '</span>');	
		}
		confirm_box.append(confirm_button);
		$('body').append(confirm_overlay).append(confirm_box);
		
		// center the alert box position
		confirm_box.css({ 'margin-left': -(confirm_box.outerWidth() / 2), 'margin-top': -(confirm_box.outerHeight() / 2)});
				
		// animate the alert box
		confirm_box.animate({opacity:1},{duration: 200});
		confirm_overlay.animate({opacity:0.75},{duration: 150});
		
		confirm_button.click(function(){
			if(typeof(settings.success) == 'function'){ settings.success(); }
			confirm_box.fadeOut(200, function(){ $(this).remove(); });
			confirm_overlay.fadeOut(200, function(){ $(this).remove(); });
		});
	}	

	function gdlr_lms_lightbox(content, allow_close){
		var lightbox = $('<div class="gdlr-lms-lightbox-wrapper"></div>').appendTo('body');
		var overlay = $('<div class="gdlr-lms-lightbox-overlay" ></div>');
		
		var content_return = '';
		if( content.attr('data-return') == 'parent' ){
			content_return = content.parent();
		}
		
		// close lightbox
		if( allow_close ){
			overlay.click(function(){
				lightbox.fadeOut(200, function(){ 
					if( content_return ){
						content_return.append($(this).find('.gdlr-lms-lightbox-container'));
					}
					$(this).remove();
				});
			});

			content.find('.gdlr-lms-lightbox-close').click(function(){
				lightbox.fadeOut(200, function(){ 
					if( content_return ){
						content_return.append($(this).find('.gdlr-lms-lightbox-container'));
					}
					$(this).remove();
				});
			});		
		}

		

		content.find('.gdlr-lms-lightbox-printer').click(function(){
			var printContents = $(this).siblings('.gdlr-printable').html();
			$('body').children('div, img').css('display', 'none');
			$('body').append(printContents);
			window.print();
			lightbox.append(printContents);	
			$('body').children('div, img').css('display', 'block');
		});
		
		lightbox.append(overlay).append(content);
		lightbox.fadeIn(200);
		
		lightbox.gdlr_lms_fluid_video();
		
		// set height for certificate
		if( content.children().is('.certificate-form-printable') ){
			content.css('margin-top', -content.height()/2);
		}
	}
	
	function gdlr_lms_format_time(second){
		var hrs = parseInt(second / 3600);
		second = second % 3600;
		
		var mins = parseInt(second / 60);
		mins = (mins < 10)? '0' + mins: mins; 
		
		second = second % 60;
		second = (second < 10)? '0' + second: second; 
		
		return hrs + ':' + mins + ':' + second;
	}

	$(document).ready(function(){	

		// date picker
		$('input.gdlr-lms-date-picker').datepicker({
			dateFormat : 'yy-mm-dd'
		});
		
		// rating
		$('.rating-form .gdlr-rating-input').each(function(){
			$(this).children().hover(function(){
				$(this).parent().siblings('.rating-input').val($(this).attr("data-value"));

				if($(this).is('i')){ $(this).removeClass().addClass('icon-star-half-empty fa fa-star-half-empty'); }
				$(this).prevAll('i').removeClass().addClass('icon-star fa fa-star');
				$(this).nextAll('i').removeClass().addClass('icon-star-empty fa fa-star-o');
			});
		});
	
		// upload admin author image
		$('#gdlr-admin-author-image').change(function(){ 
			$(this).parents('form').submit();
		})
		$('.gdlr-page-profile .gdlr-lms-admin-head-thumbnail').click(function(){
			$('#gdlr-admin-author-image').trigger('click');
		});

		// cancel booking
		$('.gdlr-lms-cancel-booking, .gdlr-lms-delete-student').click(function(){
			var cancel_button = $(this);
			var action = 'gdlr_lms_cancel_booking';
			if( $(this).is('.gdlr-lms-delete-student') ){
				action = 'gdlr_lms_delete_student';
			}
			
			gdlr_lms_confirm({
				text: cancel_button.attr('data-title'),
				yes: cancel_button.attr('data-yes'),
				no: cancel_button.attr('data-no'),
				success: function(){
					$.ajax({
						type: 'POST',
						url: cancel_button.attr('data-ajax'),
						data: {'action':action,'id': cancel_button.attr('data-id')},
						dataType: 'json',
						error: function(a, b, c){ console.log(a, b, c); },
						success: function(data){
							location.reload();
						}
					});	
				}
			});
			
			return false;
		});
		
		// view quiz answer
		$('.gdlr-lms-view-correct-answer').click(function(){
			var view_button = $(this);
			gdlr_lms_confirm({
				text: view_button.attr('data-title'),
				sub: view_button.attr('data-sub-title'),
				yes: view_button.attr('data-yes'),
				no: view_button.attr('data-no'),
				success: function(){
					location.href = view_button.attr('href');
				}
			});		
		
			return false;
		});
		
		// equally set course style-2 height
		$(window).resize(function(){
			$('.gdlr-lms-course-grid2-wrapper').each(function(){
				var max_height = 0;
				var child_elements = $(this).find('.gdlr-lms-item');
				
				child_elements.css('height', 'auto');
				if($(window).width() <= '767') return;
				
				child_elements.each(function(){
					if($(this).height() > max_height) max_height = $(this).height();
				});
				child_elements.height(max_height);
			});	
		});
	
		// init the lightbox
		$('[data-rel="gdlr-lms-lightbox"]').click(function(){
			var content = $(this).siblings('.' + $(this).attr('data-lb-open'));
			if( !content.attr('data-return') ){
				var content = $(this).siblings('.' + $(this).attr('data-lb-open')).clone(true);
			}
			if(content.length > 0){ gdlr_lms_lightbox(content, true); }
		});
		$('[data-rel="gdlr-lms-lightbox2"]').click(function(){
			var content = $(this).siblings('.gdlr-lms-lightbox-container-wrapper').clone(true);
			content.children('.' + $(this).attr('data-lb-open')).show();
			if(content.length > 0){ gdlr_lms_lightbox(content, true); }
		});	
		$('[data-rel="gdlr-lms-lightbox3"]').click(function(){
			$(this).parents('.gdlr-lms-lightbox-container').fadeOut(200)
				   .siblings('.' + $(this).attr('data-lb-open')).fadeIn(200);
		});			
		
		// quiz timer
		$('.gdlr-lms-quiz-timer input[name="timeleft"]').each(function(){
			if($(this).attr('data-full') == '0' || $(this).attr('data-full') == '') return;
				
			var timer = $(this);	
			var display = timer.siblings('.timer');
			var time_left = parseInt(timer.val());
			
			var i = setInterval(function(){
				if( time_left > 0 ){
					time_left = time_left - 1;
					timer.val(time_left);
					display.html(gdlr_lms_format_time(time_left));
				}else{
					var content = timer.siblings('.quiz-timeout-form').clone(true);
					gdlr_lms_lightbox(content, false);
					clearInterval(i);
				}
			}, 1000);
		});
		$('.submit-quiz-timeout-form').click(function(){
			$(this).parents('.gdlr-lms-lightbox-wrapper').fadeOut(200, function(){ 
				$(this).remove();
			});
			
			$('.finish-quiz-form-button').trigger('click');
		});
		
		// quiz form submit
		$('.submit-quiz-form').click(function(){
			var current_form = $(this).parents('form');
			
			if( current_form.length <= 0 ){ current_form = $('form.gdlr-lms-quiz-type'); }
			
			current_form.attr('action', $(this).attr('href'));
			current_form.submit();
			return false;
		});
		
		// finish course button
		$('.finish-quiz-form-button').click(function(){
			current_button = $(this);
			if( current_button.html() == current_button.attr('data-loading') ){
				return false;
			}

			current_button.html(current_button.attr('data-loading'));
			quiz_form = current_button.parents('form');
			
			$.ajax({
				type: 'POST',
				url: current_button.attr('href'),
				data: quiz_form.serialize(),
				dataType: 'json',
				error: function(a, b, c){ console.log(a, b, c); },
				success: function(){
					var content = current_button.siblings('.finish-quiz-form').clone(true);
					gdlr_lms_lightbox(content, false);
				}
			});	
			
			return false;
		});
		
		// payment selection
		$('.gdlr-payment-method input[name="payment-method"]').click(function(){
			$(this).parent('label').addClass('gdlr-active').siblings().removeClass('gdlr-active');
		});
		
		// buy / book button
		$('.buy-form form, .book-form form').each(function(){
			if( $(this).hasClass('gdlr-no-ajax') ) return;

			$(this).on('price-calculate', function(){
				var price_one = $(this).find('.price-one');
				var price_format = price_one.siblings('.format').val();
				var price = parseFloat(price_one.val()) * parseInt($(this).find('input[name="quantity"]').val());
				
				var discount = $(this).find('input.coupon-amount');
				var discount_type = discount.siblings('input.coupon-type');
				var discount_price = 0;
				if( discount_type.val() == 'percent' ){
					discount_price = parseInt( price * parseInt(discount.val()) / 100 );
				}else if( discount_type.val() == 'amount' ){
					discount_price = parseInt( discount.val() );
				}
				if( discount_price > price ){
					discount_price = price;
				}

				price = price - discount_price;
				price_one.siblings('.price').val(price);
				price_one.siblings('.price-display').val(price_format.replace('NUMBER', price));				
				discount.siblings('.discount-amount').val(discount_price);				
			});
			
			$(this).find('input[name="quantity"]').on('keyup', function(){
				if( $(this).val() != '' ){
					$(this).closest('form').trigger('price-calculate');
				}
			});
			
			$(this).find('.gdlr-lms-coupon-code').on('keyup', function(){
				var current_form = $(this).closest('form');
				var coupon_code = $(this);
				
				if( $(this).val() != '' ){
					$(this).siblings('.gdlr-lms-coupon-head').removeClass().addClass('gdlr-lms-coupon-head coupon-loading');
					$(this).siblings('.gdlr-lms-coupon-status').slideUp(200);
					
					$.ajax({
						type: 'POST',
						url: current_form.attr('data-ajax'),
						data: {action:'lms_check_coupon_code', id: $(this).val(), course_id: current_form.find('input[name="course_id"]').val()},
						dataType: 'json',
						error: function(a, b, c){ console.log(a, b, c); },
						success: function(data){
							if( data.status == 'failed' ){
								coupon_code.siblings('.gdlr-lms-coupon-head').removeClass().addClass('gdlr-lms-coupon-head coupon-wrong');
								if( data.message ){
									coupon_code.siblings('.gdlr-lms-coupon-status').html(data.message).slideDown(200);
								}else{
									coupon_code.siblings('.gdlr-lms-coupon-status').slideUp(200);
								}
							}else if( data.status == 'success' ){
								coupon_code.siblings('input.coupon-amount').val(data.amount);
								coupon_code.siblings('input.coupon-type').val(data.type);
								coupon_code.siblings('.gdlr-lms-coupon-head').removeClass().addClass('gdlr-lms-coupon-head coupon-correct');
								if( data.message ){
									coupon_code.siblings('.gdlr-lms-coupon-status').html(data.message).slideDown(200);
								}else{
									coupon_code.siblings('.gdlr-lms-coupon-status').slideUp(200);
								}
							}
							
							current_form.trigger('price-calculate');
						}
					});
				}else{
					$(this).siblings('.gdlr-lms-coupon-head').removeClass().addClass('gdlr-lms-coupon-head');
					$(this).siblings('.gdlr-lms-coupon-status').slideUp(200);
				}
			});
		
			$(this).submit(function(e){
				var current_form = $(this);
				var notice = $(this).find('.gdlr-lms-notice').slideUp(200);
				var loading = $(this).find('.gdlr-lms-loading').slideDown(200);

				$.ajax({
					type: 'POST',
					url: $(this).attr('data-ajax'),
					data: jQuery(this).serialize(),
					dataType: 'json',
					error: function(a, b, c){ console.log(a, b, c); },
					success: function(data){
						if( data.status == 'success' ){
							notice.addClass('success');
						}else{
							notice.removeClass('success');
						}
						
						notice.html(data.message).slideDown(200);
						loading.slideUp(200);
						
						if( data.status == 'success' && data.redirect ){
							if( data.redirect == true ){
								if( data.id ){
									current_form.find('[name="invoice"]').val(data.id);
								}
								current_form[0].submit();
							}else{
								window.location.replace(data.redirect);
							}
						}else if( data.status == 'success' && data.payment == 'cloud' ){
							var payments = new cp.CloudPayments({ language: "en-US" });
							payments.charge( data.data,
								function (options){
									$.ajax({
										type: 'POST',
										url: current_form.attr('data-ajax'),
										data: { action:'gdlr_lms_cloud_payment', return_val:options },
										dataType: 'json',
										error: function(a, b, c){ console.log(a, b, c); },
										success: function(data2){
											if( data2.status == 'success' ){
												if( data2.redirect ){
													window.location.replace(data2.redirect);
												}
											}else{
												if( data2.message ){
													gdlr_lms_notice({
														text: data2.message,
														sub: (data2.message_sub)? data2.message_sub: '',
														ok: data.ok_button
													});
												}
											}
										}
									});
								},
								function (reason, options){
									gdlr_lms_notice({
										text: data.payment_failed_text,
										sub: reason,
										ok: data.ok_button
									});
								}
							);
						}
					}
				});					
				
				e.preventDefault();
				e.returnValue = false;
			});
		
		});
	});

	$(window).load(function(){ $(this).trigger('resize'); });

})(jQuery);