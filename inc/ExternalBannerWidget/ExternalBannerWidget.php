<?php

namespace GF\ExternalBannerWidget;

//load_plugin_textdomain('gf-externalItemBannersWidget', '', plugins_url() . '/gf-externalItemBannersWidget/languages');

class ExternalBannerWidget
{
    private $wpdb;

    public function __construct(\wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
    }

    public function render_html()
    {
        $get_items_sql = "SELECT * FROM `wp_nss_external_banners_widget`";
        $data = $this->wpdb->get_results($get_items_sql);

        $template = 'vertical';
        $ref = 'blic';
        if (isset($_GET['template']) && $_GET['template'] === 'horizontal') {
            $template = 'horizontal';
        }
        if (isset($_GET['ref'])) {
            $ref = $_GET['ref'];
        }

        require(get_stylesheet_directory() . "/templates/externalBannersWidget/layout.phtml");

    }

    public function register_widget_options()
    {
        register_setting('gf-external-item-banners-widget-group', 'external-item-banners-widget-articles');
    }

    public function options_page()
    {
        ?>
        <div class="wrap">
            <h2>External item banners widget options</h2>

            <h3>NonStopShop insert article</h3>

            <form action="" method="post" class="external-item-banners-widget-Form" id="externalItemBannersWidgetForm">
                <label for="itemId">Article ID (required): </label>
                <input id="itemId" name="itemId" type="text" value="" class="regular-text" required/>

                <label for="title">Article title (required): </label>
                <input id="title" name="title" type="text" value="" class="regular-text " required/>

                <label for="description">Article description (required): </label>
                <textarea id="description" name="description" type="text" value="" class="regular-text" rows="5" required></textarea>

                <label for="salePrice">Article sale price:</label>
                <input id="salePrice" name="salePrice" type="text" value="" class="regular-text"/>

                <label for="regularPrice">Article regular price (required):</label>
                <input id="regularPrice" name="regularPrice" type="text" value="" class="regular-text" required/>

                <label for="categoryUrl">Article category URL (required):</label>
                <input id="categoryUrl" name="categoryUrl" type="text" value="" class="regular-text" required/>

                <label for="itemUrl">Article URL (required):</label>
                <input id="itemUrl" name="itemUrl" type="text" value="" class="regular-text" required/>

                <input type="submit" name="articleCreate" class="button button-primary" value="Create">
                <input type="submit" name="articleUpdate" class="button button-primary" value="Update">
            </form>
            <?php

            //create
            if (isset($_POST['articleCreate']) || isset($_POST['articleUpdate'])) {
                if (
                    isset($_POST['itemId']) && !empty($_POST['itemId']) &&
                    isset($_POST['title']) && !empty($_POST['title']) &&
                    isset($_POST['description']) && !empty($_POST['description']) &&
                    isset($_POST['salePrice']) &&
                    isset($_POST['regularPrice']) && !empty($_POST['regularPrice']) &&
                    isset($_POST['categoryUrl']) && !empty($_POST['categoryUrl']) &&
                    isset($_POST['itemUrl']) && !empty($_POST['itemUrl'])
                ) {
                    $itemId = $_POST['itemId'];
                    $title = $_POST['title'];
                    $description = $_POST['description'];
                    $salePrice = $_POST['salePrice'];
                    $regularPrice = $_POST['regularPrice'];
                    $categoryUrl = $_POST['categoryUrl'];
                    $itemUrl = $_POST['itemUrl'];

                    if (isset($_POST['articleCreate'])){
                        $sql_insert = "INSERT INTO wp_nss_external_banners_widget (itemId, title, description, salePrice, regularPrice, categoryUrl, itemUrl)
                        VALUES ({$itemId}, '{$title}', '{$description}', {$salePrice}, {$regularPrice}, '{$categoryUrl}', '{$itemUrl}')";
                        $insert = $this->wpdb->query($sql_insert);
                        echo '<div class="notice notice-success is-dismissible"><p>Article created!</p></div>';
                    }
                    if (isset($_POST['articleUpdate'])){
                        $sql_update = "UPDATE wp_nss_external_banners_widget 
                                   SET itemId = $itemId, title = '{$title}', description = '{$description}', salePrice = $salePrice, regularPrice = $regularPrice, categoryUrl = '{$categoryUrl}', itemUrl = '{$itemUrl}' 
                                   WHERE itemId LIKE $itemId";
                        $update = $this->wpdb->query($sql_update);
                        echo '<div class="notice notice-success is-dismissible"><p>Article updated!</p></div>';
                    }

                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>You must fill in all required field fields</p></div>';
                }
            }

            //delete
            if(isset($_POST['articleDelete'])){
                if(isset($_POST['itemId']) && !empty($_POST['itemId'])){
                    $itemId = $_POST['itemId'];
                    $sql_delete = "DELETE FROM wp_nss_external_banners_widget WHERE itemId LIKE $itemId";
                    $delete = $this->wpdb->query($sql_delete);
                    echo '<div class="notice notice-success is-dismissible"><p>Article deleted!</p></div>';
                }
            }

            ?>
            <?php
            $get_items_sql = "SELECT * FROM `wp_nss_external_banners_widget`";
            $items_result = $this->wpdb->get_results($get_items_sql);

            ?>
            <h3>Product list</h3>
            <table class="widefat" cellspacing="0">
                <th>Item ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Sale price</th>
                <th>Regular price</th>
                <th>Category url</th>
                <th>Item URL</th>

                <?php foreach ($items_result as $item): ?>
                    <tr>
                        <td><?= $item->itemId ?></td>
                        <td><?= $item->title ?></td>
                        <td><?= $item->description ?></td>
                        <td><?= $item->salePrice ?></td>
                        <td><?= $item->regularPrice ?></td>
                        <td><?= $item->categoryUrl ?></td>
                        <td><?= $item->itemUrl ?></td>
                        <td>
                            <script>
                                function passingValuesForUpdate(){
                                    document.getElementById('itemId').value = '<?= $item->itemId ?>';
                                    document.getElementById('title').value = '<?= $item->title ?>';
                                    document.getElementById('description').value = '<?= $item->description ?>';
                                    document.getElementById('salePrice').value = '<?= $item->salePrice ?>';
                                    document.getElementById('regularPrice').value = '<?= $item->regularPrice ?>';
                                    document.getElementById('categoryUrl').value = '<?= $item->categoryUrl ?>';
                                    document.getElementById('itemUrl').value = '<?= $item->itemUrl ?>';
                                }
                            </script>
                            <a href="#" class="button button-secondary" value="Edit" onclick="passingValuesForUpdate()">Edit</a>
                        </td>
                        <td>
                            <form action="" method="post">
                                <input type="hidden" name="itemId" value="<?= $item->itemId ?>">
                                <input type="submit" class="button button-secondary" value="Delete" name="articleDelete" >
                            </form>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php
    }
}

