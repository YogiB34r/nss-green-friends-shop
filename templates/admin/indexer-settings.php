<?php
/**
 * @var Elastica\Result $result
 * @var \GF\Search\Elastica\TermSearch $termSearch
 */
if (is_admin()) {
    $listTable = new Gf_Indexer_Wp_List_Table($indexer);
}

/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class Gf_Indexer_Wp_List_Table
{
    /**
     * @var \Search_List_Table
     */
    private $searchListTable;

    /**
     * Gf_Search_Wp_List_Table constructor.
     *
     * @param \GF\Search\Elastica\TermSearch $search
     */
    public function __construct(\GF\Search\Indexer\Indexer $indexer)
    {
        $this->searchListTable = new Search_List_Table([], $indexer);
        $this->list_table_page();
    }

    public function getSearchList()
    {
        return $this->searchListTable;
    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page()
    {
        $this->searchListTable->prepare_items();
        $args = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hierarchical' => 1,
            'hide_empty' => '0'
        );
        $cats = get_terms('product_cat', $args);
        ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Podešavanje indeksera</h2>
            <select class="categorySelect" name="categoryId">
                <?php foreach ($cats as $cat) : ?>
                    <option value="<?= $cat->term_id ?>" <?php if ($cat->term_id == $_GET['categoryId']) echo 'selected'?>>
                        <?= $cat->name ?></option>
                <?php endforeach; ?>
            </select>
            <input type="submit" id="filter-submit" class="button" value="filter">
            <?php $this->searchListTable->search_box('search', 'searchbox'); ?>
            <?php $this->searchListTable->display(); ?>
        </div>
        <?php
    }
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Search_List_Table extends WP_List_Table
{
    /**
     * @var \GF\Search\Indexer\Indexer
     */
    private $indexer;

    public function __construct($args = array(), \GF\Search\Indexer\Indexer $indexer)
    {
        parent::__construct($args);
        $this->indexer = $indexer;
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
        $keywords = 'knjiga';
        $perPage = 10;

        $data = $this->parseData($keywords, $perPage);
        usort($data, array(&$this, 'sort_data'));
        $currentPage = $this->get_pagenum();

        $this->set_pagination_args(array(
            'total_items' => $this->indexer->getTotalCount(),
            'per_page' => $perPage
        ));
//        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function parseData($keywords, $perPage)
    {
        $data = [];


//        $this->indexer->removeNonExistentProducts();



        /* @var \Elastica\Result $term */
//        foreach ($this->indexer->getResultsTest($keywords, $perPage) as $term) {
////            $url = '<input type="text" style="display:none;" />';
////            if ($term->getData()['url'] !== '') {
////                $url = '<input readonly size="55" class="redirected" type="text" value="' . $term->getData()['url'] . '" />';
////            }
////            $url .= '<button style="display: none" data-query="'. $term->getData()['searchQuery'] .'" class="redirect">
////                    Snimi
////                </button>';
//            $data[] = array(
//                'id' => $term->getData()['postId'],
//                'name' => $term->getData()['name'],
//                'sku' => $term->getData()['sku'],
//                'status' => $term->getData()['status'],
//                'stockStatus' => $term->getData()['stockStatus'],
//                'regularPrice' => $term->getData()['regularPrice'],
//                'salePrice' => $term->getData()['salePrice'],
//                'sorting' => $term->getData()['order_data'],
////                'url' => $url,
//                'action' => '<a href="#" class="showRedirect">#</a>'
//            );
//        }

        return $data;
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
            'name' => 'naziv',
            'sku' => 'sku',
            'status' => 'status',
            'stockStatus' => 'stockStatus',
            'regularPrice' => 'regularPrice',
            'salePrice' => 'salePrice',
            'sorting' => 'sorting',
//            'url' => 'Preusmeren Na',
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
            'sku' => ['sku', false],
            'regularPrice' => ['regularPrice', false],
            'salePrice' => ['salePrice', false],
            'searchQuery' => ['searchQuery', false],
//            'count' => array('count', false)
        );
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
            case 'sorting':
                return print_r($item[$column_name]);
            case 'id':
            case 'sku':
            case 'name':
            case 'status':
            case 'stockStatus':
            case 'regularPrice':
            case 'salePrice':
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

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="searchQuery[]" value="%s" />', $item['id']
        );
    }

    public function process_bulk_action()
    {
        //@TODO process bulk delete action
    }
}
?>
<script>
    jQuery(document).ready(function () {
        jQuery('#search-submit').click(function() {
            if (jQuery('#searchbox-search-input').val() !== '') {
                location = location.origin + location.pathname + '?page=gf-indexer&query=' + jQuery('#searchbox-search-input').val();
            }
        });
        jQuery('#filter-submit').click(function() {
            location = location.origin + location.pathname + '?page=gf-indexer&categoryId=' + jQuery('.categorySelect').val();
        });
    });
</script>