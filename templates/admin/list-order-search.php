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

        <form id="posts-filter" method="GET">
            <input type="hidden" name="page" value="gf-order-list" />
            <?php $orderListTable->search_box('Search orders'); ?>

            <input type="hidden" name="post_status" class="post_status_page" value="all" />
            <input type="hidden" name="pos_type" class="post_type_page" value="shop_order">

            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <h2>Custom order lista</h2>
                    <?php $orderListTable->render_customer_filter(); ?>
                    <?php $orderListTable->order_months_dropdown('shop_order') ?>
                    <?php submit_button('Filter', 'submit,', 'filter_action', '', false, array( 'id' => 'post_query_submit' )); ?>
                    <?php $orderListTable->display(); ?>
            </div>
        </form>
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
            'order' => 'Narudzbenica',
            'payment_method' => 'Nacin placanja',
            'order_phone_column' => 'Telefonom / WWW',
            'order_date' => 'Datum',
            'order_shipping_price' => 'Dostava',
            'order_total' => 'Ukupno',
            'order_status' => 'Status',
            'customActions' => 'Actions',
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
            'order_date' => array('date', false),
            'order_total' => array('date', false)
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

            if ($order_data[created_via] === 'admin') {
                $order_phone_column = 'Telefonom';
            } else {
                $order_phone_column = 'WWW';
            }
            $order_name = '<a href="' . esc_url(admin_url('post.php?post=' . $order_data['id'] . ' &action=edit')) . ' "><strong>' . esc_html($order_number = $order_data['number'] . ' ' . $order_data['billing']['first_name'] . ' ' . $order_billing_last_name = $order_data['billing']['last_name']) . '<strong></a>';
            $order_payment_method = $order_data['payment_method_title'];
            $order_total = $order_data['total'] . ' ' . $order_data['currency'];
            $order_shiping_price = $order_data['shipping_total'] . ' ' . $order_data['currency'];
            $order_status = $order_data['status'];
            $order_date_modified = $order_data['date_modified']->date('d-m-Y H:i:s');
            $jitexDoneStyle = '';
            $adresnicaDoneStyle = '';

            $customs_order_actions =
            '<a class="button" href="/back-ajax/?action=printPreorder&id=' . $order_id . '" title="Print predracuna" target="_blank">Predracun</a>' .
            '&nbsp;' .
            '<a class="button nssOrderJitexExport" '.$jitexDoneStyle.' href="/back-ajax/?action=exportJitexOrder&id=' . $order->get_id() . '" title="Export za Jitex" target="_blank">Export</a>' .
            '&nbsp;' .
            '<a class="button nssOrderAdresnica" '.$adresnicaDoneStyle.' href="/back-ajax/?action=adresnica&id=' . $order->get_id() . '" title="Kreiraj adresnicu" target="_blank">Adresnica</a>';
            // var_dump($customs_order_actions);
            // die();

            $data[] = array(
               'id' => $order_id,
               'order' => $order_name,
               'payment_method' => $order_payment_method,
               'order_phone_column' => $order_phone_column,
               'order_date' => $order_date_modified,
               'order_shipping_price' => $order_shiping_price,
               'order_total' => $order_total,
               'order_status' => $order_status,
               'customActions' => $customs_order_actions,
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
            case 'payment_method':
            case 'order_phone_column':
            case 'order_date':
            case 'order_shipping_price':
            case 'order_total':
            case 'order_status':
            case 'customActions':
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

    public function order_months_dropdown($post_type)
    {
        global $wpdb, $wp_locale;

        /**
        * Filters whether to remove the 'Months' drop-down from the post list table.
        *
        * @param bool   $disable   Whether to disable the drop-down. Default false.
        * @param string $post_type The post type.
        */
        if (apply_filters('disable_months_dropdown', false, $post_type)) {
            return;
        }

        $extra_checks = "AND post_status != 'auto-draft'";
        if (! isset($_GET['post_status']) || 'trash' !== $_GET['post_status']) {
            $extra_checks .= " AND post_status != 'trash'";
        } elseif (isset($_GET['post_status'])) {
            $extra_checks = $wpdb->prepare(' AND post_status = %s', $_GET['post_status']);
        }

        $months = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT DAY( post_date ) AS day, MONTH( post_date ) AS month, YEAR( post_date ) as year
            FROM $wpdb->posts
            WHERE post_type = %s
            $extra_checks
            ORDER BY post_date DESC
        ", $post_type));

        // var_dump($months);
        // die();

        /**
         * Filters the 'Months' drop-down results.
         *
         * @since 3.7.0
         *
         * @param object $months    The months drop-down query results.
         * @param string $post_type The post type.
         */
        $months = apply_filters('months_dropdown_results', $months, $post_type);

        $month_count = count($months);

        if (!$month_count || (1 == $month_count && 0 == $months[0]->month)) {
            return;
        }

        $m = isset($_GET['m']) ? (int) $_GET['m'] : 0; ?>
        <label for="filter-by-date" class="screen-reader-text"><?php _e('Filter by date'); ?></label>
        <select name="m" id="filter-by-date">
            <option<?php selected($m, 0); ?> value="0"><?php _e('All dates'); ?></option>
<?php
        foreach ($months as $arc_row) {
            if (0 == $arc_row->year) {
                continue;
            }

            $day = $arc_row->day;
            $month = zeroise($arc_row->month, 2);
            $year = $arc_row->year;

            printf(
                "<option %s value='%s'>%s</option>\n",
                selected($m, $day . $month . $year, false),
                esc_attr($arc_row->day . $month . $arc_row->year),
                /* translators: 1: month name, 2: 4-digit year */
                sprintf(__('%1$d %2$s %3$d'), $day, $wp_locale->get_month($month), $year)
            );
        } ?>
        </select>
<?php
    }

    public function render_customer_filter()
    {
        $user_string = '';
        $user_id = '';

        if (!empty($_GET['_customer_user'])) {
            $user_id = absint($_GET['_customer_user']);
            $user = get_user_by('id', $user_id);


            $user_string = sprintf(
                esc_html__('%1$s (#%2$s &ndash; %3$s)', 'woocommerce'),
                $user->display_name,
                absint($user->ID),
                $user->user_email
                );
        } ?>
        <select class="wc-customer-search" name="_customer_user" data-placeholder="<?php esc_attr_e('Filter by registered customer', woocomerce); ?>" data-allow_clear ="true">
          <option value="<?php echo esc_attr($user_id); ?>" selected="selected"><?php echo wp_kses_post($user_string); ?></option>
        </select>
        <?php
    }
}
