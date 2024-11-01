<?php

/*
  Plugin Name: WooCommerce Weight Based Inventory
  Plugin URI: https://stepqueue.com/plugins/woocommerce-weight-based-inventory/
  Description: Easy way to convert WooCommerce Quantity based inventory into weight based inventory.
  Version: 1.1.2
  Author: StepQueue
  Author URI: https://stepqueue.com
  License: GPL 3.0
  WC requires at least: 3.0.0
  WC tested up to: 3.2.0
 */

if (!defined('ABSPATH'))
{
    exit;
}

require_once(ABSPATH . "wp-admin/includes/plugin.php");
if (in_array('woocommerce/woocommerce.php', get_option('active_plugins')))
{
    if (!in_array('woocommerce-weight-based-inventory-pro/woocommerce-weight-based-inventory-pro.php', get_option('active_plugins')))
    {
        if (!defined('SQ_WEI_INV_VERSION'))
        {
            define('SQ_WEI_INV_VERSION', '1.1.2');
        }
        if (!defined('SQ_WEI_INV_SLUG'))
        {
            define('SQ_WEI_INV_SLUG', 'sq_wei_inv');
        }
        if (!defined('SQ_WEI_INV_URL'))
        {
            define('SQ_WEI_INV_URL', plugin_dir_url(__FILE__));
        }
        if (!defined('SQ_WEI_INV_PATH'))
        {
            define('SQ_WEI_INV_PATH', plugin_dir_path(__FILE__));
        }
        if (!defined('SQ_WEI_INV_IMG'))
        {
            define('SQ_WEI_INV_IMG', SQ_WEI_INV_URL . "assets/img/");
        }
        if (!defined('SQ_WEI_INV_CSS'))
        {
            define('SQ_WEI_INV_CSS', SQ_WEI_INV_URL . "assets/css/");
        }
        if (!defined('SQ_WEI_INV_JS'))
        {
            define('SQ_WEI_INV_JS', SQ_WEI_INV_URL . "assets/js/");
        }
        if (!defined('SQ_WEI_INV_INC'))
        {
            define('SQ_WEI_INV_INC', SQ_WEI_INV_PATH . "includes/");
        }
        if (!defined('SQ_WEI_INV_VIEWS'))
        {
            define('SQ_WEI_INV_VIEWS', SQ_WEI_INV_PATH . "views/");
        }
        
        add_action('init', 'sq_wei_inv_run', 99);

        function sq_wei_inv_run()
        {
            if(!class_exists('SQueue_Weight_Inventory'))
            {
                require_once (SQ_WEI_INV_INC . "sq-wei-inv-init.php");
                require_once (SQ_WEI_INV_INC . "sq-wei-inv-public.php");
                require_once (SQ_WEI_INV_INC . "sq-wei-inv-notification.php");
                require_once (SQ_WEI_INV_INC . "sq-wei-inv-products-table.php");
                require_once (SQ_WEI_INV_INC . "sq-wei-inv-order-process.php");
                require_once (SQ_WEI_INV_INC . "sq-wei-inv-product-settings.php");
                require_once (SQ_WEI_INV_INC . "sq-wei-inv-custom-weight.php");
                new SQueue_Weight_Inventory();
            }
            if (!class_exists('StepQueue_Uninstall_feedback_Listener')) {
                require_once (SQ_WEI_INV_INC . "class-stepqueue-uninstall.php");
            }
            $qvar = array(
                'name' => 'WooCommerce weight based inventory',
                'version' => SQ_WEI_INV_VERSION,
                'slug' => 'woo-weight-based-inventory',
                'lang' => SQ_WEI_INV_SLUG,
            );
            new StepQueue_Uninstall_feedback_Listener($qvar);
        }

        add_filter('plugin_row_meta', 'sq_wei_inv_plugin_row_meta', 10, 2);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'sq_wei_inv_plugin_action_link');

        function sq_wei_inv_plugin_action_link($links)
        {
            $plugin_links = array(
                '<a href="' . admin_url('admin.php?page='.SQ_WEI_INV_SLUG.'&tab=general') . '">' . __('Inventory Settings', SQ_WEI_INV_SLUG) . '</a>'
            );
            if (array_key_exists('deactivate', $links))
            {
                $links['deactivate'] = str_replace('<a', '<a class="woo-weight-based-inventory-deactivate-link"', $links['deactivate']);
            }
            return array_merge($plugin_links, $links);
        }

        function sq_wei_inv_plugin_row_meta($links, $file)
        {
            if ($file == plugin_basename(__FILE__))
            {
                $row_meta = array(
                    '<a href="https://stepqueue.com/documentation/woocommerce-weight-based-inventory-setup/" target="_blank">' . __('Documentation', SQ_WEI_INV_SLUG) . '</a>',
                    '<a href="https://stepqueue.com/plugins/woocommerce-weight-based-inventory/" target="_blank">' . __('Buy Pro', SQ_WEI_INV_SLUG) . '</a>',
                    '<a href="https://wordpress.org/support/plugin/woo-weight-based-inventory/" target="_blank">' . __('Support', SQ_WEI_INV_SLUG) . '</a>'
                );
                return array_merge($links, $row_meta);
            }
            return (array) $links;
        }

    } else
    {
        add_action('admin_notices', 'sq_wei_inv_admin_notices', 99);
        deactivate_plugins(plugin_basename(__FILE__));

        function sq_wei_inv_admin_notices()
        {
            is_admin() && add_filter('gettext', function($translated_text, $untranslated_text, $domain)
                    {
                        $old = array(
                            "Plugin <strong>activated</strong>.",
                            "Selected plugins <strong>activated</strong>."
                        );
                        $new = "<span style='color:red'>WooCommerce Weight Based Inventory - Pro Version is currently installed and active</span>";
                        if (in_array($untranslated_text, $old, true))
                        {
                            $translated_text = $new;
                        }
                        return $translated_text;
                    }, 99, 3);
        }

        return;
    }
} else
{
    add_action('admin_notices', 'sq_wei_inv_wc_basic_admin_notices', 99);
    deactivate_plugins(plugin_basename(__FILE__));

    function sq_wei_inv_wc_basic_admin_notices()
    {
        is_admin() && add_filter('gettext', function($translated_text, $untranslated_text, $domain)
                {
                    $old = array(
                        "Plugin <strong>activated</strong>.",
                        "Selected plugins <strong>activated</strong>."
                    );
                    $new = "<span style='color:red'>WooCommerce Weight Based Inventory - WooCommerce is not Installed</span>";
                    if (in_array($untranslated_text, $old, true))
                    {
                        $translated_text = $new;
                    }
                    return $translated_text;
                }, 99, 3);
    }

    return;
}
