jQuery(function () {
    jQuery('.sq_wei_inv_settings_wrap').on('change','#stock_display_format',function(){
        var val = jQuery(this).val();
        if(val == 'custom')
        {
            jQuery('.custom_format_text').show();
        }
        else
        {
            jQuery('.custom_format_text').hide();
        }
    });
    jQuery('table.sq_wei_inv_products').on('click','.inventory_edit_action',function(){
        var id = jQuery(this).prop('id');
        if(jQuery(this).hasClass('edit_inv'))
        {
            jQuery(this).removeClass('edit_inv');
            jQuery(this).addClass('cancel_inv');
            jQuery(this).html('Cancel');
            var inventory = jQuery("#inventory_raw_"+id).val();
            var html = '<form method="POST" action="#">';
                html+= '<input type="hidden" name="product_id" value="'+id+'">';
                html+= '<input type="hidden" name="sq_wei_inv_smart_action" value="yes">';
                html+= '<input type="number" name="inventory_weight" step="any" style="width:100%;" value="'+inventory+'">';
                html+= '</form>';
            jQuery("#edit_inventory_"+id+"_html").html(html);
        }
        else
        {
            jQuery(this).removeClass('cancel_inv');
            jQuery(this).addClass('edit_inv');
            jQuery(this).html('Edit Inventory');
            jQuery("#edit_inventory_"+id+"_html").html('');
        }
    });
    jQuery('table.sq_wei_inv_products').on('click','.weight_edit_action',function(){
        var id = jQuery(this).prop('id');
        if(jQuery(this).hasClass('edit_wei'))
        {
            jQuery(this).removeClass('edit_wei');
            jQuery(this).addClass('cancel_wei');
            jQuery(this).html('Cancel');
            var weight = jQuery("#weight_raw_"+id).val();
            var html = '<form method="POST" action="#">';
                html+= '<input type="hidden" name="product_id" value="'+id+'">';
                html+= '<input type="hidden" name="sq_wei_inv_smart_action" value="yes">';
                html+= '<input type="number" name="product_weight" step="any" style="width:100%;" value="'+weight+'">';
                html+= '</form>';
            jQuery("#edit_weight_"+id+"_html").html(html);
        }
        else
        {
            jQuery(this).removeClass('cancel_wei');
            jQuery(this).addClass('edit_wei');
            jQuery(this).html('Edit Weight');
            jQuery("#edit_weight_"+id+"_html").html('');
        }
    });
    jQuery('.sq_wei_inv_settings_wrap').on('change','#weight_type',function(){
        var val = jQuery("input[name='weight_type']:checked").val();
        if(val == 'predefined')
        {
            jQuery('#product_weight').closest('tr').show();
            jQuery("#stock_display_format option[value='basic']").removeAttr('disabled');
            jQuery("#stock_display_format").val(jQuery('#stock_display_format_old').val());
            jQuery('#stock_display_format').select2({minimumResultsForSearch: -1,width: '50%'});
        }
        else
        {
            jQuery('#product_weight').closest('tr').hide();
            jQuery("#stock_display_format option[value='basic']").attr('disabled', 'disabled' );
            jQuery("#stock_display_format").val('weight');
            jQuery('#stock_display_format').select2({minimumResultsForSearch: -1,width: '50%'});
        }
    });
});

jQuery(function () {
    jQuery('#stock_display_format').trigger('change');
    jQuery('#weight_type').trigger('change');
    jQuery("select").each(function () {
        jQuery(this).select2({minimumResultsForSearch: -1});
    });
    jQuery("table.products tr").each(function () {
        if (jQuery(this).find('.avail_inventory').html() == 'Not Set' || (jQuery(this).find('.avail_weight').html() == 'Not Set' && jQuery(this).children('.column-type').html() != 'Variable'))
        {
            jQuery(this).addClass('not_set_alert');
        }
        else
        {
            jQuery(this).removeClass('not_set_alert');
        }
    });
    
                            
});