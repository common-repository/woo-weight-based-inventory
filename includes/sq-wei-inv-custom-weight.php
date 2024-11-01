<?php
if (!defined('ABSPATH'))
{
    exit;
}
class SQueue_Custom_Weight_Processor
{

    function __construct()
    {
        add_action( 'woocommerce_before_add_to_cart_button', array($this,'add_weight_field'), 10 );
        add_filter( 'woocommerce_add_cart_item_data', array($this,'add_weight_to_cart'), 10, 3 );
        add_filter( 'woocommerce_get_item_data', array($this,'display_item_data'), 10, 2 );
        add_action( 'woocommerce_checkout_create_order_line_item', array($this,'add_weight_to_order'), 10, 4 );
        add_action('woocommerce_cart_contents',array($this,'change_product_weight_on_cart'));
        //add_filter('woocommerce_order_get_items',array($this,'change_product_weight_on_order_items'),99,3);
        add_filter('woocommerce_order_item_display_meta_key', array($this,'change_order_item_display_key'),10,3);
        add_filter('woocommerce_order_item_display_meta_value', array($this,'change_order_item_display_value'),10,3);
        add_action( 'woocommerce_before_calculate_totals', array($this,'do_price_weight_change'), 99, 1);
        add_filter('woocommerce_update_cart_validation', array($this,'do_cart_validation'),10,4);
        add_filter('woocommerce_order_item_product',array($this,'update_line_item_weight'),10,2);
        add_action('woocommerce_cart_totals_before_order_total', array($this,'show_total_weight'));
        add_filter( 'woocommerce_loop_add_to_cart_link', array($this,'replacing_add_to_cart_button'), 10, 2 );
    }
            
    function add_weight_field()
    {
        global $product;
        $product_id = $product->get_id();
        $product_type = $product->get_type();
        $weight_limit = 0;
        $is_product_weight_based = FALSE;
        if($product_type == 'simple')
        {
            $is_product_weight_based = sq_wei_inv_is_weight_based($product_id);
            $weight_limit = get_post_meta( $product_id, '_inventory_weight', TRUE );
        }
        if($is_product_weight_based)
        {
            $title = get_post_meta($product_id, '_weight_textbox_title', TRUE);
            if(!$title)
            {
                $title = __('Required Weight',SQ_WEI_INV_SLUG);
            }
            $placeholder = get_post_meta($product_id, '_weight_textbox_placeholder', TRUE);
            if(!$placeholder)
            {
                $placeholder = __('Enter your required Weight',SQ_WEI_INV_SLUG);
            }
            ?>
            <p class="form-row" id="sq_wei_inv_weight_p">
                <label for="sq_wei_inv_weight">
                    <?php echo $title; ?> <?php echo ' ( '.get_option( 'woocommerce_weight_unit' ).' ) '; ?>
                    <span class="required">*</span>
                </label>
                <span class="woocommerce-input-wrapper">
                    <input type="number" class="input-text" step="any" required title="<?php echo $title; ?>" name="sq_wei_inv_weight" max="<?php echo $weight_limit; ?>" style="width: 50%;" id="sq_wei_inv_weight" placeholder="<?php echo $placeholder; ?>">
                </span>
            </p>
            <script>
                var currency_position = "<?php echo get_option('woocommerce_currency_pos'); ?>";
                var decimal_places = <?php echo get_option('woocommerce_price_num_decimals'); ?>;
                var decimal_separator = "<?php echo get_option('woocommerce_price_decimal_sep'); ?>";
                function addCommas(nStr) {
                    nStr += '';
                    x = nStr.split('.');
                    x1 = x[0];
                    x2 = x.length > 1 ? decimal_separator + x[1] : '';
                    var rgx = /(\d+)(\d{3})/;
                    while (rgx.test(x1)) {
                        x1 = x1.replace(rgx, '$1' + '<?php echo get_option('woocommerce_price_thousand_sep'); ?>' + '$2');
                    }
                    return x1 + x2;
                }
                <?php
                if($product_type == 'simple')
                {
                    ?>
                    var product_price = <?php echo $product->get_price();?>;
                    var product_is_sale_price = "<?php echo ((!empty($product->get_sale_price()))?'yes':'no');?>";
                    var product_regular_price = <?php echo $product->get_regular_price();?>;
                    jQuery('.product').on('keyup','#sq_wei_inv_weight',function(){
                        var weight = jQuery(this).val();
                        var altered_price;
                        var altered_regular;
                        if(weight != '')
                        {
                            altered_price = addCommas((parseFloat(product_price * weight).toFixed(decimal_places)).replace(decimal_separator, '.'));
                            altered_regular = addCommas((parseFloat(product_regular_price * weight).toFixed(decimal_places)).replace(decimal_separator, '.'));
                        }
                        else
                        {
                            altered_price = addCommas((parseFloat(product_price).toFixed(decimal_places)).replace(decimal_separator, '.'));
                            altered_regular = addCommas((parseFloat(product_regular_price).toFixed(decimal_places)).replace(decimal_separator, '.'));
                        }
                        if(product_is_sale_price == 'yes')
                        {
                            switch(currency_position)
                            {
                                case 'left':
                                    jQuery('p.price del span.amount').get(0).lastChild.nodeValue = altered_regular;
                                    jQuery('p.price ins span.amount').get(0).lastChild.nodeValue = altered_price;
                                    break;
                                case 'right':
                                    jQuery('p.price del span.amount').get(0).firstChild.nodeValue = altered_regular;
                                    jQuery('p.price ins span.amount').get(0).firstChild.nodeValue = altered_price;
                                    break;
                                case 'left_space':
                                    jQuery('p.price del span.amount').get(0).lastChild.nodeValue = altered_regular+' ';
                                    jQuery('p.price ins span.amount').get(0).lastChild.nodeValue = altered_price+' ';
                                    break;
                                case 'right_space':
                                    jQuery('p.price del span.amount').get(0).firstChild.nodeValue = altered_regular+' ';
                                    jQuery('p.price ins span.amount').get(0).firstChild.nodeValue = altered_price+' ';
                                    break;
                            }
                        }
                        else
                        {
                            switch(currency_position)
                            {
                                case 'left':
                                    jQuery('p.price .amount').get(0).lastChild.nodeValue = altered_price;
                                    break;
                                case 'right':
                                    jQuery('p.price .amount').get(0).firstChild.nodeValue = altered_price;
                                    break;
                                case 'left_space':
                                    jQuery('p.price .amount').get(0).lastChild.nodeValue = altered_price+' ';
                                    break;
                                case 'right_space':
                                    jQuery('p.price .amount').get(0).firstChild.nodeValue = altered_price+' ';
                                    break;
                            }
                        }
                    });
                    <?php
                }
                ?>
            </script>
            <?php
        }
    }
    
