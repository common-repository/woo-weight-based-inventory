<?php
if (!defined('ABSPATH'))
{
    exit;
}
class SQueue_Wei_Inv_Product_Settings
{

    function __construct()
    {
        add_filter('woocommerce_product_data_tabs', array($this, 'add_inventory_data_tab'));
        add_action('woocommerce_product_data_panels', array($this, 'inventory_product_data_fields'));
        add_action( 'woocommerce_process_product_meta', array($this, 'save_inventory_fields') );
        add_action( 'wp_ajax_sq_wei_get_settings', array($this, 'get_settings') );
        add_filter( 'manage_edit-product_columns', array($this,'add_columns'));
        add_filter( 'manage_edit-product_sortable_columns', array($this,'sortable_columns'),10,1 );
        add_action( 'manage_product_posts_custom_column', array($this,'column_details'), 10, 2 );
        add_action('pre_get_posts', array($this,'orderby_columns'));
        add_filter( 'woocommerce_get_availability',array($this,'availability_check'), 99, 2);
        add_filter('woocommerce_product_is_in_stock',array($this,'check_stock_status'),99,2);
    }
    
    function add_inventory_data_tab($product_data_tabs)
    {
        global $post;
        $product = wc_get_product($post->ID);
        $type = $product->get_type();
        if($type == 'simple')
        {
            $product_data_tabs['sq_wei_inv'] = array(
                'label' => __('Weight Inventory', SQ_WEI_INV_SLUG),
                'priority' => 100,
                'class'    => array( 'show_if_simple','hide_if_virtual', 'hide_if_downloadable'),
                'target' => 'sq_wei_inv_product_data'
            );
        }
        return $product_data_tabs;
    }
    
    function inventory_product_data_fields()
    {
        global $post;
        $product = wc_get_product($post->ID);
        $type = $product->get_type();
        if($type == 'simple')
        {
            echo "<div id ='sq_wei_inv_product_data' class ='panel woocommerce_options_panel'> ";
            wp_nonce_field( "sq_wei_inv_nonce",'sq_wei_inv_nonce');
            echo '<div class="sq_wei_inv_product_data_contents">';
            $this->get_simple_settings($post->ID);
            echo '</div></div>';
        }
    }
    
    function get_settings()
    {
        ob_start();
        check_ajax_referer( 'sq_wei_inv_nonce', 'security' );

        if ( ! current_user_can( 'edit_products' ) || empty( $_POST ) || empty( $_POST['product_id'] ) ) {
                wp_die( -1 );
        }
        
        $product_id = absint( $_POST['product_id'] );
        $type = sanitize_text_field($_POST['product_type']);
        if($type == 'simple')
        {
            $this->get_simple_settings($product_id);
        }
        wp_die();
    }
    
