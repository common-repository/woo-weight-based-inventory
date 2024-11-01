<?php

if (!defined('ABSPATH'))
{
    exit;
}

class SQueue_Wei_Inv_Order_Process
{
    protected $notification;
    function __construct()
    {
        add_action('woocommerce_order_status_changed', array($this, 'status_change'), 10, 4);
        if(get_option(SQ_WEI_INV_SLUG.'_weight_type','predefined') == 'predefined')
        {
            add_filter('woocommerce_product_get_weight', array($this, 'get_weight'), 10, 2);
        }
        $this->notification = new SQueue_Wei_Inv_Notifications();
    }
    
    function get_weight($weight,$product)
    {
        $product_id = $product->get_id();
        $is_weight_based = sq_wei_inv_is_weight_based($product_id);
        if($is_weight_based)
        {
            $weight = get_post_meta($product_id, sq_wei_get_weight_slug(), TRUE);
            return $weight;
        }
        return $weight;
    }

    function status_change($order_id, $from_status, $to_status, $order)
    {
        $order_status = get_option(SQ_WEI_INV_SLUG.'_order_status_actions', sq_wei_inv_def_order_status_actions());
        $actions = (isset($order_status[$to_status]) ? $order_status[$to_status] : 'nothing');
        $factions = (isset($order_status[$from_status]) ? $order_status[$from_status] : 'nothing');
        $low_stock_threshold = get_option(SQ_WEI_INV_SLUG.'_low_stock_threshold','2');
        $out_of_stock_threshold = get_option(SQ_WEI_INV_SLUG.'_out_of_stock_threshold','0');
        $is_variable_weight = ((get_option(SQ_WEI_INV_SLUG.'_weight_type','predefined') == 'variable')?TRUE:FALSE);
        switch ($factions)
        {
            case 'reduce':
                switch ($actions)
                {
                    case 'nothing':
                    case 'return':
                        $items = $order->get_items();
                        foreach ($items as $item)
                        {
                            if ( ! $item->is_type( 'line_item' ) ) {
                                continue;
                            }
                            $product_id = $item->get_product_id();
                            $variation_id = $item->get_variation_id();
                            $product_quantity = $item->get_quantity();
                            $product = $item->get_product();
                            $weight = $product->get_weight();
                            $is_weight_based = sq_wei_inv_is_weight_based($product_id, $variation_id);
                            if ($is_weight_based)
                            {
                                switch ($is_weight_based)
                                {
                                    case 'simple':
                                        $inventory = get_post_meta($product_id, '_inventory_weight', TRUE);
                                        $remaining = $inventory + ($product_quantity * $weight);
                                        update_post_meta($product_id, '_inventory_weight', $remaining);
                                        if($is_variable_weight)
                                        {
                                            $quantity = $remaining;
                                        }
                                        else
                                        {
                                            $quantity = floor((floatval($remaining) / floatval($weight)));
                                        }
                                        if($quantity <= $low_stock_threshold)
                                        {
                                            $this->notification->low_stock_notification($product_id, $quantity);
                                        }
                                        if($quantity <= $out_of_stock_threshold)
                                        {
                                            $this->notification->out_of_stock_notification($product_id);
                                        }
                                        if ($quantity <= 0)
                                        {
                                            $backorders = get_post_meta( $product_id, '_inventory_weight_backorders', TRUE );
                                            if($backorders != 'no')
                                            {
                                                do_action(
							'woocommerce_product_on_backorder', array(
								'product'  => $product,
								'order_id' => $order_id,
								'quantity' => $product_quantity,
							)
						);
                                                update_post_meta($product_id, '_stock_status', 'onbackorder');
                                            }
                                            else
                                            {
                                                update_post_meta($product_id, '_stock_status', 'outofstock');
                                            }
                                        }
                                        else
                                        {
                                            update_post_meta($product_id, '_stock_status', 'instock');
                                        }
                                        break;
                                }
                            }
                        }
                        break;
                }
                break;
            case 'return':
            case 'nothing':
                switch ($actions)
                {
                    case 'reduce':
                        $items = $order->get_items();
                        foreach ($items as $item)
                        {
                            if ( ! $item->is_type( 'line_item' ) ) {
                                continue;
                            }
                            $product_id = $item->get_product_id();
                            $variation_id = $item->get_variation_id();
                            $product_quantity = $item->get_quantity();
                            $product = $item->get_product();
                            $weight = $product->get_weight();
                            $is_weight_based = sq_wei_inv_is_weight_based($product_id, $variation_id);
                            if ($is_weight_based)
                            {
                                switch ($is_weight_based)
                                {
                                    case 'simple':
                                        $inventory = get_post_meta($product_id, '_inventory_weight', TRUE);
                                        $remaining = $inventory - ($product_quantity * $weight);
                                        update_post_meta($product_id, '_inventory_weight', $remaining);
                                        if($is_variable_weight)
                                        {
                                            $quantity = $remaining;
                                        }
                                        else
                                        {
                                            $quantity = floor((floatval($remaining) / floatval($weight)));
                                        }
                                        if($quantity <= $low_stock_threshold)
                                        {
                                            $this->notification->low_stock_notification($product_id, $quantity);
                                        }
                                        if($quantity <= $out_of_stock_threshold)
                                        {
                                            $this->notification->out_of_stock_notification($product_id);
                                        }
                                        if ($quantity <= 0)
                                        {
                                            $backorders = get_post_meta( $product_id, '_inventory_weight_backorders', TRUE );
                                            if($backorders != 'no')
                                            {
                                                do_action(
							'woocommerce_product_on_backorder', array(
								'product'  => $product,
								'order_id' => $order_id,
								'quantity' => $product_quantity,
							)
						);
                                                update_post_meta($product_id, '_stock_status', 'onbackorder');
                                            }
                                            else
                                            {
                                                update_post_meta($product_id, '_stock_status', 'outofstock');
                                            }
                                        }
                                        else
                                        {
                                            update_post_meta($product_id, '_stock_status', 'instock');
                                        }
                                        break;
                                }
                            }
                        }
                        break;
                }
                break;
        }
    }

}

$manage_inventory = get_option(SQ_WEI_INV_SLUG . '_manage_inventory', 'both');
if ($manage_inventory != '')
{
    new SQueue_Wei_Inv_Order_Process();
}