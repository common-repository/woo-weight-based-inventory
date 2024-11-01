<?php

if (!defined('ABSPATH'))
{
    exit;
}

class SQueue_Wei_Inv_Notifications
{

    function low_stock_notification($product_id,$quantity)
    {
        $product = wc_get_product($product_id);
        if (!in_array('low_stock',get_option( SQ_WEI_INV_SLUG.'_notification', array('low_stock','out_of_stock')))) {
            return;
        }

        $subject = sprintf( '[%s] %s', $this->get_blogname(), __( 'Product low in stock', 'woocommerce' ) );
        $message = sprintf(
                /* translators: 1: product name 2: items in stock */
                __( '%1$s is low in stock. There are %2$d left.', 'woocommerce' ),
                html_entity_decode( strip_tags( $product->get_formatted_name() ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
                html_entity_decode( strip_tags( $quantity ) )
        );

        wp_mail(
            get_option(SQ_WEI_INV_SLUG.'_notification_recipient', get_option('admin_email')),
            $subject,
            $message
        );
    }
    
    function out_of_stock_notification($product_id)
    {
        $product = wc_get_product($product_id);
        if (!in_array('out_of_stock',get_option( SQ_WEI_INV_SLUG.'_notification', array('low_stock','out_of_stock')))) {
            return;
        }
        $subject = sprintf( '[%s] %s', $this->get_blogname(), __( 'Product out of stock', 'woocommerce' ) );
        /* translators: %s: product name */
        $message = sprintf( __( '%s is out of stock.', 'woocommerce' ), html_entity_decode( strip_tags( $product->get_formatted_name() ), ENT_QUOTES, get_bloginfo( 'charset' ) ) );

        wp_mail(
            get_option(SQ_WEI_INV_SLUG.'_notification_recipient', get_option('admin_email')),
            $subject,
            $message
        );
    }

}