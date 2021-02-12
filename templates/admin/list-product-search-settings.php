<?php
/**
 * @var Elastica\Result $result
 * @var \GF\Search\Elastica\TermSearch $termSearch
 */
if (is_admin()) {
    new Gf_Product_Wp_List_Table();
}

/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class Gf_Product_Wp_list_Table
{
    /**
     * Gf_Product_Wp_list_Table constructor.
     *
     * @param \GF\Search\Elastica\TermSearch $search
     */
    public function __construct()
    {
        //for testing
//        add_action('admin_menu', 'add_example_menue');
//
//        function add_example_menue()
//        {
//            add_menu_page('test', 'test', 'administrator', 'test-top', array($this, 'list_table_page'));
//        }

        $this->list_table_page();
    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page()
    {
        $productListTable = new Product_List_Table([]);
        $productListTable->prepare_items(); ?>

        <form id="posts-filter" method="get" >
        <input type="hidden" name="page" class="post_status_page" value="gf-product-list" />

         <?php $productListTable->search_box('Search products'); ?>
        <input type="hidden" name="post_status" class="post_status_page" value="all" />
        <input type="hidden" name="post_type" class="post_type_page" value="product" />
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <a href="http://nss.local/wp-admin/post-new.php?post_type=product" class="page-title-action">Add New</a>
            <a href="http://nss.local/wp-admin/edit.php?post_type=product&amp;page=product_importer" class="page-title-action">Import</a>
            <a href="http://nss.local/wp-admin/edit.php?post_type=product&amp;page=product_exporter" class="page-title-action">Export</a>
            <h2 class="wp-custom-heading-inline">Custom products list</h2>

            <?php $productListTable->bulk_actions(); ?>
            <?php $productListTable->render_products_category(); ?>
            <?php $productListTable->render_products_type(); ?>
            <?php $productListTable->render_products_stock_status(); ?>
            <?php submit_button('Filter', 'submit,', 'filter_action', '', false, array( 'id' => 'post_submit' )); ?>
            <?php $productListTable->display(); ?>
        </div>
        <?php
    }
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

if (!class_exists('WP_Query')) {
    require_once(ABSPATH . 'wp-includes/class-wp-query.php');
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Product_List_Table extends WP_List_Table
{
    public function __construct($args = array())
    {
        parent::__construct($args);
    }

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        /** Process bulk action */
        $this->process_bulk_action();

        $data = $this->table_data();
        usort($data, array(&$this, 'sort_data'));
        $perPage = 30;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage
        ));
        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'name' => 'Name',
            'sku' => 'SKU',
            'price' => 'Price',
            'categories' => 'Categories',
            'date' => 'Date'
        );
        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array(
            'name' => array('name', false),
            'sku' => array('sku', false),
            'price' => array('price', false),
            'date' => array('date', false)
        );
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
        // default arguments array
        $arguments = array('post_type' => 'product', 'posts_per_page' => 100);

        // filter part of the table data
        if ($_REQUEST['filter_action'] === 'Filter') {
            $arguments['action'] = -1;
            $arguments['paged'] = 1;
            $arguments['action2'] = -1;
            if ($_REQUEST['product_type'] !== '') {
                $arguments['product_type'] = $_REQUEST['product_type'];
            } elseif ($_REQUEST['stock_status'] !== '') {
                $arguments['stock_status'] = $_REQUEST['stock_status'];
            } elseif ($_REQUEST['product_cat'] !== '') {
                $arguments['product_cat'] = $_REQUEST['product_cat'];
            }
        }

        $query = new WP_Query($arguments);

        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();

            $product_id = get_the_ID();

            $product = wc_get_product($product_id);
            $name = '<a href="' . esc_url(admin_url('post.php?post=' . get_the_ID() . ' &action=edit')) . ' ">' . esc_html(get_the_title()) . '</a>';
            $sku = get_post_meta($product_id, '_sku', true);
            $price = get_post_meta($product_id, '_price', true)  . 'din';
            $terms = get_the_terms($product_id, 'product_cat');
            if (! $terms) {
                echo '<span class="na">&ndash;</span>';
            } else {
                $termlist = array();
                foreach ($terms as $term) {
                    $termlist[] = '<a href="' . esc_url(admin_url('edit.php?product_cat=' . $term->slug . '&post_type=product')) . ' ">' . esc_html($term->name) . '</a>';
                    $termlists = implode(', ', $termlist);
                }
            }
            $date = '<span class="date_published">Published on: ' . $product->get_date_created() .'</span>';

            $data[] = array(
                             'id' =>  $product_id,
                             'name' => $name,
                             'sku' => $sku,
                             'price' => $price,
                             'categories' => $termlists,
                             'date' => $date
                    );
            endwhile;

            return $data;
        } else {
            echo __('No products found');
        }

        wp_reset_postdata();
        die();
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'name':
            case 'sku':
            case 'price':
            case 'categories':
            case 'date':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data($a, $b)
    {
        // Set defaults
        $orderby = 'name';
        $order = 'decs';
        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }
        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }
        $result = strcmp($a[$orderby], $b[$orderby]);
        if ($order === 'desc') {
            return $result;
        }
        return -$result;
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * Delete a query record.
     *
     * @param int $id query ID
     */
    public static function delete_searchQuery($id)
    {
        global $wpdb;

        $wpdb->delete(
            "{$wpdb->prefix}",
            [ 'id' => $id ],
            [ '%d' ]
        );
        //@TODO finish delete action
    }

    public function process_bulk_action()
    {
        //@TODO process bulk delete action
    }

    public function bulk_action()
    {
    }


    /**
     * Render the product category filter for the list table.
     *
     * @since 3.5.0
     */
    public function render_products_category()
    {
        if ($_REQUEST['product_cat'] !== '') {
            wc_product_dropdown_categories(
                array(
                    'option_select_text' => __('Filter by category', 'woocommerce'),
                    'hide_empty'         => 0,
                    'selected' => $_REQUEST['product_cat']
                )
            );
        } else {
            wc_product_dropdown_categories(
                array(
                    'option_select_text' => __('Filter by category', 'woocommerce'),
                    'hide_empty'         => 0,
                )
            );
        }
    }

    /**
     * Render the product type filter for the list table.
     *
     * @since 3.5.0
     */
    public function render_products_type()
    {
        $current_product_type = isset($_REQUEST['product_type']) ? wc_clean(wp_unslash($_REQUEST['product_type'])) : false; // WPCS: input var ok, sanitization ok.
        $output               = '<select name="product_type" id="dropdown_product_type"><option value="">' . __('Filter by product type', 'woocommerce') . '</option>';

        foreach (wc_get_product_types() as $value => $label) {
            $output .= '<option value="' . esc_attr($value) . '" ';
            $output .= selected($value, $current_product_type, false);
            $output .= '>' . esc_html($label) . '</option>';

            if ('simple' === $value) {
                $output .= '<option value="downloadable" ';
                $output .= selected('downloadable', $current_product_type, false);
                $output .= '> ' . (is_rtl() ? '&larr;' : '&rarr;') . ' ' . __('Downloadable', 'woocommerce') . '</option>';

                $output .= '<option value="virtual" ';
                $output .= selected('virtual', $current_product_type, false);
                $output .= '> ' . (is_rtl() ? '&larr;' : '&rarr;') . ' ' . __('Virtual', 'woocommerce') . '</option>';
            }
        }

        $output .= '</select>';
        echo $output; // WPCS: XSS ok.
    }

    /**
     * Render the stock status filter for the list table.
     *
     * @since 3.5.0
     */
    public function render_products_stock_status()
    {
        $current_stock_status = isset($_REQUEST['stock_status']) ? wc_clean(wp_unslash($_REQUEST['stock_status'])) : false; // WPCS: input var ok, sanitization ok.
        $stock_statuses       = wc_get_product_stock_status_options();
        $output               = '<select name="stock_status"><option value="">' . esc_html__('Filter by stock status', 'woocommerce') . '</option>';

        foreach ($stock_statuses as $status => $label) {
            $output .= '<option ' . selected($status, $current_stock_status, false) . ' value="' . esc_attr($status) . '">' . esc_html($label) . '</option>';
        }

        $output .= '</select>';
        echo $output; // WPCS: XSS ok.
    }
}
?>