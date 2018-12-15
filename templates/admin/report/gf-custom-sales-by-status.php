<?php

if (! class_exists(WC_Admin_Report)) {
    require_once(WC()->plugin_path().'/includes/admin/reports/class-wc-admin-report.php');
}


function sales_by_status_custom_callback()
{
    $customStatusReport = new Gf_Custom_Sales_By_Status();

    $customStatusReport->output_report();
}


class Gf_Custom_Sales_By_Status extends WC_Admin_Report
{

    /**
     * Chart colors.
     *
     * @var array
     */
    public $chart_colours = array();

    /**
     * Selected statuses.
     *
     * @var array
     */
    public $show_status;
    /**
     * Item sales.
     *
     * @var array
     */
    private $item_sales = array();

    /**
     * Item sales and times.
     *
     * @var array
     */
    private $item_sales_and_times = array();

    /**
     * Order items.
     *
     * @var array
     */
    private $order_items;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (isset($_GET['show_status'])) {
            $this->show_status = $_GET['show_status'];
        }
    }


    /**
     * Get the legend for the main status sidebar.
     *
     * @return array
     */
    public function get_chart_legend()
    {
        if (empty($this->show_status)) {
            return array();
        }

        $legend = array();
        $index  = 0;

        foreach ($this->show_status as $status) {
            foreach ($this->order_items as $item) {
                if ($status === $item->post_status) {
                    $productOrderStatusIds[] = $item->product_id;
                }
            }
            $product_ids = array_unique($productOrderStatusIds);

            foreach ($product_ids as $id) {
                if (isset($this->item_sales[ $id ])) {
                    $total += $this->item_sales[ $id ];
                }
            }

            $legend[] = array(
                /* translators: 1: total items sold 2: category name */
                'title'            => sprintf(__('%1$s sales in status %2$s', 'woocommerce'), '<strong>' . wc_price($total) . '</strong>', $status),
                'color'            => isset($this->chart_colours[ $index ]) ? $this->chart_colours[ $index ] : $this->chart_colours[0],
                'highlight_series' => $index,
            );

            $index++;
            unset($total);
            unset($productOrderStatusIds);
        }


        return $legend;
    }

    /**
     * Output the report.
     */
    public function output_report()
    {
        $ranges = array(
            'year'       => __('Year', 'woocommerce'),
            'last_month' => __('Last month', 'woocommerce'),
            'month'      => __('This month', 'woocommerce'),
            '7day'       => __('Last 7 days', 'woocommerce'),
        );

        $this->chart_colours = array( '#3498db', '#34495e', '#1abc9c', '#2ecc71', '#f1c40f', '#e67e22', '#e74c3c', '#2980b9', '#8e44ad', '#2c3e50', '#16a085', '#27ae60', '#f39c12', '#d35400');

        $current_range = ! empty($_GET['range']) ? sanitize_text_field(wp_unslash($_GET['range'])) : '7day';

        if (! in_array($current_range, array( 'custom', 'year', 'last_month', 'month', '7day' ))) {
            $current_range = '7day';
        }

        $this->check_current_range_nonce($current_range);
        $this->calculate_current_range($current_range);

        // Get item sales data.
        if (!empty($this->show_status)) {
            $order_items = $this->get_order_report_data(
                array(
                    'data'         => array(
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
                            'name'            => 'order_item_amount',
                        ),
                        'post_date'   => array(
                            'type'     => 'post_data',
                            'function' => '',
                            'name'     => 'post_date',
                        ),
                        'post_status'   => array(
                            'type'     => 'post_data',
                            'function' => '',
                            'name'     => 'post_status',
                        ),
                    ),
                    'group_by'     => 'ID, product_id, post_date',
                    'query_type'   => 'get_results',
                    'filter_range' => true,
                )
            );

            $this->item_sales           = array();
            $this->item_sales_and_times = array();

            $this->order_items = $order_items;

            if (is_array($order_items)) {
                foreach ($order_items as $order_item) {
                    switch ($this->chart_groupby) {
                        case 'day':
                            $time = strtotime(date('Ymd', strtotime($order_item->post_date))) * 1000;
                            break;
                        case 'month':
                        default:
                            $time = strtotime(date('Ym', strtotime($order_item->post_date)) . '01') * 1000;
                            break;
                    }

                    $this->item_sales_and_times[ $time ][ $order_item->product_id ] = isset($this->item_sales_and_times[ $time ][ $order_item->product_id ]) ? $this->item_sales_and_times[ $time ][ $order_item->product_id ] + $order_item->order_item_amount : $order_item->order_item_amount;

                    $this->item_sales[ $order_item->product_id ] = isset($this->item_sales[ $order_item->product_id ]) ? $this->item_sales[ $order_item->product_id ] + $order_item->order_item_amount : $order_item->order_item_amount;
                }
            }
        }

        include WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php';
    }

    /**
     * Get chart widgets.
     *
     * @return array
     */
    public function get_chart_widgets()
    {
        return array(
            array(
                'title'    => __('Order status', 'woocommerce'),
                'callback' => array( $this, 'status_widget' ),
            ),
        );
    }

    /**
     * Output status widget.
     */
    public function status_widget()
    {
        $orders_status = array('wc-completed','wc-processing','wc-on-hold', 'wc-cekaseuplata' , 'wc-u-pripremi' , 'wc-u-pripremiplaceno' , 'wc-u-obradi', 'wc-poslato', 'wc-naruceno', 'wc-spz-pakovanje' , 'wc-spz-slanje' , 'wc-isporuceno' , 'wc-finalizovano' , 'wc-reklamacija-pnns' , 'wc-stornirano-pn' , 'wc-vracena-posiljka' , 'wc-reklamacija' , 'wc-stornirano'); ?>
        <form method="GET">
            <div>
                <select multiple="multiple" data-placeholder="<?php esc_attr_e('Select status&hellip;', 'woocommerce'); ?>" class="wc-enhanced-select" id="show_categories" name="show_status[]" style="width: 205px;">
                    <?php foreach ($orders_status as $status): ?>
                        <option <?php echo (in_array($status, $this->show_status)) ? 'selected ' : ''; ?> value="<?php echo $status ?>"><?php echo $status ?></option>
                    <?php endforeach ?>
                </select>
                <?php // @codingStandardsIgnoreStart?>
                <a href="#" class="select_none"><?php esc_html_e('None', 'woocommerce'); ?></a>
                <a href="#" class="select_all"><?php esc_html_e('All', 'woocommerce'); ?></a>
                <button type="submit" class="submit button" value="<?php esc_attr_e('Show', 'woocommerce'); ?>"><?php esc_html_e('Show', 'woocommerce'); ?></button>
                <input type="hidden" name="range" value="<?php echo (! empty($_GET['range'])) ? esc_attr(wp_unslash($_GET['range'])) : ''; ?>" />
                <input type="hidden" name="start_date" value="<?php echo (! empty($_GET['start_date'])) ? esc_attr(wp_unslash($_GET['start_date'])) : ''; ?>" />
                <input type="hidden" name="end_date" value="<?php echo (! empty($_GET['end_date'])) ? esc_attr(wp_unslash($_GET['end_date'])) : ''; ?>" />
                <input type="hidden" name="page" value="<?php echo (! empty($_GET['page'])) ? esc_attr(wp_unslash($_GET['page'])) : ''; ?>" />
                <input type="hidden" name="tab" value="<?php echo (! empty($_GET['tab'])) ? esc_attr(wp_unslash($_GET['tab'])) : ''; ?>" />
                <input type="hidden" name="report" value="<?php echo (! empty($_GET['report'])) ? esc_attr(wp_unslash($_GET['report'])) : ''; ?>" />
                <?php // @codingStandardsIgnoreEnd?>
            </div>
            <script type="text/javascript">
                jQuery(function(){
                    // Select all/None
                    jQuery( '.chart-widget' ).on( 'click', '.select_all', function() {
                        jQuery(this).closest( 'div' ).find( 'select option' ).attr( 'selected', 'selected' );
                        jQuery(this).closest( 'div' ).find('select').change();
                        return false;
                    });

                    jQuery( '.chart-widget').on( 'click', '.select_none', function() {
                        jQuery(this).closest( 'div' ).find( 'select option' ).removeAttr( 'selected' );
                        jQuery(this).closest( 'div' ).find('select').change();
                        return false;
                    });
                });
            </script>
        </form>
        <?php
    }

    /**
     * Output an export link.
     */
    public function get_export_button()
    {
        $current_range = ! empty($_GET['range']) ? sanitize_text_field(wp_unslash($_GET['range'])) : '7day'; ?>
        <a
            href="#"
            download="report-<?php echo esc_attr($current_range); ?>-<?php echo esc_attr(date_i18n('Y-m-d', current_time('timestamp'))); ?>.csv"
            class="export_csv"
            data-export="chart"
            data-xaxes="<?php esc_attr_e('Date', 'woocommerce'); ?>"
            data-groupby="<?php echo esc_attr($this->chart_groupby); ?>"
        >
            <?php esc_html_e('Export CSV', 'woocommerce'); ?>
        </a>
        <?php
    }

    /**
     * Get the main chart.
     */
    public function get_main_chart()
    {
        global $wp_locale;

        if (empty($this->show_status)) {
            ?>
            <div class="chart-container">
                <p class="chart-prompt"><?php esc_html_e('Choose a status to view stats', 'woocommerce'); ?></p>
            </div>
            <?php
        } else {
            $chart_data = array();
            $index      = 0;

            foreach ($this->show_status as $status) {
                foreach ($this->order_items as $item) {
                    if ($status === $item->post_status) {
                        $productOrderStatusIds[] = $item->product_id;
                    }
                }

                $product_ids = array_unique($productOrderStatusIds);

                $status_chart_data = array();

                for ($i = 0; $i <= $this->chart_interval; $i ++) {
                    $interval_total = 0;

                    switch ($this->chart_groupby) {
                        case 'day':
                            $time = strtotime(date('Ymd', strtotime("+{$i} DAY", $this->start_date))) * 1000;
                            break;
                        case 'month':
                        default:
                            $time = strtotime(date('Ym', strtotime("+{$i} MONTH", $this->start_date)) . '01') * 1000;
                            break;
                    }

                    foreach ($product_ids as $id) {
                        if (isset($this->item_sales_and_times[ $time ][ $id ])) {
                            $interval_total += $this->item_sales_and_times[ $time ][ $id ];
                        }
                    }

                    $status_chart_data[] = array( $time, (float) wc_format_decimal($interval_total, wc_get_price_decimals()) );
                }

                $chart_data[ $status ]['status'] = $status;
                $chart_data[ $status ]['data']     = $status_chart_data;

                $index++;
                unset($productOrderStatusIds);
            } ?>
            <div class="chart-container">
                <div class="chart-placeholder main"></div>
            </div>
            <?php // @codingStandardsIgnoreStart?>
            <script type="text/javascript">
                var main_chart;

                jQuery(function(){
                    var drawGraph = function( highlight ) {
                        var series = [
                            <?php
                                $index = 0;
            foreach ($chart_data as $data) {
                $color  = isset($this->chart_colours[ $index ]) ? $this->chart_colours[ $index ] : $this->chart_colours[0];
                $width  = $this->barwidth / sizeof($chart_data);
                $offset = ($width * $index);
                $series = $data['data'];
                foreach ($series as $key => $series_data) {
                    $series[ $key ][0] = $series_data[0] + $offset;
                }
                echo '{
                                            label: "' . esc_js($data['status']) . '",
                                            data: jQuery.parseJSON( "' . json_encode($series) . '" ),
                                            color: "' . $color . '",
                                            bars: {
                                                fillColor: "' . $color . '",
                                                fill: true,
                                                show: true,
                                                lineWidth: 1,
                                                align: "center",
                                                barWidth: ' . $width * 0.75 . ',
                                                stack: false
                                            },
                                            ' . $this->get_currency_tooltip() . ',
                                            enable_tooltip: true,
                                            prepend_label: true
                                        },';
                $index++;
            } ?>
                        ];

                        if ( highlight !== 'undefined' && series[ highlight ] ) {
                            highlight_series = series[ highlight ];

                            highlight_series.color = '#9c5d90';

                            if ( highlight_series.bars ) {
                                highlight_series.bars.fillColor = '#9c5d90';
                            }

                            if ( highlight_series.lines ) {
                                highlight_series.lines.lineWidth = 5;
                            }
                        }

                        main_chart = jQuery.plot(
                            jQuery('.chart-placeholder.main'),
                            series,
                            {
                                legend: {
                                    show: false
                                },
                                grid: {
                                    color: '#aaa',
                                    borderColor: 'transparent',
                                    borderWidth: 0,
                                    hoverable: true
                                },
                                xaxes: [ {
                                    color: '#aaa',
                                    reserveSpace: true,
                                    position: "bottom",
                                    tickColor: 'transparent',
                                    mode: "time",
                                    timeformat: "<?php echo ('day' === $this->chart_groupby) ? '%d %b' : '%b'; ?>",
                                    monthNames: <?php echo json_encode(array_values($wp_locale->month_abbrev)); ?>,
                                    tickLength: 1,
                                    minTickSize: [1, "<?php echo $this->chart_groupby; ?>"],
                                    tickSize: [1, "<?php echo $this->chart_groupby; ?>"],
                                    font: {
                                        color: "#aaa"
                                    }
                                } ],
                                yaxes: [
                                    {
                                        min: 0,
                                        tickDecimals: 2,
                                        color: 'transparent',
                                        font: { color: "#aaa" }
                                    }
                                ],
                            }
                        );

                        jQuery('.chart-placeholder').resize();

                    }

                    drawGraph();

                    jQuery('.highlight_series').hover(
                        function() {
                            drawGraph( jQuery(this).data('series') );
                        },
                        function() {
                            drawGraph();
                        }
                    );
                });
            </script>
            <?php // @codingStandardsIgnoreEnd?>
            <?php
        }
    }
}
