<?php


if (!class_exists('Wc_Admin_Report')) {
    require_once(WC()->plugin_path().'/includes/admin/reports/class-wc-admin-report.php');
}

if (!class_exists('WC_Report_Sales_By_Product')) {
    require_once(WC()->plugin_path().'/includes/admin/reports/class-wc-report-sales-by-product.php');
}

function sales_by_product_custom_callback()
{
    $customReport = new Gf_Custom_Sales_By_Product();

    $customReport->output_report();
}

class Gf_Custom_Sales_By_Product extends WC_Report_Sales_By_Product
{
    public function output_report()
    {
        $ranges = array(
          'year'         => __('Year', 'woocommerce'),
          'last_month'   => __('Last month', 'woocommerce'),
          'month'        => __('This month', 'woocommerce'),
        );

        $current_range = ! empty($_GET['range']) ? sanitize_text_field($_GET['range']) : 'month';
        if (! in_array($current_range, array( 'custom', 'year', 'last_month', '7day' ))) {
            $current_range = 'month';
        }
        $this->check_current_range_nonce($current_range);
        $this->calculate_current_range($current_range);
        $this->chart_colours = array(
            'sales_amount' => '#3498db',
            'item_count'   => '#d4d9dc',
        );
        // $hide_sidebar = true;

        include('gf-html-report-by-date.php');
    }

    public function products_widget()
    {
        ?>
        <h4 class="section_title"><span><?php esc_html_e('Product search', 'woocommerce'); ?></span></h4>
        <div class="section">
            <form method="GET">
                <div>
                    <?php // @codingStandardsIgnoreStart?>
                    <select class="wc-product-search" style="width:203px;" multiple="multiple" id="product_ids" name="product_ids[]" data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'woocommerce'); ?>" data-action="woocommerce_json_search_products_and_variations"></select>
                    <button type="submit" class="submit button" value="<?php esc_attr_e('Show', 'woocommerce'); ?>"><?php esc_html_e('Show', 'woocommerce'); ?></button>
                    <input type="hidden" name="range" value="<?php echo (! empty($_GET['range'])) ? esc_attr($_GET['range']) : ''; ?>" />
                    <input type="hidden" name="start_date" value="<?php echo (! empty($_GET['start_date'])) ? esc_attr($_GET['start_date']) : ''; ?>" />
                    <input type="hidden" name="end_date" value="<?php echo (! empty($_GET['end_date'])) ? esc_attr($_GET['end_date']) : ''; ?>" />
                    <input type="hidden" name="page" value="<?php echo (! empty($_GET['page'])) ? esc_attr($_GET['page']) : ''; ?>" />
                    <input type="hidden" name="tab" value="<?php echo (! empty($_GET['tab'])) ? esc_attr($_GET['tab']) : ''; ?>" />
                    <input type="hidden" name="report" value="<?php echo (! empty($_GET['report'])) ? esc_attr($_GET['report']) : ''; ?>" />
                    <?php wp_nonce_field('custom_range', 'wc_reports_nonce', false); ?>
                    <?php // @codingStandardsIgnoreEnd?>
                </div>
            </form>
        </div>
        <h4 class="section_title"><span><?php esc_html_e('Top sellers', 'woocommerce'); ?></h4>
        <div class="section">
            <table cellspacing="0">
                <?php

                $defaultTopSellersData = array(
                        'data'         => array(
                            '_product_id' => array(
                                'type'            => 'order_item_meta',
                                'order_item_type' => 'line_item',
                                'function'        => '',
                                'name'            => 'product_id',
                            ),
                            '_qty'        => array(
                                'type'            => 'order_item_meta',
                                'order_item_type' => 'line_item',
                                'function'        => 'SUM',
                                'name'            => 'order_item_qty',
                            ),
                        ),
                        'order_by'     => 'order_item_qty DESC',
                        'group_by'     => 'product_id',
                        'limit'        => 30,
                        'query_type'   => 'get_results',
                        'filter_range' => true,
                    );


        if (isset($_GET['best_selling_products']) && $_GET['best_selling_products'] !== 0) {
            foreach ($defaultTopSellersData as $key => $value) {
                if ($key === 'limit') {
                    $defaultTopSellersData[$key] = (int) $_GET['best_selling_products'];
                }
            }
        }



        $top_sellers = $this->get_order_report_data($defaultTopSellersData);

        if ($top_sellers) {
            // @codingStandardsIgnoreStart
            foreach ($top_sellers as $product) {
                echo '<tr class="' . (in_array($product->product_id, $this->product_ids) ? 'active' : '') . '">
                            <td class="count">' . esc_html($product->order_item_qty) . '</td>
                            <td class="name"><a href="' . esc_url(add_query_arg('product_ids', $product->product_id)) . '">' . esc_html(get_the_title($product->product_id)) . '</a></td>
                            <td class="sparkline">' . $this->sales_sparkline($product->product_id, 7, 'count') . '</td>
                        </tr>';
            }
            // @codingStandardsIgnoreEnd
        } else {
            echo '<tr><td colspan="3">' . esc_html__('No products found in range', 'woocommerce') . '</td></tr>';
        } ?>
            </table>
        </div>
        <h4 class="section_title"><span><?php esc_html_e('Top freebies', 'woocommerce'); ?></span></h4>
        <div class="section">
            <table cellspacing="0">
                <?php