    function add_weight_to_cart($cart_item_data, $product_id, $variation_id )
    {
	$weight = filter_input( INPUT_POST, 'sq_wei_inv_weight' );
	if ( empty( $weight ) ) {
		return $cart_item_data;
	}
	$cart_item_data['sq_wei_inv_weight'] = $weight;
        $product_data = wc_get_product( $variation_id ? $variation_id : $product_id );
        if($product_data->get_type() == 'simple')
        {
            $price = $product_data->get_price();
            $cart_item_data['sq_wei_inv_weight_old_price'] = $price;
            $cart_item_data['sq_wei_inv_weight_converted_price'] = $weight * $price;
        }
	return $cart_item_data;
    }
    
    function do_cart_validation($validation,$cart_item_key, $values, $quantity)
    {
        $cart = WC()->cart;
        $item = $cart->get_cart_item($cart_item_key);
        if(isset($item['sq_wei_inv_weight']) && !empty($item['sq_wei_inv_weight']))
        {
            return false;
        }
        return $validation;
    }
    
    function display_item_data($item_data, $cart_item)
    {
        if ( empty( $cart_item['sq_wei_inv_weight'] ) ) {
            return $item_data;
        }
        $product_id = $cart_item['product_id'];
        $title = get_post_meta($product_id, '_weight_textbox_title', TRUE);
        if(!$title)
        {
            $title = __('Required Weight',SQ_WEI_INV_SLUG);
        }
        $item_data[] = array(
            'key'     => $title,
            'value'   => wc_clean( $cart_item['sq_wei_inv_weight'] ).' '.get_option( 'woocommerce_weight_unit' ),
            'display' => '',
        );
        $item_data[] = array(
            'key'     => __('Price for',SQ_WEI_INV_SLUG).' 1'.' '.get_option( 'woocommerce_weight_unit' ),
            'value'   => wc_clean( wc_price($cart_item['sq_wei_inv_weight_old_price'])),
            'display' => ''
        );
        return $item_data;
    }
    
