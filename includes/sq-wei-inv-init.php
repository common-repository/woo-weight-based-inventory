<?php
if (!defined('ABSPATH'))
{
    exit;
}

class SQueue_Weight_Inventory
{
    protected $hook;
    function __construct()
    {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_filter('set-screen-option', array($this,'set_screen'), 10, 3 );
    }
    
    function admin_menu()
    {
        $this->hook = add_submenu_page('woocommerce', __('Weight Inventory',SQ_WEI_INV_SLUG), __('Weight Inventory',SQ_WEI_INV_SLUG), 'manage_woocommerce',SQ_WEI_INV_SLUG, array($this, 'render_tab'));
        add_action( "load-$this->hook", array($this,'screen_option'));
    }

    function screen_option()
    {
        $option = 'per_page';
 
        $args = array(
            'label' => 'Products',
            'default' => 10,
            'option' => SQ_WEI_INV_SLUG.'_per_page'
        );
        $page = (!empty($_GET['page']))? esc_attr($_GET['page']) : '';
        $tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'products';
        if($page == SQ_WEI_INV_SLUG && $tab == 'products')
        {
            add_screen_option( $option, $args );
            new SQueue_Wei_Inv_Products();
        }
    }
    
    function set_screen( $status, $option, $value ) {
        return $value;
    }
    
    function render_tab()
    {
        $page = (!empty($_GET['page']))? esc_attr($_GET['page']) : '';
        $tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'products';
        if($page == SQ_WEI_INV_SLUG && isset($_POST['sq_wei_inv_settings']) && $tab == 'general')
        {
            update_option(SQ_WEI_INV_SLUG.'_manage_inventory', sanitize_text_field($_POST['manage_inventory']));
            update_option(SQ_WEI_INV_SLUG.'_weight_type', sanitize_text_field($_POST['weight_type']));
            if(isset($_POST['notification']))
            {
                update_option(SQ_WEI_INV_SLUG.'_notification', $_POST['notification']);
            }
            else
            {
                update_option(SQ_WEI_INV_SLUG.'_notification', array());
            }
            update_option(SQ_WEI_INV_SLUG.'_notification_recipient', sanitize_text_field($_POST['notification_recipient']));
            update_option(SQ_WEI_INV_SLUG.'_low_stock_threshold', sanitize_text_field($_POST['low_stock_threshold']));
            update_option(SQ_WEI_INV_SLUG.'_out_of_stock_threshold', sanitize_text_field($_POST['out_of_stock_threshold']));
            update_option(SQ_WEI_INV_SLUG.'_stock_display_format', sanitize_text_field($_POST['stock_display_format']));
            update_option(SQ_WEI_INV_SLUG.'_custom_format_text', sanitize_text_field($_POST['custom_format_text']));
            switch ($_POST['manage_inventory'])
            {
                case 'weight':
                    update_option('woocommerce_manage_stock', 'no');
                    break;
                case 'both':
                    update_option('woocommerce_manage_stock', 'yes');
                    break;
            }
        }
        if($page == SQ_WEI_INV_SLUG && isset($_POST['sq_wei_inv_order_status']) && $tab == 'order_status')
        {
            update_option(SQ_WEI_INV_SLUG.'_order_status_actions', $_POST['order_status_actions']);
        }
        if($page == SQ_WEI_INV_SLUG && isset($_POST['sq_wei_inv_order_status_reset']) && $tab == 'order_status')
        {
            update_option(SQ_WEI_INV_SLUG.'_order_status_actions', sq_wei_inv_def_order_status_actions());
        }
        if($page == SQ_WEI_INV_SLUG && $tab == 'products' && isset($_POST['sq_wei_inv_smart_action']) && $_POST['sq_wei_inv_smart_action'] == 'yes')
        {
            if(isset($_POST['inventory_weight']) && isset($_POST['product_id']))
            {
                update_post_meta( $_POST['product_id'], '_inventory_weight', esc_attr( $_POST['inventory_weight']) );
            }
            if(isset($_POST['product_weight']) && isset($_POST['product_id']))
            {
                update_post_meta( $_POST['product_id'], sq_wei_get_weight_slug(), esc_attr( $_POST['product_weight']) );
            }
        }
        echo '
            <div class="wrap">
                <h1 class="wp-heading-inline">'.__('WooCommerce Weight Based Inventory', SQ_WEI_INV_SLUG).'</h1>
                <hr class="wp-header-end">';
            $this->admin_page_tabs($tab);
            switch($tab)
            {
                case "products":
                    echo '<div class="table-box table-box-main" id="products_section" style="margin-top: 10px;">';
                       require SQ_WEI_INV_VIEWS.'sq-wei-inv-products.php';
                    echo '</div>';
                    break;
                case "general":
                    echo '<div class="table-box table-box-main sq_wei_inv_settings_wrap" id="general_section" style="margin-top: 10px;">';
                       require SQ_WEI_INV_VIEWS.'sq-wei-inv-settings.php';
                    echo '</div>';
                    break;
                case "order_status":
                    echo '<div class="table-box table-box-main" id="order_status_section" style="margin-top: 10px;">';
                       require SQ_WEI_INV_VIEWS.'sq-wei-inv-order-status.php';
                    echo '</div>';
                    break;
                case "premium":
                    echo '<div class="table-box table-box-main" id="premium_section" style="margin-top: 10px;">';
                       require SQ_WEI_INV_VIEWS.'upgrade_premium.php';
                    echo '</div>';
                    break;
            }
    echo '</div>';
    }
    
