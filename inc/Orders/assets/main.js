let baseAjaxUrl = gfData.ajaxUrl+'?action=gfOrdersTable&ajaxAction=getOrders';
let ajaxUrlWithFilters = baseAjaxUrl;
var table = jQuery('#orderTable').DataTable({
    'processing': true,
    'serverSide': true,
    'searching': true,
    'ajax': ajaxUrlWithFilters,
    'dataSrc': function (data) {
        return data.data;
    },
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
        jQuery(api.column(1).footer()).html(
            'Zbir stranice'
        );
        jQuery(api.column(3).footer()).html(
            api.ajax.json().pageShippingTotal
        );
        jQuery(api.column(4).footer()).html(
            api.ajax.json().pageOrderSubtotals
        );
        jQuery(api.column(5).footer()).html(
            api.ajax.json().pageOrdersTotal
        );
    }
});
var filters = document.getElementsByClassName('filterSelect')
Array.prototype.forEach.call(filters, function (elem) {
    elem.addEventListener('change', function () {
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
    ajaxUrlWithFilters = baseAjaxUrl;
    Array.prototype.forEach.call(filters, function (elem) {
        ajaxUrlWithFilters += '&' + elem.id + '=' + elem.value
    })
    table.ajax.url(ajaxUrlWithFilters);
    table.draw()
}