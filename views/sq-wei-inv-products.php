<div class="wrap" id="sq_wei_inv_products_wrap">
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <?php
                    $table = new SQueue_Wei_Inv_Products();
                    $table->prepare_items();
                    $table->display();
                    ?>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>