    function admin_page_tabs($current = 'products') {
        $tabs = array(
            'products'   => __("Weight Based Products", SQ_WEI_INV_SLUG),
            'general'   => __("General Settings", SQ_WEI_INV_SLUG),
            'order_status'   => __("Order Statuses Actions", SQ_WEI_INV_SLUG),
            'premium'   => __("Premium Features", SQ_WEI_INV_SLUG)
        );
        $html =  '<h2 class="nav-tab-wrapper">';
        foreach( $tabs as $tab => $name ){
            $class = ($tab == $current) ? 'nav-tab-active' : '';
            $style = ($tab == $current) ? 'border-bottom: 1px solid transparent !important;' : '';
            $html .=  '<a style="text-decoration:none !important;'.$style.'" class="nav-tab ' . $class . '" href="?page='.SQ_WEI_INV_SLUG.'&tab=' . $tab . '">' . $name . '</a>';
        }
        $html .= '</h2>';
        echo $html;
    }
    
    

    

    function admin_scripts()
    {
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';
        if (in_array($screen_id, array('product', 'edit-product')))
        {
            wp_enqueue_script('sq-wei-inv-admin-product', SQ_WEI_INV_JS . 'wei-inv-admin-product.js');
            wp_enqueue_style('sq-wei-inv-admin-product', SQ_WEI_INV_CSS . 'wei-inv-admin-product.css');
        }
        $page = (!empty($_GET['page']))? esc_attr($_GET['page']) : '';
        $tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'products';
        if($page == SQ_WEI_INV_SLUG)
        {
            wp_enqueue_script('jquery');
            wp_enqueue_script('wc-enhanced-select');
            wp_enqueue_style( 'woocommerce_admin_styles');
            wp_enqueue_script('sq-wei-inv-admin-page', SQ_WEI_INV_JS . 'wei-inv-admin-page.js');
            wp_enqueue_style('sq-wei-inv-admin-page', SQ_WEI_INV_CSS . 'wei-inv-admin-page.css');
        }
        if($page == SQ_WEI_INV_SLUG && $tab == 'premium')
        {
            wp_enqueue_style('bootstrap', SQ_WEI_INV_CSS . 'bootstrap.css');
        }
    }
}
