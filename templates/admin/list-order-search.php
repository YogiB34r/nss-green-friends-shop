<?php

/**
 *
 * Here we check to see if admin made this request
 *
 */
if (is_admin()) {
    new Gf_Order_Wp_List_Table();
}


/**
 *
 * Gf_Order_Wp_List_Table class will create the page to load the table
 *
 */
class Gf_Order_Wp_List_Table
{

    /**
     *
     * Gf_Order_Wp_List_Table constructor.
     *
     */
    public function __construct()
    {
        $this->list_table_page();
    }



    /**
     *
     * Display list table page.
     *
     * @return void.
     */
    public function list_table_page()
    {
        $orderListTable = new Order_List_Table;

        $orderListTable->prepare_items(); ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Custom order lista</h2>
            <?php $orderListTable->display(); ?>
        </div>
    <?php
    }
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Create new table class that will extenc Wp_List_Table
 */
class Order_List_Table extends Wp_List_Table
{
    public function __construct($args = array())
    {
        parent::__construct($args);
    }

    /**
     *
     * Prepare the items for the table to process
     *
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        /* Process bulk action */
        $this->process_bulk_action();

        $ordersData = $this->table_data();
        usort($ordersData, array(&$this, 'sort_data'));

        $perPage = 30;
        $currentPage = $this->get_pagenum();
        $totalItems = count($ordersData);
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage
        ));
        $data = array_slice($ordersData, (($currentPage - 1) * $perPage), $perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     *
     * Override parent get_columns method. Defines columns to use in our listing tables.
     *
     * @return array
     */

    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type=checkbox />',
            'order' => 'Order',
            'date' => 'Date',
            'status' => 'Status',
            'total' => 'Total'
        );

        return $columns;
    }

    /**
     *
     * Define witch columns are hidden.
     *
     * @return array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     *
     * Define sortable columns
     *
     *@return array
     */
    public function get_sortable_columns()
    {
        return array(
            'order' => array('order', false),
            'date' => array('date', false),
            'total' => array('date', false)
        );
    }


    public function process_bulk_action()
    {
    }

    /**
     *
     * Get the table data.
     *
     * @return array
     */
    public function table_data()
    {
        $args = array(
            'type' => 'shop_order',
            'limit' => -1
        );

        $orders = wc_get_orders($args);

        foreach ($orders as $order) {
            $order_data = $order->get_data();
            $order_id = $order_data['id'];
            $order_name = '<a href="' . esc_url(admin_url('post.php?post=' . $order_data['id'] . ' &action=edit')) . ' "><strong>' . esc_html($order_number = $order_data['number'] . ' ' . $order_data['billing']['first_name'] . ' ' . $order_billing_last_name = $order_data['billing']['last_name']) . '<strong></a>';
            $order_total = $order_data['total'] . ' ' . $order_data['currency'];
            $order_status = $order_data['status'];
            $order_date_modified = $order_data['date_modified']->date('d-m-Y H:i:s');

            $data[] = array(
               'id' => $order_id,
               'order' => $order_name,
               'date' => $order_date_modified,
               'status' => $order_status,
               'total' => $order_total
                );
        }

        // var_dump($data);
        // die();
        return $data;
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
            case 'order':
            case 'date':
            case 'status':
            case 'total':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    /**
     *
     * Allows you to sort the data by variables set in $_GET[]
     *
     * @return mixed
     */

    private function sort_data($a, $b)
    {
        //Here we first set the default values
        $orderby = 'order';
        $order = 'desc';

        //If $_GET['orderBy'] is set use it
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }
        //If $_GET['order'] is set use it
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
}
