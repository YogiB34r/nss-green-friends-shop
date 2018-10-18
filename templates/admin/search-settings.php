<?php
/**
 * @var Elastica\Result $result
 * @var \GF\Search\Elastica\TermSearch $termSearch
 */
if (is_admin()) {
    new Gf_Search_Wp_List_Table($termSearch);
}

/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class Gf_Search_Wp_List_Table
{
    /**
     * Gf_Search_Wp_List_Table constructor.
     *
     * @param \GF\Search\Elastica\TermSearch $search
     */
    public function __construct(\GF\Search\Elastica\TermSearch $search)
    {
        $this->list_table_page($search);
    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page($search)
    {
        $searchListTable = new Search_List_Table([], $search);
        $searchListTable->prepare_items();
        ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Podešavanje pretrage</h2>
            <?php $searchListTable->display(); ?>
        </div>
        <?php
    }
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Search_List_Table extends WP_List_Table
{
    /**
     * @var \GF\Search\Elastica\TermSearch
     */
    private $search;

    public function __construct($args = array(), \GF\Search\Elastica\TermSearch $search)
    {
        parent::__construct($args);
        $this->search = $search;
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
        $perPage = 10;
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
            'searchQuery' => 'Upit',
            'count' => 'Broj Ponavljanja',
            'url' => 'Preusmeren Na',
            'action' => 'Akcija',
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
            'searchQuery' => array('searchQuery', false),
            'count' => array('count', false)
        );
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
        $data = [];
        foreach ($this->search->getTerms() as $term) {
            $url = '<input type="text" style="display:none;" />';
            if ($term->getData()['url'] !== '') {
                $url = '<input readonly size="55" class="redirected" type="text" value="' . $term->getData()['url'] . '" />';
            }
            $url .= '<button style="display: none" data-query="'. $term->getData()['searchQuery'] .'" class="redirect">
                    Snimi
                </button>';
            $data[] = array(
                'id' => $term->id,
                'searchQuery' => $term->searchQuery,
                'count' => $term->count,
                'url' => $url,
                'action' => '<a href="#" class="showRedirect">Preusmeri</a>'
            );
        }

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
            case 'id':
            case 'searchQuery':
            case 'count':
            case 'action':
            case 'url':
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
        $orderby = 'searchQuery';
        $order = 'asc';
        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }
        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }
        $result = strcmp($a[$orderby], $b[$orderby]);
        if ($order === 'asc') {
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

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="searchQuery[]" value="%s" />', $item['id']
        );
    }

    /**
     * Delete a query record.
     *
     * @param int $id query ID
     */
    public static function delete_searchQuery( $id ) {
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
<script>
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
</script>