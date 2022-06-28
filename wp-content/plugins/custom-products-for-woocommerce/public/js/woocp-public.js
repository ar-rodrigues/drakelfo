(function( $ ) {
    'use strict';
    //var aniTime = 500;	//slow
    //var aniTime2 = 250;	//fast
    var aniTime3 = 150;	//faster
    // var aniTime4 = 75;	//super fast
    var selectH = 55;

    $.widget( 'custom.iconselectmenu', $.ui.selectmenu, {
        _renderItem: function( ul, item ) {
            var li = $( '<li>' ),
                wrapper = $( '<div>', { text: item.label } );

            if ( item.disabled ) {
                li.addClass( 'ui-state-disabled' );
            }

            $( '<span>', {
                style: item.element.attr( 'data-style' ),
                'class': 'ui-icon ' + item.element.attr( 'data-class' )
            })
                .appendTo( wrapper );

            return li.append( wrapper ).appendTo( ul );
        }
    });
    $.fn.woocpChangeProduct = function() {
        var cEl = $(this);
        var container = $('.woocp_customizer_container');
        var customizer = container.find('.woocp_customizer');
        container.woocpDoOverlay();
        var prodId = cEl.val();
        var data = {
            'action': 'woocp_change_product',
            'product_id': prodId
        };
        $.post(ajax_object.ajax_url, data, function(response) {
            customizer.css('height',customizer.height()).attr('data-productid',prodId).children().each(function(){
                $(this).fadeOut(aniTime3,function(){
                  console.log(response);
                    $(this).remove();
                    //if($(this).hasClass('woocp_product_customizer_image')){
                        customizer.html(response).css('height','auto');
                        if(container.find('image#canvas_bg').length > 0){
                            container.find('image#canvas_bg').on('load',function(){
                                container.woocpUndoOverlay();
                            });
                        }
                        else if(container.find('img#woocp_customizer_image').length > 0){
                            container.find('img#woocp_customizer_image').on('load',function(){
                                container.woocpUndoOverlay();
                            });
                        }
                        $( '.woocp_attribute_select,.woocp_product_select' ).each(function(){
                            $(this).iconselectmenu().iconselectmenu( 'menuWidget' ).addClass( 'ui-menu-icons woocp_option_icons' );
                        });
                        //IE fix
                        setTimeout(function(){
                            container.woocpUndoOverlay();
                        },500);
                    //}
                });
            });

        });
    };

    $.fn.woocpAddToCart = function(prodId=false) {
        var atcEl = $(this);
        if(!prodId) prodId = $(this).attr('data-productid');
        var selected = atcEl.parent().find('input[name="woocp_selected"]').val();
        var quantity = atcEl.parent().find('input[name="quantity"].woocp_quantity').val();

        if(null == quantity || undefined == typeof quantity || 'undefined' == quantity) {
            quantity = $('.quantity input').val();
        }
        var target = $(this).closest('.woocp_add_to_cart_container').find('.woocp_msg_area');

        var spinner = $(this).closest('.woocp_add_to_cart_container').find('.woocp_spinner_container');
        $(atcEl).attr('disabled','true');

        spinner.show();
        target.height(0);

        var data = {
            'action': 'woocp_add_to_cart',
            'woocp_selected': selected,
            'add_to_cart': prodId,
            'qty': quantity
        };

        $.post(ajax_object.ajax_url, data, function(response) {
            spinner.hide();
            target.html(response);
            var h = target.find('[class^="woocommerce-"]')[0].offsetHeight;
            target.height(h+30);
            $(atcEl).removeAttr('disabled');
        });
    };
    $.fn.woocpDoOverlay = function(){
        $(this).addClass("processing").block({
            message: null ,
            overlayCSS: {
                background: "rgba(255,255,255,0.6)",
                opacity: 1,
                zIndex: 999999999
            }
        });
    };
    $.fn.woocpUndoOverlay = function(){
        $(this).removeClass("processing").unblock();
    };

    $(document).on('ready ajaxComplete',function(){
        woocpAddMissingForms();
    });

    $(window).on('load',function(){
        $( '.woocp_attribute_select,.woocp_product_select' ).each(function(){
            $(this).iconselectmenu().iconselectmenu( 'menuWidget' ).addClass( 'ui-menu-icons woocp_option_icons' );
        });
        $('.woocp_customizer_container .ui-selectmenu-button').each(function(){
            var w = $(this).closest('.woocp_customizer_container').find('.woocp_customizer').width();
            $(this).width(w);
        });
    });

    $(document).on('click','.woocp_product_component.collapsed .expand',function(e){
        var target = $(this).closest('.woocp_product_component');
        target.removeClass('collapsed').addClass('expanded');
        target.find('.woocp_component_attribute').each(function(){
            $(this).toggleSlide().removeClass('collapsed').addClass('expanded');
        });
    });
    $(document).on('click','.woocp_product_component.expanded .expand',function(e){
        var target = $(this).closest('.woocp_product_component');
        target.removeClass('expanded').addClass('collapsed');
        target.find('.woocp_component_attribute').each(function(){
            $(this).toggleSlide().removeClass('expanded').addClass('collapsed');
        });
    });

    $(document).on('click','.woocp_add_to_cart_button',function(e){
        e.preventDefault();
        $(this).woocpAddToCart();
    });

    $(document).on('change','.woocp_component_attribute input[type="radio"]',function(e){
        e.preventDefault();
        var customizer = $(this).closest('.woocp_customizer');
        woocpUpdateSelectedArray(customizer);
    });
    $(document).on('iconselectmenuchange','.woocp_product_select', function(e){
        e.stopPropagation();
        $(this).woocpChangeProduct();
        $(this).iconselectmenu('close');
    });

    function woocpUpdateSelectedArray(customizer=false, targetClass=false){
        if(false === customizer){
            customizer = $('.woocp_customizer');
        }
        let target = customizer.find('input[name="woocp_selected"]');
        if(false !== targetClass && 'undefined' !== targetClass && undefined !== typeof targetClass && undefined !== targetClass && targetClass.length > 0) {
            target = $('.'+targetClass).find('input[name="woocp_selected"]');
        }
        var selected = {};
        var compObj = {};
        var val, attrId, attrLabel, compId, compLabel, fee, optLabel,eCost;
        customizer.find('.woocp_product_component').each(function(){
            var coEl = $(this);
            compId = coEl.attr('data-componentid');
            compLabel = coEl.attr('data-componentlabel');
            fee = null;
            compObj = {'id':compId,'label':compLabel,'fee':fee,'attrs':[]}
            selected[compId] = compObj;
            coEl.find('.woocp_component_attribute').each(function(){
                val = $(this).find('input[type="radio"]:checked').val();
                eCost = $(this).find('input[type="radio"]:checked').attr('data-cost');
                if(parseInt(val) !== 0){
                    optLabel = $(this).find('input[type="radio"]:checked').parent().text();
                    attrId = $(this).attr('data-attributeid');
                    attrLabel = $(this).attr('data-attributelabel');
                    selected[compId]['attrs'].push({'id':attrId,'label':attrLabel,'selected':val,'selectedLabel':optLabel,'extraCost':eCost});
                }
            });
        });
        target.each(function(){
            $(this).val(JSON.stringify(selected));
        });
    }

    function woocpAddMissingForms() {
        $('input[name="woocp_selected"]').each(function(){
            let el = $(this);
            if(el.closest('form').length === 0 && el.closest('div.cart').length > 0){
                let form = el.closest('div.cart');
                let formHtml = form.html();
                let prodId = form.attr('data-product_id');
                let variations = form.attr('data-product_variations');
                let enctype = form.attr('enctype');
                let method = form.attr('method');
                let action = form.attr('action');
                let classes = form.attr('class');

                let newEl = $('<form class="'+classes+'" enctype="'+enctype+'" method="'+method+'" action="'+action+'" data-product_id="'+prodId+'" data-product_variations=\''+variations+'\'></form>');
                newEl.html(formHtml);

                form.replaceWith(newEl);
            }
        });
    }

})( jQuery );
