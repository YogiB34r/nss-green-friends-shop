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
        $this->list_table_page();
    }

    if (!class_exists('WC_Admin_List_Table_Products')) {
        include_once 'class-wc-admin-list-table.php';
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
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Custom products list</h2>
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
        // $this->search = $search;
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
        $perPage = 30;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage
        ));
        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
        // var_dump($data);
        // die();
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
        // $data =[];
        $query = new WP_Query(array('post_type' => 'product', 'posts_per_page' => 50));

        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();

            // wc_get_template_part('content', 'product');
            $product_id = get_the_ID();
            // var_dump($product_id);
            // die();
            $product = wc_get_product($product_id);
            // $name = get_the_title();
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
            // var_dump($data);
            // die();
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

//    function column_url($item)
//    {
//        $actions = array(
//            'edit' => sprintf('<a href="?page=%s&action=%s&url=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id']),
//        );
//
//        return sprintf('%1$s %2$s', $item['url'], $this->row_actions($actions));
//    }

//    function get_bulk_actions()
//    {
//        $actions = array(
//            'delete' => 'Delete'
//        );
//        return $actions;
//    }

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
}
?>
<!-- <script>
    jQuery(document).ready(function () {
        jQuery('.toplevel_page_gf-search-settings').on('click', '.redirect', function () {
            var parent = jQuery(this).parent();
            var data = {
                term: jQuery(this).data('query'),
                url: parent.find('input').val()
            };
            jQuery.post('/gf-ajax/?saveSearchRedirect=1', data, function (response) {
                if (response == 1) {
                    parent.find('input').prop('readonly', true);
                    parent.find('button').hide();
                    alert('Upit izmenjen.');
                }
            });
        });

        jQuery('.toplevel_page_gf-search-settings').on('click', '.showRedirect', function () {
            var inputElement = jQuery(this).parent().prev().find('input');
            if (inputElement.hasClass('redirected')) {
                inputElement.prop('readonly', false);
            } else {
                inputElement.show();
            }
            inputElement.parent().children().show();
        });
    });
</script> --><?php
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
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Custom products list</h2>
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
        // $this->search = $search;
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
        $perPage = 30;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage
        ));
        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
        // var_dump($data);
        // die();
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
        // $data =[];
        $query = new WP_Query(array('post_type' => 'product', 'posts_per_page' => 50));

        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();

            // wc_get_template_part('content', 'product');
            $product_id = get_the_ID();
            // var_dump($product_id);
            // die();
            $product = wc_get_product($product_id);
            // $name = get_the_title();
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
            // var_dump($data);
            // die();
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

//    function column_url($item)
//    {
//        $actions = array(
//            'edit' => sprintf('<a href="?page=%s&action=%s&url=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id']),
//        );
//
//        return sprintf('%1$s %2$s', $item['url'], $this->row_actions($actions));
//    }

//    function get_bulk_actions()
//    {
//        $actions = array(
//            'delete' => 'Delete'
//        );
//        return $actions;
//    }

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
}
?>
<!-- <script>
    jQuery(document).ready(function () {
        jQuery('.toplevel_page_gf-search-settings').on('click', '.redirect', function () {
            var parent = jQuery(this).parent();
            var data = {
                term: jQuery(this).data('query'),
                url: parent.find('input').val()
            };
            jQuery.post('/gf-ajax/?saveSearchRedirect=1', data, function (response) {
                if (response == 1) {
                    parent.find('input').prop('readonly', true);
                    parent.find('button').hide();
                    alert('Upit izmenjen.');
                }
            });
        });

        jQuery('.toplevel_page_gf-search-settings').on('click', '.showRedirect', function () {
            var inputElement = jQuery(this).parent().prev().find('input');
            if (inputElement.hasClass('redirected')) {
                inputElement.prop('readonly', false);
            } else {
                inputElement.show();
            }
            inputElement.parent().children().show();
        });
    });
</script> -->