    function get_simple_settings($id)
    {
        echo '<div class="sq_wei_inv_simple_options">';
            woocommerce_wp_checkbox(
                array(
                    'id' => 'weight_based_inventory',
                    'name' => 'weight_based_inventory',
                    'value' => get_post_meta($id, '_weight_based_inventory', TRUE),
                    'label' => __('Weight based inventory', SQ_WEI_INV_SLUG),
                    'description' => __('Enable weight based inventory management at product level', SQ_WEI_INV_SLUG),
                    'desc_tip' => true
                )
            );
            if(get_option(SQ_WEI_INV_SLUG.'_weight_type','predefined') == 'variable')
            {
                echo '<p style="font-size: 1.2em;color: #da0aa0;border: 1px solid #ddd;margin: 10px;text-align: justify;">';
                echo __('Make sure to give your regular and sale price for ',SQ_WEI_INV_SLUG).' ( '.__('one',SQ_WEI_INV_SLUG).' '.get_option( 'woocommerce_weight_unit' ).' ) '.__('weight. ',SQ_WEI_INV_SLUG);
                echo __('We will calculate the customer buying weight price based on the regular and sale price for one',SQ_WEI_INV_SLUG).' '.get_option( 'woocommerce_weight_unit' ).' '.__(' weight.',SQ_WEI_INV_SLUG);
                echo '</p>';
                woocommerce_wp_text_input(
                    array
                    (
                        'id' => 'weight_textbox_title',
                        'name' => 'weight_textbox_title',
                        'label' => __('Custom Weight Field Title', SQ_WEI_INV_SLUG),
                        'desc_tip' => TRUE,
                        'description' => __("Enter the Title for the Weight Field", SQ_WEI_INV_SLUG),
                        'type' => 'text',
                        'value' => get_post_meta($id, '_weight_textbox_title', TRUE)
                    )
                );
                woocommerce_wp_text_input(
                    array
                    (
                        'id' => 'weight_textbox_placeholder',
                        'name' => 'weight_textbox_placeholder',
                        'label' => __('Custom Weight Field Placeholder', SQ_WEI_INV_SLUG),
                        'desc_tip' => TRUE,
                        'description' => __("Enter the placeholder for the Weight Field", SQ_WEI_INV_SLUG),
                        'type' => 'text',
                        'value' => get_post_meta($id, '_weight_textbox_placeholder', TRUE)
                    )
                );
            }
            echo '<div class="weight_based_inventory_fields" style="display: none;">';
                woocommerce_wp_text_input(
                    array
                    (
                        'id' => 'inventory_weight',
                        'name' => 'inventory_weight',
                        'class' => 'short',
                        'label' => __('Inventory Weight', SQ_WEI_INV_SLUG).' ( '.get_option( 'woocommerce_weight_unit' ).' ) ',
                        'desc_tip' => TRUE,
                        'custom_attributes' => array(
                            'step' => 'any',
                        ),
                        'description' => __("Enter a quantity to enable inventory weight management at product level.", SQ_WEI_INV_SLUG),
                        'type' => 'number',
                        'value' => get_post_meta($id, '_inventory_weight', TRUE)
                    )
                );
                woocommerce_wp_select(
                    array
                    (
                        'id' => "inventory_weight_backorders",
                        'name' => "inventory_weight_backorders",
                        'value' => get_post_meta($id, '_inventory_weight_backorders', TRUE),
                        'label' => __('Weight Allow backorders?', SQ_WEI_INV_SLUG),
                        'options' => wc_get_product_backorder_options(),
                        'desc_tip' => TRUE,
                        'description' => __('If managing stock, this controls whether or not backorders are allowed. If enabled, weight quantity can go below 0.', SQ_WEI_INV_SLUG),
                    )
                );
                echo '<p style="font-size: 1.2em;color: #da0aa0;border: 1px solid #ddd;margin: 10px;text-align: justify;">';
                echo __('Make sure your stock status are correct. ',SQ_WEI_INV_SLUG);
                echo __('We are handling it programmatically for reducing the stock. But Stock status update is completely manual process. So make sure stock status is instock.',SQ_WEI_INV_SLUG);
                echo '</p>';
            echo '</div>';
        echo '</div>';
    }
    
    function save_inventory_fields($post_id)
    {
        $product = wc_get_product($post_id);
        if($product->get_type() == 'simple')
        {               
            if (isset( $_POST['weight_based_inventory']) ) 
            {
                update_post_meta( $post_id, '_weight_based_inventory', esc_attr( $_POST['weight_based_inventory'] ) );
                update_post_meta( $post_id, '_inventory_weight', esc_attr( $_POST['inventory_weight']) );
                update_post_meta( $post_id, '_inventory_weight_backorders', esc_attr( $_POST['inventory_weight_backorders']) );
            } 
            else 
            {
                update_post_meta( $post_id, '_weight_based_inventory', 'no' );
            }
        }
        if(get_option(SQ_WEI_INV_SLUG.'_weight_type','variable'))
        {
            update_post_meta( $post_id, '_weight_textbox_title', esc_attr( $_POST['weight_textbox_title'] ) );
            update_post_meta( $post_id, '_weight_textbox_placeholder', esc_attr( $_POST['weight_textbox_placeholder'] ) );
        }
    }
    
