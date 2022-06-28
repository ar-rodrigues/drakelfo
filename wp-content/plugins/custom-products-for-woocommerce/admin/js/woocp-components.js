(function( $ ) {
    'use strict';
    var aniTime2 = 500;	//slow
    var aniTime2 = 250;	//fast
    var aniTime3 = 150;	//faster
    var aniTime4 = 75;	//super fast

    $.fn.woocpAddProductComponent = function() {
        //get attached components and add the new id
        var success = true;
        var select = $('#_woocp_product_components_select');
        if(select.val() === 'placeholder') return false;

        var compId = parseInt(select.val());
        var attached = $('input[name="_woocp_product_components"]');
        var attachedArray = JSON.parse(attached.val());
        if(attachedArray == null) attachedArray = [];

        attachedArray.forEach(function(comp){
            if(parseInt(comp.id) === compId) success = false;
        });
        if(!success) return false;
        $('.spinner.woocp_add_component').css('visibility','visible');

        var postId = $('input[name="post_ID"]').val();
        var newObj = {'id':compId,'label':'','description':'','fee':'','attrs':[]};
        attachedArray.push(newObj);
        var newArray = JSON.stringify(attachedArray);

        //add new array to hidden input field
        $(attached).val(newArray);

        var data = {
            'action': 'woocp_add_product_component',
            'postId': postId,
            'componentId': compId,
            '_woocp_product_components': newArray
        };

        jQuery.post(ajaxurl, data, function(response) {
            $('#woocp_product_components_list').append(response);
            $('.spinner.woocp_add_component').css('visibility','hidden');
            woocpResetTipTip();
        });
    };

    $.fn.woocpSaveProductComponents = function() {
        var fullArray = woocpGetFullArray();
        var postId = $('input[name="post_ID"]').val();
        if($('input[name="woocp_product_customization_fee"]').length !== 0){
            var custFee = $('input[name="woocp_product_customization_fee"]').val();
        }
        else var custFee = '';

        var data = {
            'action': 'woocp_save_product_components',
            'postId': postId,
            '_woocp_product_components': fullArray,
            'woocp_product_customization_fee':custFee
        };

        jQuery.post(ajaxurl, data, function(response) {
            $('.woocp_message .msg1').fadeOut(aniTime2);
            $('.spinner.woocp_save_components').css('visibility','hidden');
        });
    };

    $.fn.woocpDeleteProductComponent = function() {
        var confirm = window.confirm('Are you sure you want to remove this component?');
        if(confirm){
            var id = $(this).closest('.woocp_product_component').attr('data-componentId');
            var attachedEl = $('input[name="_woocp_product_components"]');
            var attached = JSON.parse(attachedEl.val());
            for(var i=0;i<attached.length;i++){
                if(parseInt(attached[i].id) === parseInt(id)) attached.splice(i,1);
            }
            if(attached == null) attached = [];
            $(this).closest('.woocp_product_component').remove();
            $(attachedEl).val(JSON.stringify(attached));
            $(attachedEl).woocpSaveProductComponents();
        }
    };

    $.fn.woocpAddComponentAttribute = function() {
        var acael = $(this);
        var fullArray = JSON.parse(woocpGetFullArray());
        //if selected is placeholder return false
        var select = $(acael).closest('.woocp_product_component').find('.woocp_attributes_select');
        if(select.val() === 'placeholder') return false;

        var newVal = parseInt(select.val());
        var componentId = $(acael).closest('.woocp_product_component').attr('data-componentId');
        //if selected exists in full array return false
        var attached = $('input[name="woocp_component_attributes_'+componentId+'"]');
        var attachedArray = JSON.parse(attached.val());
        if(attachedArray == null) attachedArray = {};
        if(newVal in attachedArray) return false;

        $(this).closest('.woocp_add_attribute_field').find('.spinner.woocp_add_attribute').css('visibility','visible');

        $('input[name^="woocp_component_attributes_"]').each(function(){
            var caId = $(this).attr('name').replace('woocp_component_attributes_','');
            var newAr = [];
            var attachedAttr = JSON.parse($(this).val());

            //attach new attribute to component attributes object
            if(caId === componentId && !attachedAttr.includes(newVal)){
                var newValObj = {"id":newVal,"options": []};
                attachedAttr.push(newValObj);
                $('input[name^="woocp_component_attributes_'+componentId+'"]').val(JSON.stringify(attachedAttr));
            }

        });
        var data = {
            'action': 'woocp_add_component_attribute',
            'attributeId': newVal,
        };

        jQuery.post(ajaxurl, data, function(response) {
            $(document).woocpSaveProductComponents();
            $(acael).closest('.woocp_product_component').find('.woocp_component_attributes_list').append(response);
            $('.woocp_select2').select2();
            woocpResetTipTip();
            $('.spinner.woocp_add_attribute').css('visibility','hidden');
        });
    };

    $.fn.woocpDeleteComponentAttribute= function() {
        var confirm = window.confirm('Are you sure you want to remove this attribute?');
        if(confirm){
            var cId = $(this).closest('.woocp_product_component').attr('data-componentId');
            var id = $(this).closest('.woocp_options_select_container').attr('data-attributeId');
            var attachedEl = $('input[name="woocp_component_attributes_'+cId+'"]');

            var attached = JSON.parse(attachedEl.val());
            for(var i=0;i<attached.length;i++){
                if(parseInt(attached[i].id) === parseInt(id)) attached.splice(i,1);
            }
            if(attached == null) attached = [];

            $(this).closest('.woocp_options_select_container').remove();
            $(attachedEl).val(JSON.stringify(attached));
            $(attachedEl).woocpSaveProductComponents();
        }
    };

    $.fn.woocpUpdateAttributeOption = function() {
        var options = $(this).val();
        var cId = $(this).closest('.woocp_product_component').attr('data-componentId');
        var aId = $(this).closest('.woocp_options_select_container').attr('data-attributeId');
        var target = $('input[name^="woocp_component_attributes_'+cId+'"]');
        var targetAr = JSON.parse(target.val());
        if(options !== null){
            $.each(targetAr,function(ind,val){
                if(parseInt(val.id) === parseInt(aId)) val.options = options;
            });
        }
        target.val(JSON.stringify(targetAr));
        woocpGetFullArray();
    };

    $.fn.woocpSortComponents = function() {
        var sortEl = $(this);
        var startIndex = sortEl.attr('data-startindex');
        var newIndex = sortEl.index();
        var compId = sortEl.attr('data-componentid');
        if(startIndex === newIndex) return false;
        //update array and show message
        var sorted = $( '#woocp_product_components_list' ).sortable( 'toArray', {attribute:'data-componentid'});
        for(var i=0;i<sorted.length;i++) sorted[i] = parseInt(sorted[i]);
        $('input[name="_woocp_product_components_order"]').val(JSON.stringify(sorted));
        $('.woocp_message .msg1').fadeIn(aniTime2);
        //update tag list with new order
        var tag = $('#woocp_product_customizer_tag_list .woocp_component_tag[data-componentid="'+compId+'"');
        if(newIndex > startIndex) {
            var prevId = sortEl[0].previousElementSibling.attributes['data-componentid']['value'];
            tag.insertAfter($('#woocp_product_customizer_tag_list .woocp_component_tag[data-componentid="'+prevId+'"'));
        }
        if(newIndex < startIndex) {
            var prevId = sortEl[0].nextElementSibling.attributes['data-componentid']['value'];
            tag.insertBefore($('#woocp_product_customizer_tag_list .woocp_component_tag[data-componentid="'+prevId+'"'));
        }
    };

    $.fn.woocpAttrSortable = function() {
        var target = $(this).find('.woocp_component_attributes_list');
        target.sortable({
            opacity: 0.6,
            cursor: 'move',
            handle: '.sorthandle',
            placeholder: "ui-state-highlight"
        });
    };

    $.fn.woocpSortAttributes = function() {
        var compId = $(this).closest('.woocp_product_component').attr('data-componentid');
        var sorted = $('.woocp_product_component[data-componentid="'+compId+'"] .woocp_component_attributes_list').sortable( 'toArray', {attribute:'data-attributeid'});
        var curOrder = JSON.parse($('input[name="woocp_component_attributes_'+compId+'"]').val());
        $('.woocp_message .msg1').fadeIn(aniTime2);
        var newAr = [];
        $.each(curOrder,function(index,value){
            var atId = value.id;
            $.each(sorted,function(in2,val2){
                if(val2 == atId) newAr[in2] = value;
            });
        });
        $('input[name="woocp_component_attributes_'+compId+'"]').val(JSON.stringify(newAr));
    };



    $(function(){
        $('.woocp_select2').select2();

        $('#_woocp_product_components_select,.woocp_attributes_select').val('placeholder');

        $('#woocp_product_components_list').sortable({
            opacity: 0.6,
            cursor: 'move',
            handle: '.sorthandle',
            placeholder: "ui-state-highlight"
        });
        $('#woocp_product_components_list .woocp_product_component').each(function(){
            $(this).woocpAttrSortable();
        });
    });

    $(document).on('click','.woocp_add_product_component',function(e){
        e.preventDefault();
        $(this).woocpAddProductComponent();
    });
    $(document).on('click','.woocp_save_product_components',function(e){
        e.preventDefault();
        $('.spinner.woocp_save_components').css('visibility','visible');
        $(this).woocpSaveProductComponents();
    });
    $(document).on('click','.woocp_delete_product_component',function(e){
        e.preventDefault();
        $(this).woocpDeleteProductComponent();
    });

    $(document).on('mouseenter','.woocp_options_select_container',function(){
        $(this).find('.woocp_remove_attribute').removeClass('hide');
    });
    $(document).on('mouseleave','.woocp_options_select_container',function(){
        $(this).find('.woocp_remove_attribute').addClass('hide');
    });

    $(document).on('click','.woocp_remove_attribute',function(e){
        e.preventDefault();
        $(this).woocpDeleteComponentAttribute();
    });
    $(document).on('click','.woocp_add_component_attribute',function(e){
        e.preventDefault();
        $(this).woocpAddComponentAttribute();
    });
    $(document).on('click','.woocp_select2_buttons .select_all_options',function(e){
        e.preventDefault();
        $(this).closest('.woocp_options_select_container').find('.woocp_select2 option').prop("selected",true).trigger("change");
    });
    $(document).on('click','.woocp_select2_buttons .clear_all_options',function(e){
        e.preventDefault();
        $(this).closest('.woocp_options_select_container').find('.woocp_select2 option').prop("selected",false).trigger("change");
    });
    $(document).on('change','.woocp_attribute_options_select',function(){
        $(this).woocpUpdateAttributeOption();
        $('.woocp_message .msg1').fadeIn(aniTime2);
    });
    $('#woocp_product_components_list_container').on('click','.woocp_expand_components,.woocp_close_components',function(e){
        e.preventDefault();
        if($(this).hasClass('woocp_expand_components')){
            $('#woocp_product_components_list_container .woocp_product_component.closed').each(function(){
                $(this).find('h3').click();
            });
        }
        else if($(this).hasClass('woocp_close_components')){
            $('#woocp_product_components_list_container .woocp_product_component.open').each(function(){
                $(this).find('h3').click();
            });
        }
    });

    $(document).on('sortstart','#woocp_product_components_list',function(e,ui){
        if(ui.item.hasClass('woocp_product_component')) ui.item.attr('data-startindex',ui.item.index());
    });
    $(document).on('sortupdate','#woocp_product_components_list',function(e,ui){
        if(ui.item.hasClass('woocp_product_component')) ui.item.woocpSortComponents();
        ui.item.woocpSortAttributes();
        woocpGetFullArray();
    });



    function woocpGetFullArray(){
        var fullArray = [];
        var i = 0;
        $('input[name^="woocp_component_attributes_"]').each(function(){
            var caId = $(this).closest('.woocp_product_component').attr('data-componentId');
            var label = $('input[name="component_label_'+caId+'"]').val();
            var fee = $('input[name="component_fee_'+caId+'"]').val();
            var desc = $('textarea[name="woocp_component_description_'+caId+'"]').val().replace(/["]+/g, '“').replace(/[']+/g, '´');
            var newObj = {};
            var attachedAttr = JSON.parse($(this).val());

            //attach whole component object to fullArray
            if(!(caId in fullArray)){
                newObj['id'] = caId;
                newObj['label'] = label;
                newObj['fee'] = fee;
                newObj['description'] = desc;
                newObj['attrs'] = attachedAttr;
                fullArray[i] = newObj;
                i++;
            }
        });
        if(Object.keys(fullArray).length < 1){
            fullArray = JSON.parse($('input[name="_woocp_product_components"]').val());
        }
        fullArray = JSON.stringify(fullArray);
        $('input[name="_woocp_product_components"]').val(fullArray);
        return fullArray;
    }


})( jQuery );
