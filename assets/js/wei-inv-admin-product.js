jQuery(function () {
    jQuery("#woocommerce-product-data").on("change", "#weight_based_inventory", function (e) {
        var value = jQuery(this).is(':checked');
        if(value)
        {
            jQuery( '.weight_based_inventory_fields' ).show();
            var manage_stock = jQuery( '#_manage_stock' ).is(':checked');
            if(manage_stock)
            {
                jQuery( '#_manage_stock' ).trigger('click');
            }
        }
        else
        {
            jQuery( '.weight_based_inventory_fields' ).hide();
        }
    });
    jQuery("#woocommerce-product-data").on("click", "li.sq_wei_inv_tab a", function (e) {
        jQuery( '#woocommerce-product-data' ).block({
                message: null,
                overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                }
        });
        var data = {};
        data.action          = 'sq_wei_get_settings';
        data.security        = jQuery('#sq_wei_inv_nonce').val();
        data.product_id      = woocommerce_admin_meta_boxes_variations.post_id;
        data.product_type    = jQuery( '#product-type' ).val();
        jQuery.ajax({
                url: woocommerce_admin_meta_boxes_variations.ajax_url,
                data: data,
                type: 'POST',
                success: function( response ) {
                    jQuery('.sq_wei_inv_product_data_contents').html(response);
                    jQuery( '#weight_based_inventory' ).trigger('change');
                    jQuery(".variable_weight_based_inventory_check").each(function () {
                        var parent = jQuery('#weight_based_inventory').is(':checked');
                        if(parent)
                        {
                            jQuery(this).prop('readonly','readonly');
                            jQuery(this).trigger('change');
                        }
                        else
                        {
                            jQuery(this).trigger('change');
                        }
                    });
                    jQuery( document.body ).trigger( 'init_tooltips' );
                    jQuery( '#woocommerce-product-data' ).unblock();
                }
        });
    });
});