    function add_columns($columns)
    {
        $keys = array_keys( $columns );
	$index = array_search( 'sku', $keys );
	$pos = FALSE === $index ? count( $columns ) : $index + 1;
        $new = array('weight_inventory' => __('Weight Inventory',SQ_WEI_INV_SLUG));
	return array_merge( array_slice( $columns, 0, $pos ), $new, array_slice( $columns, $pos ) );
    }
    
    function sortable_columns($columns)
    {
        $columns['weight_inventory'] = 'weight_inventory';
        return $columns;
    }
    
    function orderby_columns($query)
    {
        if ( !is_admin() )
        { 
            return;
        }
        if(!isset($_GET['post_type']) && $_GET['post_type'] != 'product') {	
            return;
        }
        $orderby = $query->get( 'orderby');
        if ('weight_inventory' == $orderby)
        {
            $query->set('meta_key','_weight_based_inventory');
            $query->set('orderby','meta_value');
        }
    }
    
    function availability_check($availability, $_product)
    {   
        $avail = sq_wei_inv_availablity($_product, $availability);
        if($avail)
        {
            return $avail;
        }
        else
        {
            return $availability;
        }
    }
    
    function check_stock_status($status,$_product)
    {
        if($status == NULL)
        {
            return $status;
        }
        elseif($status)
        {
            $status = 'instock';
        }
        else
        {
            $status = 'outofstock';
        }
        $status_check = sq_wei_inv_stock_status($_product, $status);
        if($status_check)
        {
            return 'outofstock' !== $status_check;
        }
        else
        {
            return $status;
        }
    }
    
    function column_details($column, $postid)
    {
        if ( $column == 'weight_inventory' ) {
            $product = wc_get_product($postid);
            if($product->get_type() == 'simple')
            {
                $wei_inv = get_post_meta( $postid, '_weight_based_inventory', TRUE );
                $wei_quan = get_post_meta( $postid, '_inventory_weight', TRUE );
                $hold = get_post_meta( $postid, '_inventory_hold_weight', TRUE );
                $weight = get_post_meta( $postid, sq_wei_get_weight_slug(), TRUE );
                $orders = 0;
                $deci_orders = 0;
                if($wei_quan !=0 && $weight != 0)
                {
                    $deci_orders = (floatval($wei_quan)/floatval($weight));
                    $orders = floor($deci_orders);
                }
                if($wei_quan == 0)
                {
                    $wei_quan = __('Not Set',SQ_WEI_INV_SLUG);
                }
                else
                {
                    $wei_quan = $wei_quan.' '.get_option( 'woocommerce_weight_unit' );
                }
                if($weight == 0)
                {
                    $weight = __('Not Set',SQ_WEI_INV_SLUG);
                }
                else
                {
                    $weight = $weight.' '.get_option( 'woocommerce_weight_unit' );
                }
                if($wei_inv == 'yes')
                {
                    echo '<mark class="yes_weight_inventory">'.__('Yes',SQ_WEI_INV_SLUG).'</mark> - '.$wei_quan;
                }
                else
                {
                    echo '<mark class="no_weight_inventory">'.__('No',SQ_WEI_INV_SLUG).'</mark> - '.$wei_quan;
                }
                echo '<br>'.__('Product Weight',SQ_WEI_INV_SLUG).' - <b>'.$weight.'</b><br>';
                echo __('Placeable Orders',SQ_WEI_INV_SLUG).' - <b>'.$orders.'</b><br>';
                echo __('Remaining',SQ_WEI_INV_SLUG).' - <b>'.round($deci_orders - $orders,2).' '.get_option( 'woocommerce_weight_unit' ).'</b>';
                if($hold)
                {
                    echo '<br>'.__('On Hold Weight',SQ_WEI_INV_SLUG).' - <b>'.$hold.' '.get_option( 'woocommerce_weight_unit' ).'</b><br>';
                }
            }
            else
            {
                _e('Only on Premium',SQ_WEI_INV_SLUG);
            }
        }
    }
}

$manage_inventory = get_option(SQ_WEI_INV_SLUG.'_manage_inventory','both');
if($manage_inventory != '')
{
    new SQueue_Wei_Inv_Product_Settings();
}