                $defaultTopFreebiesData = array(
                        'data' => array(
                            '_product_id' => array(
                                'type'            => 'order_item_meta',
                                'order_item_type' => 'line_item',
                                'function'        => '',
                                'name'            => 'product_id',
                            ),
                            '_qty'        => array(
                                'type'            => 'order_item_meta',
                                'order_item_type' => 'line_item',
                                'function'        => 'SUM',
                                'name'            => 'order_item_qty',
                            ),
                        ),
                        'where_meta'   => array(
                            array(
                                'type'       => 'order_item_meta',
                                'meta_key'   => '_line_subtotal',
                                'meta_value' => '0',
                                'operator'   => '=',
                            ),
                        ),
                        'order_by'     => 'order_item_qty DESC',
                        'group_by'     => 'product_id',
                        'limit'        => 30,
                        'query_type'   => 'get_results',
                        'filter_range' => true,);


        if (isset($_GET['best_selling_products']) && $_GET['best_selling_products'] !== 0) {
            foreach ($defaultTopFreebiesData as $key => $value) {
                if ($key === 'limit') {
                    $defaultTopFreebiesData[$key] = (int) $_GET['best_selling_products'];
                }
            }
        }

        $top_freebies = $this->get_order_report_data($defaultTopFreebiesData);

        if ($top_freebies) {
            // @codingStandardsIgnoreStart
            foreach ($top_freebies as $product) {
                echo '<tr class="' . (in_array($product->product_id, $this->product_ids) ? 'active' : '') . '">
                            <td class="count">' . esc_html($product->order_item_qty) . '</td>
                            <td class="name"><a href="' . esc_url(add_query_arg('product_ids', $product->product_id)) . '">' . esc_html(get_the_title($product->product_id)) . '</a></td>
                            <td class="sparkline">' . $this->sales_sparkline($product->product_id, 7, 'count') . '</td>
                        </tr>';
            }
            // @codingStandardsIgnoreEnd
        } else {
            echo '<tr><td colspan="3">' . esc_html__('No products found in range', 'woocommerce') . '</td></tr>';
        } ?>
            </table>
        </div>
        <h4 class="section_title"><span><?php esc_html_e('Top earners', 'woocommerce'); ?></span></h4>
        <div class="section">
            <table cellspacing="0">
                <?php

                $defaultToEarnersData = array(
                        'data'=> array(
                            '_product_id' => array(
                                'type'            => 'order_item_meta',
                                'order_item_type' => 'line_item',
                                'function'        => '',
                                'name'            => 'product_id',
                            ),
                            '_line_total' => array(
                                'type'            => 'order_item_meta',
                                'order_item_type' => 'line_item',
                                'function'        => 'SUM',
                                'name'            => 'order_item_total',
                            ),
                        ),
                        'order_by'     => 'order_item_total DESC',
                        'group_by'     => 'product_id',
                        'limit'        => 30,
                        'query_type'   => 'get_results',
                        'filter_range' => true,
                );

        if (isset($_GET['best_selling_products']) && $_GET['best_selling_products'] !== 0) {
            foreach ($defaultToEarnersData as $key => $value) {
                if ($key === 'limit') {
                    $defaultToEarnersData[$key] = (int) $_GET['best_selling_products'];
                }
            }
        }

        $top_earners = $this->get_order_report_data($defaultToEarnersData);

        if ($top_earners) {
            // @codingStandardsIgnoreStart
            foreach ($top_earners as $product) {
                echo '<tr class="' . (in_array($product->product_id, $this->product_ids) ? 'active' : '') . '">
                            <td class="count">' . wc_price($product->order_item_total) . '</td>
                            <td class="name"><a href="' . esc_url(add_query_arg('product_ids', $product->product_id)) . '">' . esc_html(get_the_title($product->product_id)) . '</a></td>
                            <td class="sparkline">' . $this->sales_sparkline($product->product_id, 7, 'sales') . '</td>
                        </tr>';
            }
            // @codingStandardsIgnoreEnd
        } else {
            echo '<tr><td colspan="3">' . esc_html__('No products found in range', 'woocommerce') . '</td></tr>';
        } ?>
            </table>
        </div>
        <script type="text/javascript">
            jQuery('.section_title').click(function(){
                var next_section = jQuery(this).next('.section');

                if ( jQuery(next_section).is(':visible') )
                    return false;

                jQuery('.section:visible').slideUp();
                jQuery('.section_title').removeClass('open');
                jQuery(this).addClass('open').next('.section').slideDown();

                return false;
            });
            jQuery('.section').slideUp( 100, function() {
                <?php if (empty($this->product_ids)) : ?>
                    jQuery('.section_title:eq(1)').click();
                <?php endif; ?>
            });
        </script>
        <?php
    }
}
