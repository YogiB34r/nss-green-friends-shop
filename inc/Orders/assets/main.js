const baseAjaxUrlOrders = gfData.ajaxUrl+'?action=gfOrdersTable&ajaxAction=getOrders';
const baseAjaxUrlCount = gfData.ajaxUrl+'?action=gfOrdersTable&ajaxAction=getPagesTotals';
let ajaxUrlWithFilters = baseAjaxUrlOrders;
let ajaxUrlWithFiltersForCount = baseAjaxUrlCount;

var table = jQuery('#orderTable').DataTable({
    'processing': true,
    'serverSide': true,
    'searching': false,
    'pageLength': 25,
    'lengthMenu': [ 10, 25, 50, 75, 100,250,500,1000 ],
    'ajax': ajaxUrlWithFilters,
    'dataSrc': function (data) {
        return data.data;
    },
    'dom': '<"top"flp<"clear">>rt<"bottom"ifp<"clear">>',
    'columns': [
        {
            data:'orderId',
            name:'orderId',
            className: 'selectCheckbox',
            orderable:false,
        },
        {
            data: "orderTitle",
            name: "orderTitle",
            orderable:false,
        },

        {
            data: "shippingMethod",
            name: "shippingMethod",
            orderable:false,
        },
        {
            data: "shippingTotal",
            name: "shippingTotal",
            orderable:false,
        },
        {
            data: "itemsTotal",
            name: "itemsTotal",
            orderable:false,
        },
        {
            data: "total",
            name: "total",
            orderable:false,
        },
        {
            data: "paymentMethod",
            name: "paymentMethod",
            orderable:false,
        },
        {
            data: "createdVia",
            name: "createdVia",
            orderable:false,
        },
        {
            data: "date",
            name: "date",
            orderable:false,
        },
        {
            data: "status",
            name: "status",
            orderable:false,
        },
        {
            data:"actions",
            name:"actions",
            orderable: false
        }
    ],
    drawCallback: function (data) {
        var api = this.api();
        var data = api.ajax.json();
        jQuery(api.column(1).footer()).html(
            'Zbir stranice'
        );
        jQuery(api.column(3).footer()).html(
            data.pageShippingTotal
        );
        jQuery(api.column(4).footer()).html(
            data.pageOrderSubtotals
        );
        jQuery(api.column(5).footer()).html(
            data.pageOrdersTotal
        );
    }
});
var filters = document.getElementsByClassName('filterSelect')
Array.prototype.forEach.call(filters, function (elem) {
    elem.addEventListener('change', function () {
        ajaxUrlWithFilters = baseAjaxUrlOrders
        ajaxUrlWithFiltersForCount += baseAjaxUrlCount
        refreshTable()
    })
})
var statusFilters = document.getElementsByClassName('statusSelect')
Array.prototype.forEach.call(statusFilters, function (elem) {
    elem.addEventListener('click', function (e) {
        ajaxUrlWithFilters = baseAjaxUrlOrders
        ajaxUrlWithFiltersForCount += baseAjaxUrlCount
        e.preventDefault()
        let value = e.target.getAttribute('value')
        if (value === ''){
            value = '-1';
        }
        ajaxUrlWithFilters += '&orderStatus=' + value
        ajaxUrlWithFiltersForCount += '&orderStatus=' + value
        refreshTable()
    })
})
jQuery(function () {
    var dateFormat = "mm/dd/yy",
        from = jQuery("#from")
            .datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                numberOfMonths: 1
            })
            .on("change", function () {
                to.datepicker("option", "minDate", getDate(this));
                refreshTable()
            }),
        to = jQuery("#to").datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            numberOfMonths: 1
        })
            .on("change", function () {
                from.datepicker("option", "maxDate", getDate(this));
                refreshTable()
            });

    function getDate(element) {
        var date;
        try {
            date = jQuery.datepicker.parseDate(dateFormat, element.value);
        } catch (error) {
            date = null;
        }

        return date;
    }

    let selectAllInput = jQuery('#selectAll');
    let submitBulk = jQuery('#submitBulk');
    selectAllInput.on('change',function(){
        if (jQuery(this).is(':checked')) {
            jQuery('.individualCheckbox').each(function(){
                jQuery(this).prop('checked',true);
            });
        } else {
            jQuery('.individualCheckbox').each(function(){
                jQuery(this).prop('checked',false);
            });
        }
    });
    submitBulk.on('click',function(){
        if(jQuery('#bulkActions').val() === '-1') {
            alert('Morate izabrati grupnu akciju');
            return;
        }
        let checkedInputs = [];
        jQuery('.individualCheckbox').each(function(){
            if (jQuery(this).is(':checked')) {
                checkedInputs.push(jQuery(this).data('id'));
            }
        });
        if (checkedInputs.length > 0) {
            jQuery.ajax({
                url: gfData.ajaxUrl + '?action=gfOrdersTable&ajaxAction=bulkActions',
                type: 'POST',
                data: {
                    orderIds: checkedInputs,
                    bulkAction: jQuery('#bulkActions').val()
                },
            }).done(function (response) {
                refreshTable();
                if (
                    typeof response !== 'undefined'
                    && typeof response.data !== 'undefined'
                    && typeof response.data.zipUrl !== 'undefined'){
                    window.open(response.data.zipUrl, '_blank');
                }
                jQuery('#bulkActions').val('-1');
            });
        }
    });

});
function refreshTable() {
    Array.prototype.forEach.call(filters, function (elem) {
        ajaxUrlWithFilters += '&' + elem.id + '=' + elem.value
        ajaxUrlWithFiltersForCount += '&' + elem.id + '=' + elem.value
    })
    table.ajax.url(ajaxUrlWithFilters);
    table.draw()
}

function appendHtml(shipping, subtotal, total){
    if (typeof jQuery('#totals') !== 'undefined'){
        jQuery('#totals').remove();
    }
    jQuery('tfoot').append(
        '<tr id="totals"><th class="selectCheckbox" rowspan="1" colspan="1"></th><th rowspan="1" colspan="1">Zbir</th><th rowspan="1" colspan="1"></th><th rowspan="1" colspan="1">'+shipping+'</th><th rowspan="1" colspan="1">'+subtotal+'</th><th rowspan="1" colspan="1">'+total+'</th><th rowspan="1" colspan="1"></th><th rowspan="1" colspan="1"></th><th rowspan="1" colspan="1"></th><th rowspan="1" colspan="1"></th><th rowspan="1" colspan="1"></th></tr>'
    )
}

jQuery('#orderTable').on('draw.dt', function (){
    appendHtml('','','');
    jQuery.get(ajaxUrlWithFiltersForCount, function (response){
        appendHtml(response.data.allPagesShippingTotal, response.data.allPagesSubtotal, response.data.allPagesTotal);
    })
})