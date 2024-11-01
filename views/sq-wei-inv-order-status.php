<?php
if (!defined('ABSPATH'))
{
    exit;
}
$order_status = get_option(SQ_WEI_INV_SLUG.'_order_status_actions', sq_wei_inv_def_order_status_actions());
$status = wc_get_order_statuses();
$statuses = array();
foreach ($status as $key => $value)
{
    $statuses[str_replace('wc-', '', $key)] = $value;
}
foreach ($statuses as $key => $value)
{
    if(!isset($order_status[$key]))
    {
        $order_status[$key] = 'nothing';
    }
}
?>
<form method="post" action="<?php echo admin_url("admin.php?page=" . SQ_WEI_INV_SLUG.'&tab=order_status'); ?>">
    <table class="form-table">
        <?php
            foreach ($order_status as $key => $value)
            {
                ?>
                <tr>
                    <th scope="row">
                        <label for="<?php echo $key; ?>">
                            <?php echo $statuses[$key]; ?>
                        </label>
                    </th>
                    <td>
                        <select name="order_status_actions[<?php echo $key; ?>]" class="select_box_big">
                            <option value="nothing" <?php echo (($value == 'nothing')?"selected":"") ?>><?php _e('Do nothing from Inventory',SQ_WEI_INV_SLUG); ?></option>
                            <option value="reduce" <?php echo (($value == 'reduce')?"selected":"") ?>><?php _e('Reduce Weight from Inventory',SQ_WEI_INV_SLUG); ?></option>
                            <option value="return" <?php echo (($value == 'return')?"selected":"") ?>><?php _e('Return Weight to Inventory',SQ_WEI_INV_SLUG); ?></option>
                        </select>
                    </td>
                </tr>
                <?php
            }
        ?>
    </table>
    <input type="submit" class="button button-primary" name="sq_wei_inv_order_status" id="sq_wei_inv_order_status" value="<?php _e("Save Changes", SQ_WEI_INV_SLUG); ?>">
    <input type="submit" class="button button-primary" name="sq_wei_inv_order_status_reset" id="sq_wei_inv_order_status_reset" value="<?php _e("Reset Defaults", SQ_WEI_INV_SLUG); ?>">
</form>