(function( $ ) {
	'use strict';
	var aniTime = 500;	//slow
	var aniTime2 = 250;	//fast
	var aniTime3 = 150;	//faster
	var aniTime4 = 75;	//super fast
	var licenseMsgH = 60;
	
	$.fn.woocpOpenProductPanel = function() {
		if($(this).prop('checked') == true)	$('#woocp_product_customizer_wrapper').removeClass('hide');
		else $('#woocp_product_customizer_wrapper').addClass('hide');
	};
	
	$.fn.woocpCustomizerMediaUploader = function() {
		var mediaUploader;
		
		if (mediaUploader) {
			mediaUploader.open();
			return;
		}
		
		mediaUploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
			text: 'Choose Image'
		}, multiple: false });

		mediaUploader.on('select', function() {
			$('.spinner.woocp_save_customizer_image').css('visibility','visible');
			var attachment = mediaUploader.state().get('selection').first().toJSON();
			$('#woocp_customizer_image_id').val(attachment.id);
			$('#woocp_customizer_image').attr('src',attachment.url);
			saveCustomizerImage();
			$(document).trigger('woocp-customizer-image-updated');
		});
		mediaUploader.open();
	};
	$.fn.woocpAttributeMediaUploader = function() {
		var mediaUploader;
		
		if (mediaUploader) {
			mediaUploader.open();
			return;
		}
		
		mediaUploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
			text: 'Choose Image'
		}, multiple: false });

		mediaUploader.on('select', function() {
			var attachment = mediaUploader.state().get('selection').first().toJSON();
			$('#woocp_taxonomy_image_id').val(attachment.id);
			$('#woocp_taxonomy_image_wrapper').html('<img src="'+attachment.url+'"/>');
		});
		mediaUploader.open();
	};
	
	$.fn.woocpUpdateCustomizable= function() {
		var postId = $('input[name="post_ID"]').val();
		var customizable = $('input[name="_woocp_is_customizable"]').prop('checked');
		if(customizable) var val = 1;
		else var val = 0;
		var data = {
			'action': 'woocp_update_customizable',
			'postId': postId,
			'_woocp_is_customizable': val,
		};

		jQuery.post(ajaxurl, data, function(response) {
			$('.spinner.woocp_update_customizable').css('visibility','hidden');
		});
	};
	
	$.fn.woocpRemoveCustomizerImage= function() {
		var postId = $('input[name="post_ID"]').val();
		var data = {
			'action': 'woocp_remove_customizer_image',
			'postId': postId,
		};

		jQuery.post(ajaxurl, data, function(response) {
			$('.spinner.woocp_save_customizer_image').css('visibility','hidden');
			$('#woocp_customizer_image').attr('src',$('#woocp_customizer_image_original').attr('src'));
		});
	};
	 
	$(function(){
		
		$('#_woocp_is_customizable').each(function(){
			$(this).woocpOpenProductPanel();
		});
		
		$('#woocp_product_customizer_tabs').tabs();
		
		$('.wp-list-table #woocp_component_image > a > span:first-child').each(function(){
			$(this).addClass('woocp_component_image_icon');
		});
		
		$('input.disabled,select.disabled,textarea.disabled').each(function(){
			$(this).attr('disabled','disabled');
		});
		$('input.readonly,select.readonly,textarea.readonly').each(function(){
			$(this).attr('readonly','readonly');
		});
		
		$('input[type="number"]:not(#woocp_customizer_tab_priority)').attr('min',0);
		$('input[name="woocp_customizer_image_width"]').attr('max',100).attr('min',1);
		
		$('#woocp_custom_css_help').each(function(){
			$(this).attr('data-height',$(this).height()).height(0).css('opacity',1);
		});
		
	});

	$(window).on('load',function(){
		$('.woocp_select2').select2();
		woocpResetTipTip();
	});
	$(document).on( 'click', '.upgrade_notice .notice-dismiss', function () {
        var type = $( this ).closest( '.upgrade_notice' ).data( 'notice' );
		var data = {
              action: 'woocp_dismissed_notice_handler',
              type: type,
		};
		jQuery.post(ajaxurl, data, function(response) {
			
		});
    });
	$(document).on('click','#_woocp_is_customizable',function(){
		$('.spinner.woocp_update_customizable').css('visibility','visible');
		$(this).woocpOpenProductPanel();
		$(this).woocpUpdateCustomizable();
	});
	$(document).on('click','#woocp_add_attribute_image_button',function(e){
		e.preventDefault();
		$(this).woocpAttributeMediaUploader();
        return false;
    });
    $(document).on('click','#woocp_remove_attribute_image_button',function(){
       $('#woocp_taxonomy_image_id').val('');
       $('#woocp_taxonomy_image_wrapper').html('');
    });
	$(document).on('click','#woocp_add_customizer_image_button',function(e){
		e.preventDefault();
		$(this).woocpCustomizerMediaUploader();
        return false;
    });
	$(document).on('click','#woocp_remove_customizer_image_button',function(e){
		$('.spinner.woocp_save_customizer_image').css('visibility','visible');
		e.preventDefault();
		$(this).woocpRemoveCustomizerImage();
        return false;
    });
	
	$(document).on('click','#woocp_product_customizer_container label',function(){
		var lFor = $(this).attr('for');
		var target = $('*[name="'+lFor+'"]');
		if (target.tagName == 'input' && target.attr('type') == 'input') target.click();
		if (target.tagName == 'input' && target.attr('type') == 'checkbox') target.click();
	});
	
	window.woocpResetTipTip = function(){
		if(typeof $(document).tipTip !== 'undefined'){
			var tiptip_args = {
				'attribute': 'data-tip',
				'fadeIn': 50,
				'fadeOut': 50,
				'delay': 200
			};
			$( '.woocommerce-help-tip,.woocp_pro_label.tips' ).tipTip( tiptip_args );
		}
	}
	
	function saveCustomizerImage(){
		var postId = $('input[name="post_ID"]').val();
		var data = {
            action: 'woocp_save_customizer_image',
			postId: postId,
            image_id: $('#woocp_customizer_image_id').val(),
		};
		jQuery.post(ajaxurl, data, function(response) {
			$('.spinner.woocp_save_customizer_image').css('visibility','hidden');
		});
	}
	


})( jQuery );


