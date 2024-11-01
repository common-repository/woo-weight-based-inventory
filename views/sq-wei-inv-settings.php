<?php
if (!defined('ABSPATH'))
{
    exit;
}
$manage_inventory = get_option(SQ_WEI_INV_SLUG.'_manage_inventory','both');
$weight_type = get_option(SQ_WEI_INV_SLUG.'_weight_type','predefined');
$notification = get_option(SQ_WEI_INV_SLUG.'_notification',array('low_stock','out_of_stock'));
$notification_recipient = get_option(SQ_WEI_INV_SLUG.'_notification_recipient', get_option('admin_email'));
$low_stock_threshold = get_option(SQ_WEI_INV_SLUG.'_low_stock_threshold','2');
$out_of_stock_threshold = get_option(SQ_WEI_INV_SLUG.'_out_of_stock_threshold','0');
$stock_display_format = get_option(SQ_WEI_INV_SLUG.'_stock_display_format','basic');
$custom_format_text = get_option(SQ_WEI_INV_SLUG.'_custom_format_text','');
?>
<form method="post" action="<?php echo admin_url("admin.php?page=" . SQ_WEI_INV_SLUG.'&tab=general'); ?>">
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="manage_inventory">
                    <?php _e('Manage Inventory', SQ_WEI_INV_SLUG); ?>
                </label>
            </th>
            <td>
                <select name="manage_inventory" id="manage_inventory" class="select_box_big">
                    <option value="" <?php echo (($manage_inventory == '')?"selected":"") ?>><?php _e('Select Inventory',SQ_WEI_INV_SLUG); ?></option>
                    <option value="both" <?php echo (($manage_inventory == 'both')?"selected":"") ?>><?php _e('Enable weight based & WooCommerce quantity based Inventory',SQ_WEI_INV_SLUG); ?></option>
                    <option value="weight" <?php echo (($manage_inventory == 'weight')?"selected":"") ?>><?php _e('Only Weight Based Inventory',SQ_WEI_INV_SLUG); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="weight_type">
                    <?php _e('Weight Type', SQ_WEI_INV_SLUG); ?>
                </label>
            </th>
            <td>
                <label>
                    <input name="weight_type" type="radio" value="predefined" id="weight_type" <?php echo (($weight_type == 'predefined')?"checked":"") ?>>
                    <?php _e("Predefined weight and price by store owners", SQ_WEI_INV_SLUG); ?>
                </label>
                <br>
                <label>
                    <input name="weight_type" type="radio" value="variable" id="weight_type" <?php echo (($weight_type == 'variable')?"checked":"") ?>>
                    <?php _e("Variable weight by customers and calculate price", SQ_WEI_INV_SLUG); ?>
                </label>
                <p><?php _e("For Variable weight by customers and calculate price will give a number box for entering the required weight.", SQ_WEI_INV_SLUG); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="notification">
                    <?php _e('Notification', SQ_WEI_INV_SLUG); ?>
                </label>
            </th>
            <td>
                <label>
                    <input type="checkbox" name="notification[]" value="low_stock" <?php echo ((in_array('low_stock', $notification))?"checked":"") ?>> <?php _e('Low Stock Notification',SQ_WEI_INV_SLUG); ?>
                </label>
                <br>
                <br>
                <label>
                    <input type="checkbox" name="notification[]" value="out_of_stock" <?php echo ((in_array('out_of_stock', $notification))?"checked":"") ?>> <?php _e('Out of Stock Notification',SQ_WEI_INV_SLUG); ?>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="notification_recipient">
                    <?php _e('Notification Recipient(s)', SQ_WEI_INV_SLUG); ?>
                </label>
            </th>
            <td>
                <input type="email" name="notification_recipient" value="<?php echo $notification_recipient; ?>" class="input_box_big">
                <p class="description"><?php _e('Enter recipients (comma Seperated for multiple) that will receive the notifications',SQ_WEI_INV_SLUG); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="low_stock_threshold">
                    <?php _e('Low stock threshold', SQ_WEI_INV_SLUG); ?>
                </label>
            </th>
            <td>
                <input type="number" name="low_stock_threshold" value="<?php echo $low_stock_threshold; ?>" class="input_small_box">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="out_of_stock_threshold">
                    <?php _e('Out of stock threshold', SQ_WEI_INV_SLUG); ?>
                </label>
            </th>
            <td>
                <input type="number" name="out_of_stock_threshold" value="<?php echo $out_of_stock_threshold; ?>" class="input_small_box">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="stock_display_format">
                    <?php _e('Stock Display Format', SQ_WEI_INV_SLUG); ?>
                </label>
            </th>
            <td>
                <select name="stock_display_format" id="stock_display_format" class="select_box_big">
                    <option value="" <?php echo (($stock_display_format == '')?"selected":"") ?>><?php _e("Don't show Stock Status", SQ_WEI_INV_SLUG); ?></option>
                    <option value="basic" <?php echo (($stock_display_format == 'basic')?"selected":"") ?>><?php _e("Convert inventory stock weight into quantity - 12 in stock", SQ_WEI_INV_SLUG); ?></option>
                    <option value="weight" <?php echo (($stock_display_format == 'weight')?"selected":"") ?>><?php _e("Display inventory stock weight", SQ_WEI_INV_SLUG); ?> - 120 <?php echo get_option( 'woocommerce_weight_unit' ); ?> <?php _e("in stock", SQ_WEI_INV_SLUG); ?></option>
                    <option value="custom" <?php echo (($stock_display_format == 'custom')?"selected":"") ?>><?php _e("Custom Format", SQ_WEI_INV_SLUG); ?></option>
                </select>
            </td>
        </tr>
        <tr class="custom_format_text" style="display:none;">
            <th scope="row">
                <label for="custom_format_text">
                    <?php _e('Custom Format', SQ_WEI_INV_SLUG); ?>
                </label>
            </th>
            <td>
                <input type="text" name="custom_format_text" value="<?php echo $custom_format_text; ?>" class="input_box_big">
                <p class="description">
                    <?php _e("Use", SQ_WEI_INV_SLUG); ?> <code>[{quantity}]</code> <?php _e("for replacing the quantity", SQ_WEI_INV_SLUG); ?>.<br>
                    <?php _e("Use", SQ_WEI_INV_SLUG); ?> <code>[{unit}]</code> <?php _e("for replacing the weight unit", SQ_WEI_INV_SLUG); ?>.<br>
                    <?php _e("Use", SQ_WEI_INV_SLUG); ?> <code>[{stock}]</code> <?php _e("for replacing the inventory stock weight", SQ_WEI_INV_SLUG); ?>.
                </p>
            </td>
        </tr>
    </table>
    <input type="submit" class="button button-primary" name="sq_wei_inv_settings" id="sq_wei_inv_settings" value="<?php _e("Save Changes", SQ_WEI_INV_SLUG); ?>">
</form>