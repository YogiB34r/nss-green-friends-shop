<?php


namespace GF\Marketplace;


class Marketplace
{
    /**
     * @var \QM_DB|\wpdb
     */
    private $db;
    /**
     * @var string
     */
    private $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->tableName = $wpdb->prefix . 'mpVendorData';
    }

    public function init()
    {
        add_action('show_user_profile', [$this, 'marketplaceExtraFields']);
        add_action('edit_user_profile', [$this, 'marketplaceExtraFields']);
        add_action('personal_options_update', [$this, 'saveMarketplaceExtraFields']);
        add_action('edit_user_profile_update', [$this, 'saveMarketplaceExtraFields']);
        add_filter('woocommerce_package_rates', [$this, 'handleMarketplaceShipping'], 11, 2);
        add_action('init', function () {
            add_action('save_post', [$this, 'markOrderAsMarketplace'], 10, 2);
            add_action('woocommerce_new_order',[$this, 'sendVendorEmailOnManualOrder'], 10, 1);
        });
        add_action('manage_shop_order_posts_custom_column', [$this, 'marketplaceColumnPopulate']);
        add_action('admin_footer-edit.php', [$this, 'marketplaceCustomScript']);
        add_filter('woocommerce_email_recipient_new_order', [$this,'changeEmailRecipient'], 10, 2);
    }

    public function activate()
    {
        $this->createTable();
        $this->createIndex('isActive');
        $this->createIndex('minFreeShippingCost');
    }

    private function createTable()
    {
        $charset = $this->db->get_charset_collate();
        $sql = "CREATE TABLE {$this->tableName} ( 
        `vendorId` BIGINT(20) UNIQUE NOT NULL,
        `companyName` VARCHAR(128) NOT NULL UNIQUE,
        `companyAddress` VARCHAR(128) NOT NULL, 
        `bankAccountNumber` VARCHAR(30) NOT NULL, 
        `minFreeShippingCost` INT(5) DEFAULT NULL,
        'email' varchar (64) NOT NULL,
        `isActive` INT(1) DEFAULT 1,
        `shippingPrices` BLOB, 
        PRIMARY KEY (`vendorId`)){$charset};";
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");
        dbDelta($sql);
    }

    private function createIndex($columnName)
    {
        $sql = "CREATE INDEX {$columnName} ON {$this->tableName} ({$columnName}) ";
        $this->db->query($sql);
    }

    public function marketplaceExtraFields($user)
    {
        $vendorData = $this->getByVendorId($user->ID) ?? [];
        $companyName = $vendorData['companyName'] ?? '';
        $companyAddress = $vendorData['companyAddress'] ?? '';
        $bankAccountNumber = $vendorData['bankAccountNumber'] ?? '';
        $minFreeShippingCost = $vendorData['minFreeShippingCost'] ?? '';
        $shippingPrices = unserialize($vendorData['shippingPrices'], ['allowedClasses' => false]) ?? [];
        $email = $vendorData['email'] ?? '';
        $isActive = $vendorData['isActive'] ?? '0';
        $checked = '';
        if ($isActive === '1') {
            $checked = 'checked';
        }

        if (isset($user->get_role_caps()['supplier'])):?>
            <div class="marketplaceWrapper">
                <h2>Marektplace</h2>
                <input <?=$checked?> type="checkbox" name="marketPlaceActive" id="marketPlaceActive" value="1">
                <label for="marketPlaceActive">Aktiviraj</label>
                <h4>Podaci za plaćanje</h4>
                <div>
                    <label for="companyName">Ime Firme</label>
                    <input value="<?=$companyName?>" type="text" id="companyName" name="companyName">
                </div>
                <div>
                    <label for="companyAddress">Adresa firme</label>
                    <textarea rows="4" cols="30" type="text" id="companyAddress"
                              name="companyAddress"><?=$companyAddress?></textarea>
                </div>
                <div>
                    <label for="companyBankAccount">Broj računa</label>
                    <input value="<?=$bankAccountNumber?>" type="text" id="companyBankAccount"
                           name="companyBankAccount">
                </div>
                <div>
                    <label for="companyEmail">Email za dostavu porudžbenica</label>
                    <input value="<?=$email?>" type="email" id="companyEmail" name="companyEmail">
                </div>
                <h4>Dostava</h4>
                <div>
                    <label for="freeShipping">Minimalan iznos za besplatnu dostavu(RSD)</label>
                    <input value="<?=$minFreeShippingCost?>" type="number" name="freeShipping" id="freeShipping">
                </div>
                <h4>Cene dostave</h4>
                <table>
                    <thead>
                    <tr>
                        <th>Kilaža</th>
                        <th>Cena</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    for ($i = 0; $i < 8; $i++): ?>
                        <tr>
                            <td>
                                <input type="number" step="any" value="
                                <?php if (isset($shippingPrices[$i])){echo $shippingPrices[$i]['weight'];}?>"
                                       name="<?='shippingPriceTable[' . $i . '][weight]'?>">
                            </td>
                            <td>
                                <input type="number" step="any" value="
                                <?php if (isset($shippingPrices[$i])){echo $shippingPrices[$i]['price'];}?>"
                                       name="<?='shippingPriceTable[' . $i . '][price]'?>">
                            </td>
                        </tr>
                    <?php
                    endfor; ?>
                    </tbody>
                </table>
                <label for="overWeightShipping">Cena po kilogramu preko poslednje unešene kilaže</label>
                <input id="overWeightShipping" type="number" name="shippingPriceTable[overWeightPrice]"
                       value="<?php if (isset($shippingPrices['overWeightPrice'])){echo $shippingPrices['overWeightPrice'];}?>">
            </div>
        <?php
        endif;
    }

    public function saveMarketplaceExtraFields($userId)
    {
        $user = get_user_by('ID', $userId);
        $data['vendorId'] = (int)$userId;
        $data['isActive'] = 0;
        if (in_array('supplier', $user->get_role_caps(), false)) {
            if (empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-user_' . $userId)) {
                return false;
            }
            if (!current_user_can('edit_user', $userId)) {
                return false;
            }
            if (isset($_POST['companyName']) && $_POST['companyName'] !== '') {
                $data['companyName'] = $_POST['companyName'];
            }
            if (isset($_POST['companyAddress']) && $_POST['companyAddress'] !== '') {
                $data['companyAddress'] = $_POST['companyAddress'];
            }
            if (isset($_POST['companyBankAccount']) && $_POST['companyBankAccount'] !== '') {
                $data['companyBankAccount'] = $_POST['companyBankAccount'];
            }
            if (isset($_POST['companyEmail']) && $_POST['companyEmail'] !== '') {
                $data['companyEmail'] = $_POST['companyEmail'];
            }
            if (isset($_POST['freeShipping']) && $_POST['freeShipping'] !== '') {
                $data['minShippingPrice'] = (int)$_POST['freeShipping'];
            }
            if ($_POST['marketPlaceActive']) {
                $data['isActive'] = 1;
            }
            if (count($_POST['shippingPriceTable']) > 0) {
                foreach ($_POST['shippingPriceTable'] as $key => $value) {
                    if ($value['price'] !== '' && $value['weight'] !== '') {
                        continue;
                    }
                    unset($_POST['shippingPriceTable'][$key]);
                }
                $data['shippingPrices'] = serialize($_POST['shippingPriceTable']);
            }
            if (count($this->getByVendorId($userId)) > 0) {
                $this->update($data);
            } else {
                $this->insert($data);
            }
        }

        return true;
    }

    private function insert(array $data)
    {
        $this->db->insert($this->tableName,
            [
                'vendorId' => $data['vendorId'],
                'companyName' => $data['companyName'],
                'companyAddress' => $data['companyAddress'],
                'bankAccountNumber' => $data['companyBankAccount'],
                'minFreeShippingCost' => $data['minShippingPrice'],
                'email' => $data['companyEmail'],
                'isActive' => $data['isActive'],
                'shippingPrices' => $data['shippingPrices']
            ], ['%d', '%s', '%s', '%s', '%d', '%s', '%d', '%s']);
    }

    private function update(array $data)
    {
        $this->db->update($this->tableName,
            [
                'vendorId' => $data['vendorId'],
                'companyName' => $data['companyName'],
                'companyAddress' => $data['companyAddress'],
                'bankAccountNumber' => $data['companyBankAccount'],
                'minFreeShippingCost' => $data['minShippingPrice'],
                'email' => $data['companyEmail'],
                'isActive' => $data['isActive'],
                'shippingPrices' => $data['shippingPrices']
            ], ['vendorId' => $data['vendorId']], ['%d', '%s', '%s', '%s', '%d', '%s', '%d', '%s']);
    }

    public function getByVendorId(int $vendorId)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE `vendorId` = {$vendorId}";
        return $this->db->get_results($sql, ARRAY_A)[0] ?? [];
    }

    public function handleMarketplaceShipping($rates)
    {
        $cartContents = WC()->cart->get_cart_contents();
        $cartWeight = WC()->cart->cart_contents_weight;

        //if there is custom cost set for products remove their weight from cart because Shipping class will calculate extra cost
        $overrides = $this->settingCustomShippingPriceOverride($cartWeight, $cartContents);
        $cartWeight = $overrides['cartWeight'];

        $suppliers = [];
        foreach ($cartContents as $item) {
            $product = $item['data'];
            if ($product instanceof \WC_Product_Variation){
                $supplierId = $product->parent->get_meta('supplier');
            } else {
                $supplierId = (int)$product->get_meta('supplier');
            }
            if (!in_array($supplierId, $suppliers, true)) {
                $suppliers[] = $supplierId;
            }
        }
        //if there is more than one supplier there is no need for marketplace logic
        if (count($suppliers) > 1) {
            unset($rates['free_shipping:11'], $rates['flat_rate:12']);
            return $rates;
        }
        $minPrice = null;
        $vendor = $this->getByVendorId($suppliers[0]);

        if (isset($vendor['isActive']) && $vendor['isActive'] === '1') {
            $minPrice = (int)$vendor['minFreeShippingCost'];
            if ($minPrice) {
                $cartPrice = (int)WC()->cart->get_totals()['cart_contents_total'];
                if ($cartPrice >= $minPrice) {
                    /** @var \WC_Shipping_Rate $rate */
                    foreach ($rates as $key => $rate) {
                        if ($rate->get_method_id() === 'free_shipping') {
                            continue;
                        }
                        unset($rates[$key]);
                    }
                    return $rates;
                }
            }
            $shippingPriceTable = unserialize($vendor['shippingPrices'], ['allowed_classes' => false]) ?? [];
            $overWeightPrice = (int)$shippingPriceTable['overWeightPrice'];
            unset($shippingPriceTable['overWeightPrice']);
            $elementCount = count($shippingPriceTable);

            if ( $elementCount > 0) {
                foreach ($shippingPriceTable as $key => $value) {
                    if ((float)$value['weight'] >= $cartWeight) {
                        $maxWeight = (float)$value['weight'];
                        $minWeight = (float)$shippingPriceTable[$key - 1]['weight'];
                        $label = $minWeight . ' - ' . $maxWeight . ' kg';
                        $shippingPrice = (float)$value['price'];
                        break;
                    }
                    if ($overWeightPrice !== 0 && $key === $shippingPriceTable[$elementCount-1] && ((float)$value['weight'] < $cartWeight)) {
                        $shippingPrice = (float)$value['price'] + ($cartWeight - (float)$value['weight']) * $overWeightPrice;
                        $label = sprintf('Preko %d kg (%d) + %ddin po kg',
                            (float)$value['weight'], (int)$value['price'], $overWeightPrice);
                    } else {
                        $maxWeight = (float)$value['weight'];
                        $label = 'Preko ' . $maxWeight . ' kg';
                        $shippingPrice = (float)$value['price'];
                    }
                }
                /** @var \WC_Shipping_Rate $rate */
                foreach ($rates as $index => $rate) {
                    if ($rate->get_id() === 'flat_rate:12') {
                        $rate->set_label($label);
                        $rate->set_cost($shippingPrice);
                        continue;
                    }
                    unset($rates[$index]);
                }
                return $rates;
            }
        }
        unset($rates['free_shipping:11'], $rates['flat_rate:12']);
        return $rates;
    }

    public function markOrderAsMarketplace($postId, \WP_Post $post)
    {
        //@todo send email to vendor
        if ($post->post_type === 'shop_order') {
            $suppliers = [];
            $order = wc_get_order($postId);
            $items = $order->get_items();
            /** @var \WC_Order_Item_Product $item */
            foreach ($items as $item) {
                $product = wc_get_product($item->get_product_id());
                $supplierId = (int)$product->get_meta('supplier');
                if (!in_array($supplierId, $suppliers, true)) {
                    $suppliers[] = $supplierId;
                }
            }
            //if there is only one supplier this order is maybe marketplace order
            if (count($suppliers) === 1) {
                $vendor = $this->getByVendorId($suppliers[0]);
                if (isset($vendor['isActive']) && $vendor['isActive'] === '1') {
                    $order->update_meta_data('marketplaceVendor', $suppliers[0]);
                    $order->save();

                }
            }
        }
    }

    public function sendVendorEmailOnManualOrder($orderId)
    {
      $order = wc_get_order($orderId);
       if ($order->get_meta('marketplaceVendor',true) !== ''){
           WC()->mailer()->emails['WC_Email_New_Order']->trigger( $order->get_id(), $order );
       }
    }

    /**
     * This is only valid place to get order in order list so we use it to add empty span to mark order as marketplace
     * in the list
     * @param $column
     */
    public function marketplaceColumnPopulate($column)
    {
        $marketPlaceField = get_post_meta(get_the_ID(), 'marketplaceVendor', true);
        if ($marketPlaceField !== '') {
            echo '<span class="marketplace"></span>';
        }
    }

    public function marketplaceCustomScript()
    {
        ?>
        <script>
            let marketplaceIndicators = document.getElementsByClassName('marketplace');
            Array.prototype.forEach.call(marketplaceIndicators, function (elem) {
                elem.parentElement.parentElement.style.backgroundColor = "rgba(255, 204, 204, 1)"
            })
        </script>
        <?php
    }

    public function changeEmailRecipient($recipient, $order)
    {
        // Bail on WC settings pages since the order object isn't yet set yet
        $page = $_GET['page'] = $_GET['page'] ?? '';
        if ('wc-settings' === $page) {
            return $recipient;
        }

        $marketPlaceField = get_post_meta($order->ID, 'marketplaceVendor', true);
        if ($marketPlaceField !== '') {
            $vendor = $this->getByVendorId($marketPlaceField);
            $recipient = $vendor['email'];
        }
        return $recipient;
    }

    /**
     * Checks for custom price in product meta
     * @param \WC_Product $product
     * @return bool|string
     */
    private function getCustomShippingPrice(\WC_Product $product)
    {
        if ($product instanceof \WC_Product_Variation) {
            $product = wc_get_product($product->get_parent_id());
        }

        $customCost = $product->get_meta('customShippingPrice', true);
        if (strlen($customCost) > 0) {
            return $customCost;
        }
        return false;
    }

    /**
     * Reduce cart weight used for calculating weight based shipping if product has custom shipping price,
     * returns updated cart weight and total cost of custom shipping
     * @param $cartWeight
     * @param $cartContents
     * @return array
     */
    private function settingCustomShippingPriceOverride($cartWeight, $cartContents)
    {
        $customCost = 0;
        /** @var \WC_Product $product */
        foreach ($cartContents as $cartContent) {
            $product = $cartContent['data'];
            //If there is more than one product with custom shipping price add price for each of them to custom cost total
            if ($cartContent['quantity'] > 1) {
                if ($this->getCustomShippingPrice($product)) {
                    for ($i = 1; $i <= $cartContent['quantity']; $i++) {
                        $customCost += (int)$this->getCustomShippingPrice($product);
                        $productWeight = (float)$product->get_weight();

                        /*
                        Remove weight of product with special price from cart weight so items with normal shipping
                        cost can have valid shipping price based on its weight
                        */
                        $cartWeight -= $productWeight;
                    }
                }
                continue;
            }
            if ($this->getCustomShippingPrice($product)) {
                $customCost += (int)$this->getCustomShippingPrice($product);
                $productWeight = (float)$product->get_weight();
                $cartWeight -= $productWeight;
            }
        }
        return [
            'cartWeight' => $cartWeight,
            'customShippingCost' => $customCost
        ];
    }
}