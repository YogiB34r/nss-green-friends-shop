<?php

namespace GF\Orders;

class AdminListFilters
{

    public function init()
    {
        $this->hooks();
    }

    private function hooks()
    {
        add_action('init', function () {
            add_action('restrict_manage_posts', [$this, 'restrictManagePostHook']);
            add_action('pre_get_posts', [$this, 'preGetPostHook']);
        });
    }

    public function restrictManagePostHook()
    {
        $this->orderTypeFilter();
        $this->marketplaceFilter();
        $this->vendorIdFilter();
    }

    public function preGetPostHook($query)
    {
        $this->orderTypeFilterHandler($query);
        $this->marketplaceFilterHandler($query);
    }

    private function orderTypeFilter()
    {
        global $typenow;
        if ($typenow === 'shop_order'):
            $selected = $_GET['gfCreatedVia'] ?? '';
            ?>
            <select name="gfCreatedVia">
                <option<?=$selected === '1' ? ' selected' : ''?> value="1">Svi tipovi porudžbenice</option>
                <option<?=$selected === '2' ? ' selected' : ''?> value="2">WWW</option>
                <option<?=$selected === '3' ? ' selected' : ''?> value="3">PHONE</option>
            </select>
        <?php
        endif;
    }

    private function orderTypeFilterHandler($query)
    {
        $orderTypeFilter = $_GET['gfCreatedVia'] ?? '';
        $value = null;
        switch ($orderTypeFilter) {
            case '1':
                $value = null;
                break;
            case '2':
                $value = 'checkout';
                break;
            case '3':
                $value = 'admin';
                break;
        }
        if ($value) {
            $metaQuery[] = [ // Add to "meta query"
                'meta_key' => '_created_via',
                'value' => $value,
            ];
            $query->set('meta_query', $metaQuery);
        }
    }

    private function marketplaceFilter()
    {
        global $typenow;
        if ($typenow === 'shop_order'):
            $selected = $_GET['gfMarketplace'] ?? '';
            ?>
            <select id="gfMarketplace" name="gfMarketplace">
                <option<?=$selected === '1' ? ' selected' : ''?> value="1">Marketplace narudzbenice</option>
                <option<?=$selected === '2' ? ' selected' : ''?> value="2">Da</option>
                <option<?=$selected === '3' ? ' selected' : ''?> value="3">Ne</option>
            </select>
        <?php
        endif;
    }

    private function marketplaceFilterHandler($query)
    {
        $marketplaceOrder = $_GET['gfMarketplace'] ?? '';
        $vendorId = $_GET['gfVendorId'] ?? '';
        if ($vendorId !== '') {
            $marketplaceOrder = '2';
        }
        $formattedArray = [];
        switch ($marketplaceOrder) {
            case '1':
            case '3':
                break;
            case '2':
                global $wpdb;
                $sql = "SELECT `post_id` FROM {$wpdb->postmeta} WHERE `meta_key` = 'marketplaceVendor'";
                if ($vendorId !== '') {
                    $sql .= ' AND `meta_value` = ' . $_GET['gfVendorId'];
                }
                $posts = $wpdb->get_results($sql, ARRAY_N);
                foreach ($posts as $post) {
                    $formattedArray[] = $post[0];
                }
                $query->set('post__in', $formattedArray);
                break;
        }
    }

    private function vendorIdFilter()
    {
        global $typenow;
        if ($typenow === 'shop_order'):
            global $wpdb;
            $vendorDataTable = $wpdb->prefix . 'mpVendorData';
            $sql = "SELECT `vendorId` FROM {$vendorDataTable} WHERE `isActive` = 1";
            $activeVendors = $wpdb->get_results($sql);
            ?>
            <select id="vendorIdSelect" name="gfVendorId">
                <option value="">Izaberite Vendora</option>
                <?php
                foreach ($activeVendors as $vendor) :
                    $selected = '';
                    $userData = get_userdata($vendor->vendorId);
                    if (isset($_GET['gfVendorId']) && $vendor->vendorId == $_GET['gfVendorId']) {
                        $selected = 'selected';
                    }
                    ?>
                    <option <?=$selected?> value="<?=$vendor->vendorId?>"><?=$userData->display_name?></option>
                <?php
                endforeach;
                ?>
            </select>
        <?php
        endif;
    }

}