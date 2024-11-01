<?php
if (!defined('ABSPATH'))
{
    exit;
}

if (!class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if(!class_exists("SQueue_Wei_Inv_Products"))
{
    class SQueue_Wei_Inv_Products extends WP_List_Table {
        
        function __construct()
        {
            add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array($this,'handle_custom_query_var'), 10, 2 );
            parent::__construct( array(
                    'singular' => 'Product',     // Singular name of the listed records.
                    'plural'   => 'Products',    // Plural name of the listed records.
                    'ajax'     => false,       // Does this table support ajax?
            ) );
        }
        
        function handle_custom_query_var( $query, $query_vars ) {
            if ( ! empty( $query_vars['_weight_based_inventory'] ) ) {
                $query['meta_query'][] = array(
                        'key' => '_weight_based_inventory',
                        'value' => esc_attr( $query_vars['_weight_based_inventory'] ),
                );
            }
            return $query;
        }
        
        public static function get_products( $per_page = 10, $page_number = 0,$orderby = '_inventory_weight', $order='asc') {
            $products = wc_get_products(
                array
                ( 
                    '_weight_based_inventory' => 'yes',
                    'meta_key' => $orderby,
                    'orderby'   => 'meta_value',
                    'order' => strtoupper($order),
                    'type' => array('simple'),
                    'offset' => $page_number,
                    'limit' => $per_page
                )
            );
            $data = array();
            $weight_type = get_option(SQ_WEI_INV_SLUG.'_weight_type','predefined');
            foreach ($products as $product)
            {
                $pro = array();
                $product_id = $product->get_ID();
                $pro['id'] = $product_id;
                $pro['name'] = $product->get_name();
                $pro['type'] = ucfirst($product->get_type());
                $pro['parent'] = ucfirst($product->get_parent_id());
                $pro['status'] = $product->get_stock_status();
                $pro['status_remarks'] = 'all';
                $inventory = get_post_meta($product_id, '_inventory_weight', TRUE);
                $pro['inventory_raw'] = $inventory;
                $backorder = get_post_meta($product_id, '_inventory_weight_backorders', TRUE);
                $weight = get_post_meta($product_id, sq_wei_get_weight_slug(), TRUE);
                $pro['weight_raw'] = $weight;
                if(empty($inventory))
                {
                    if($backorder == 'no')
                    {
                        $pro['status_remarks'] = 'inventory';
                        $pro['status'] = 'outofstock';
                    }
                    else
                    {
                        $pro['status_remarks'] = 'backorder';
                        $pro['status'] = 'onbackorder';
                    }
                    $inventory = __('Not Set',SQ_WEI_INV_SLUG);
                }
                else
                {
                    $inventory = $inventory.' '.get_option( 'woocommerce_weight_unit' );
                }
                if($weight_type == 'predefined')
                {
                    if(empty($weight))
                    {
                        if($pro['type'] != 'Variable')
                        {
                            $pro['status'] = 'outofstock';
                            $pro['status_remarks'] = 'weight';
                            $weight = __('Not Set',SQ_WEI_INV_SLUG);
                        }
                        else
                        {
                            $pro['status'] = 'unknown';
                            $pro['status_remarks'] = 'variable';
                            $weight = __('On Variation',SQ_WEI_INV_SLUG);
                        }

                    }
                    else
                    {
                        $weight = $weight.' '.get_option( 'woocommerce_weight_unit' );
                    }
                }
                $pro['inventory'] = $inventory;
                $pro['weight'] = $weight;
                $pro['price'] = $product->get_price_html();
                $data[] = $pro;
            }
            return $data;
        }
        
        public static function record_count() {
            $products = wc_get_products( 
                array( 
                    '_weight_based_inventory' => 'yes',
                    'return' => 'ids',
                    'type' => array('simple'),
                )
            );
            return count($products);
        }
        
        public function no_items() {
            _e( 'No Products based on Weight Inventory.', SQ_WEI_INV_SLUG );
        }
        
        protected function column_default( $item, $column_name ) {
            $weight_type = get_option(SQ_WEI_INV_SLUG.'_weight_type','predefined');
            switch ($column_name)
            {
                case 'status':
                    switch ($item['status'])
                    {
                        case 'instock':
                            echo '<mark class="'.$item['status'].'">'.__('In stock',SQ_WEI_INV_SLUG).'</mark>';
                            break;
                        case 'outofstock':
                            echo '<mark class="'.$item['status'].'">'.__('Out Of stock',SQ_WEI_INV_SLUG).'</mark>';
                            break;
                        case 'onbackorder':
                            echo '<mark class="'.$item['status'].'">'.__('Backorder',SQ_WEI_INV_SLUG).'</mark>';
                            break;
                    }
                    if($weight_type == 'predefined')
                    {
                        switch ($item['status_remarks'])
                        {
                            case 'all':
                                return;
                            case 'inventory':
                                return '<br>Invalid Inventory';
                            case 'weight':
                                return '<br>Invalid Weight';
                            case 'backorder':
                                return '<br>Backorder Enabled';
                            case 'variable':
                                return 'On Variation';
                        }
                    }
                    break;
                case 'inventory':
                    echo '<div class="avail_inventory">'.$item['inventory'].'</div>';
                    echo '<input type="hidden" id="inventory_raw_'.$item['id'].'" value="'.$item['inventory_raw'].'">';
                    echo '<div id="edit_inventory_'.$item['id'].'_html">
                        </div>';
                    echo '<div id="inventory_row_actions_'.$item['id'].'">
                            <span class="edit"><a href="javascript:void(0)" target="_blank" id="'.$item['id'].'" class="inventory_edit_action edit_inv">'.__('Edit Inventory',SQ_WEI_INV_SLUG).'</a></span>
                        </div>';
                    break;
                case 'weight':
                    if(get_option(SQ_WEI_INV_SLUG.'_weight_type','predefined') == 'predefined')
                    {
                        echo '<div class="avail_weight">'.$item['weight'].'</div>';
                        if($item['type'] != 'Variable')
                        {
                            echo '<input type="hidden" id="weight_raw_'.$item['id'].'" value="'.$item['weight_raw'].'">';
                            echo '<div id="edit_weight_'.$item['id'].'_html">
                                </div>';
                            echo '<div id="weight_row_actions_'.$item['id'].'">
                                    <span class="edit"><a href="javascript:void(0)" target="_blank" id="'.$item['id'].'" class="weight_edit_action edit_wei">'.__('Edit Weight',SQ_WEI_INV_SLUG).'</a></span>
                                </div>';
                        }
                    }
                    else
                    {
                        _e('Variable Weight',SQ_WEI_INV_SLUG);
                    }
                    break;
                default :
                    return $item[$column_name];
            }
	}
        
        protected function column_name( $item ) {
                $actions['id'] = sprintf(
			'<span style="color:silver;">ID: %1$s</span> ',
			$item['id']
		);
		$actions['edit'] = sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			admin_url("post.php?post=".(($item['parent'] != 0)?$item['parent']:$item['id'])."&action=edit"),
			__( 'Edit', SQ_WEI_INV_SLUG)
		);
                $actions['view'] = sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			get_permalink($item['id']),
			__( 'View', SQ_WEI_INV_SLUG)
		);
                
		// Return the title contents.
		return sprintf( '%1$s %2$s',
			$item['name'],
			$this->row_actions( $actions )
		);
	}
        
        function get_columns() {
            $columns = [
                'name'     => __( 'Name', SQ_WEI_INV_SLUG ),
                'type'      => __( 'Type', SQ_WEI_INV_SLUG ),
                'inventory'      => __( 'Inventory', SQ_WEI_INV_SLUG ),
                'weight'        => __( 'Weight', SQ_WEI_INV_SLUG ),
                'status'    => __( 'Status', SQ_WEI_INV_SLUG ),
                'price'    => __( 'Price', SQ_WEI_INV_SLUG ),
            ];
            return $columns;
        }
        function get_sortable_columns() {
            $sortable_columns = array(
                'inventory'    => '_inventory_weight',
                'price' => '_price',
            );
            if(get_option(SQ_WEI_INV_SLUG.'_weight_type','predefined') == 'predefined')
            {
                $sortable_columns['weight'] = sq_wei_get_weight_slug();
            }
            return $sortable_columns;
	}
        public function prepare_items() {
            $this->_column_headers = $this->get_column_info();
            $this->process_bulk_action();
            $per_page     = $this->get_items_per_page( SQ_WEI_INV_SLUG.'_per_page', 10 );
            $current_page = $this->get_pagenum();
            $total_items  = self::record_count();
            $this->set_pagination_args( [
                'total_items' => $total_items,
                'per_page'    => $per_page,
                'total_pages' => ceil( $total_items / $per_page ),
            ] );
            $orderby = ! empty( $_REQUEST['orderby'] ) ? wp_unslash( $_REQUEST['orderby'] ) : '_inventory_weight';
            $order = ! empty( $_REQUEST['order'] ) ? wp_unslash( $_REQUEST['order'] ) : 'asc';
            $data = self::get_products($per_page,($current_page-1),$orderby,$order);
            $this->items = $data;
        }
    }
}