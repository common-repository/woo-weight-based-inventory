<?php
if (!defined('ABSPATH'))
{
    exit;
}

function sq_wei_inv_availablity($_product,$avail)
{
    $id = $_product->get_ID();
    $type = $_product->get_type();
    $weight_slug = sq_wei_get_weight_slug();
    switch ($type)
    {
        case 'simple':
            $wei_inv = get_post_meta( $id, '_weight_based_inventory', TRUE );
            $manage = get_post_meta( $id, '_manage_stock', TRUE );
            $wei_quan = get_post_meta( $id, '_inventory_weight', TRUE );
            $weight = get_post_meta( $id, $weight_slug, TRUE );
            $backorders = get_post_meta( $id, '_inventory_weight_backorders', TRUE );
            break;
        default:
            return FALSE;
    }
    if($manage == 'yes')
    {
        return FALSE;
    }
    if($wei_inv != 'yes')
    {
        return FALSE;
    }
    if($avail['class'] == 'out-of-stock')
    {
        return FALSE;
    }
    if(empty($weight))
    {
        return array( 
            'availability' => __('Out of stock','woocommerce'),
            'class' => 'out-of-stock'
        ) ;
    }
    if(empty($wei_quan))
    {
        switch ($backorders)
        {
            case 'no':
                return array( 
                    'availability' => __('Out of stock','woocommerce'),
                    'class' => 'out-of-stock'
                );
            case 'notify':
                return array( 
                    'availability' => __('Available on backorder','woocommerce'),
                    'class' => 'available-on-backorder'
                ) ;
            case 'yes':
                return array( 
                    'availability' => '',
                    'class' => 'available-on-backorder'
                ) ;
        }
    }
    $quantity = 0;
    $weight_type = get_option(SQ_WEI_INV_SLUG.'_weight_type','predefined');
    if($weight_type == 'predefined')
    {
        if($wei_quan !=0 && $weight != 0)
        {
            $quantity = floor((floatval($wei_quan)/floatval($weight)));
        }
    }
    else
    {
        $quantity = $wei_quan;
    }
    if($quantity <= 0)
    {
        switch ($backorders)
        {
            case 'no':
                return array( 
                    'availability' => __('Out of stock','woocommerce'),
                    'class' => 'out-of-stock'
                );
            case 'notify':
                return array( 
                    'availability' => __('Available on backorder','woocommerce'),
                    'class' => 'available-on-backorder'
                ) ;
            case 'yes':
                return array( 
                    'availability' => '',
                    'class' => 'available-on-backorder'
                ) ;
        }
        
    }
    else
    {
        $weight_type = get_option(SQ_WEI_INV_SLUG.'_weight_type','predefined');
        $stock_display_format = get_option(SQ_WEI_INV_SLUG.'_stock_display_format','basic');
        if($weight_type == 'variable' && $stock_display_format == 'basic')
        {
            $stock_display_format = 'weight';
        }
        switch ($stock_display_format)
        {
            case '':
                $formatted = '';
                break;
            case 'basic':
                $formatted = $quantity.' '.__('in stock',SQ_WEI_INV_SLUG);
                break;
            case 'weight':
                $formatted = $wei_quan.' '.get_option( 'woocommerce_weight_unit' ).' '.__('in stock',SQ_WEI_INV_SLUG);
                break;
            case 'custom':
                $custom_format_text = get_option(SQ_WEI_INV_SLUG.'_custom_format_text','');
                if($weight_type != 'variable')
                {
                    $formatted = str_replace('[{quantity}]', $quantity, $custom_format_text);
                }
                $formatted = str_replace('[{stock}]', $wei_quan, $formatted);
                $formatted = str_replace('[{unit}]', get_option( 'woocommerce_weight_unit' ), $formatted);
                break;
        }
        return array(
            'availability' => $formatted,
            'class' => 'in-stock'
        ) ;
    }
}

function sq_wei_inv_stock_status($_product,$status)
{
    $id = $_product->get_ID();
    $type = $_product->get_type();
    switch ($type)
    {
        case 'simple':
            $wei_inv = get_post_meta( $id, '_weight_based_inventory', TRUE );
            $manage = get_post_meta( $id, '_manage_stock', TRUE );
            $wei_quan = get_post_meta( $id, '_inventory_weight', TRUE );
            $weight = get_post_meta( $id, sq_wei_get_weight_slug(), TRUE );
            break;
        default:
            return FALSE;
    }
    if($manage == 'yes')
    {
        return FALSE;
    }
    if($wei_inv != 'yes')
    {
        return FALSE;
    }
    if($status == 'outofstock')
    {
        return FALSE;
    }
    if(empty($wei_quan))
    {
        return 'outofstock';
    }
    if(empty($weight))
    {
        return 'outofstock';
    }
    $quantity = 0;
    $weight_type = get_option(SQ_WEI_INV_SLUG.'_weight_type','predefined');
    if($weight_type == 'predefined')
    {
        if($wei_quan !=0 && $weight != 0)
        {
            $quantity = floor((floatval($wei_quan)/floatval($weight)));
        }
    }
    else
    {
        $quantity = $wei_quan;
    }
    if($quantity <= 0)
    {
        return 'outofstock';
    }
    else
    {
        return 'instock';
    }
}

function sq_wei_inv_def_order_status_actions()
{
    return array(
        'pending'    => 'nothing',
        'processing' => 'reduce',
        'on-hold'    => 'nothing',
        'completed'  => 'reduce',
        'cancelled'  => 'nothing',
        'refunded'   => 'return',
        'failed'     => 'nothing',
    );
}

function sq_wei_get_weight_slug()
{
    $weight_slug = '_weight';
    return $weight_slug;
}

function sq_wei_inv_is_weight_based($product)
{
    $wei_inv = get_post_meta( $product, '_weight_based_inventory', TRUE );
    if($wei_inv != 'yes')
    {
        return FALSE;
    }
    else 
    {
        return 'simple';
    }
}