    function add_weight_to_order( $item, $cart_item_key, $values, $order ) {
        if ( empty( $values['sq_wei_inv_weight'] ) ) {
            return;
        }
        $item->add_meta_data( 'sq_wei_inv_weight', $values['sq_wei_inv_weight'] );
    }
    
    function change_product_weight_on_order_items($items, $order, $types)
    {
        foreach($items as $key => $item)
        {
            if ( ! $item->is_type( 'line_item' ) ) 
            {
                continue;
            }
            $weight = $item->get_meta('sq_wei_inv_weight');
            if(!empty($weight))
            {
                $pro = $item->get_product();
                if($pro->get_type() == 'simple')
                {
                    $pro->set_weight($weight);
                    $pro->apply_changes();
                }
            }
        }
        return $items;
    }
    
    function change_product_weight_on_cart()
    {
        $cart = wc()->cart;
        foreach ($cart->get_cart_contents() as $value)
        {
            if(isset($value['sq_wei_inv_weight']) && !empty($value['sq_wei_inv_weight']))
            {
                $pro = $value['data'];
                $pro->set_weight($value['sq_wei_inv_weight']);
                $pro->apply_changes();
            }
        }
    }
    
    function change_order_item_display_key($display_key, $meta, $item)
    {
        if ( ! $item->is_type( 'line_item' ) ) 
        {
            return $display_key;
        }
        $product_id = $item->get_product_id();
        if($display_key == 'sq_wei_inv_weight')
        {
            $display_key = get_post_meta($product_id, '_weight_textbox_title', TRUE);
            if(!$display_key)
            {
                $display_key = __('Required Weight',SQ_WEI_INV_SLUG);
            }
        }
        return $display_key;
    }
    
    function change_order_item_display_value($display_value, $meta, $item)
    {
        if ( ! $item->is_type( 'line_item' ) ) 
        {
            return $display_value;
        }
        if($meta->key == 'sq_wei_inv_weight')
        {
            $display_value .= ' '.get_option( 'woocommerce_weight_unit' ).' ';
        }
        return $display_value;
    }
    
    function do_price_weight_change($cart)
    {
        if ( (is_admin() && ! defined( 'DOING_AJAX' ) ) || $cart->is_empty() )
        {
            return;
        }
        foreach ( $cart->get_cart() as $item ) 
        {
            if(isset($item['sq_wei_inv_weight']) && !empty($item['sq_wei_inv_weight']))
            {
                $item['data']->set_price( $item['sq_wei_inv_weight_converted_price'] );
                $item['data']->set_weight( $item['sq_wei_inv_weight'] );
            }
        }
    }
    
    function update_line_item_weight($product,$item)
    {
        $weight =  $item->get_meta('sq_wei_inv_weight');
        if(!empty($weight) && $product->get_type() == "simple")
        {
            $product->set_weight($weight);
            $product->apply_changes();
        }
        return $product;
    }
    
    function show_total_weight()
    {
	echo '<tr>';
	echo '<th>' . __('Total Weight:', 'woocommerce').'</th>';
	echo '<td><b>' . wc()->cart->cart_contents_weight . ' ' . get_option('woocommerce_weight_unit').'</b></td>';
	echo '</tr>';
    }

    function replacing_add_to_cart_button($button, $product)
    {
        if($product->get_type() == 'simple')
        {
            $id = $product->get_id();
            $is_weight_based = sq_wei_inv_is_weight_based($id);
            if($is_weight_based)
            {
                $button_text = apply_filters('sq_wei_inv_add_to_cart_text',__("View product", "woocommerce"));
                $button = '<a class="button" href="' . $product->get_permalink() . '">' . $button_text . '</a>';
            }
        }
        return $button;
    }
    
}

$manage_inventory = get_option(SQ_WEI_INV_SLUG.'_manage_inventory','both');
$weight_type = get_option(SQ_WEI_INV_SLUG.'_weight_type','predefined');
if($manage_inventory != '' && $weight_type == 'variable')
{
    new SQueue_Custom_Weight_Processor();
}