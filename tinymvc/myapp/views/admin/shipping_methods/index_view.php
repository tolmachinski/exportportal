<?php views('admin/shipping_methods/filter_view'); ?>
<?php views()->display('new/file_upload_scripts'); ?>

<div class="row">
    <div class="ship col-xs-12">
        <div class="titlehdr">
            <span>Shipping Methods</span>
            <a
            class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green"
            href="<?php echo __SITE_URL; ?>shipping_methods/popup_forms/add"
            data-title="Add Shipping Method"
            data-table="shippingTypesTable"></a>
        </div>

        <div class="clearfix mt-10 wr-filter-list"></div>

        <table cellspacing="0" cellpadding="0" id="shippingTypesTable" class="data table-bordered table-striped w-100pr">
            <thead>
                <tr>
                    <th class="dt_shipping_type_id">ID</th>
                    <th class="dt_shipping_type_name">Name</th>
                    <th class="dt_shipping_type_short_desc">Short Description</th>
                    <th class="dt_shipping_type_full_desc">Full Description</th>
                    <th class="dt_shipping_type_img">Image</th>
                    <th class="dt_shipping_type_is_visible">Visible</th>
                    <th class="dt_shipping_type_created_at">Created At</th>
                    <th class="dt_shipping_type_updated_at">Updated At</th>
                    <th class="dt_actions tac vam">Actions</th>
                </tr>

            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    var shippingTypesTable;
    var shippingTypesFilters;

    $(function() {
        shippingTypesTable = $('#shippingTypesTable').dataTable( {
            "sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "searching": false,
            "sAjaxSource": "<?php echo __SITE_URL . 'shipping_methods/ajax_admin_all_shipping_methods_dt'; ?>",
            "sServerMethod": "POST",
            "sorting": [[ 0, "asc" ]],
            "aoColumnDefs": [
                {"sClass": "w-35 vam tac", "aTargets": ['dt_shipping_type_id'], "mData": "id", 'bSortable': true},
                {"sClass": "w-150 tac vam", "aTargets": ['dt_shipping_type_name'], "mData": "name", 'bSortable': false},
                {"sClass": "w-350 tac vam", "aTargets": ['dt_shipping_type_short_desc'], "mData": "small_description", 'bSortable': false},
                {"sClass": "w-350 tac vam", "aTargets": ['dt_shipping_type_full_desc'], "mData": "full_description", 'bSortable': false},
                {"sClass": "w-80 tac vam", "aTargets": ['dt_shipping_type_img'], "mData": "image", 'bSortable': false},
                {"sClass": "w-80 tac vam", "aTargets": ['dt_shipping_type_is_visible'], "mData": "is_visible", 'bSortable': false},
                {"sClass": "w-100 tac vam", "aTargets": ['dt_shipping_type_created_at'], "mData": "createdAt", 'bSortable': true},
                {"sClass": "w-100 tac vam", "aTargets": ['dt_shipping_type_updated_at'], "mData": "updatedAt", 'bSortable': true},
                {"sClass": "w-30 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false},

            ],

            "sPaginationType": "full_numbers",
            "aLengthMenu": [[25, 50, 100], [25, 50, 100]],
            "fnServerData": async function ( sSource, aoData, fnCallback ) {
                let renderData = { aaData: [] };

                if (!shippingTypesFilters) {
                    shippingTypesFilters = $('.dt_filter').dtFilters('.dt_filter', {
                        'container': '.wr-filter-list',
                        callBack: function() {
                            shippingTypesTable.fnDraw();
                        }
                    });
                }

                aoData = aoData.concat(shippingTypesFilters.getDTFilter());

                try {
                    const data = await postRequest(sSource, aoData);
                    const { mess_type: messageType, message } = data;
                    renderData = data;

                    if (messageType !== "success") {
                        systemMessages(message, `message-${messageType}`);
                    }
                } catch(e) {
                    onRequestError(e);
                } finally {
                    fnCallback(renderData);
                }
            },
            "fnDrawCallback": function(oSettings) {
            }
        });
    });

    var visibleStatus = function (element) {
        var onRequestSuccess = function (data) {
            systemMessages(data.message, data.mess_type);
            if ('success' === data.mess_type) {
                shippingTypesTable.fnDraw(false);
            }
        };

        postRequest(__site_url + 'shipping_methods/ajax_operations/visible_status', { id: $(element).data('id')})
        .then(onRequestSuccess)
        .catch(onRequestError);
    }

    var deleteShippingMethod = function (element) {
        var onRequestSuccess = function (data) {
            systemMessages(data.message, data.mess_type);
            if ('success' === data.mess_type) {
                shippingTypesTable.fnDraw();
            }
        };

        postRequest(__site_url + 'shipping_methods/ajax_operations/delete', { id: $(element).data('id')})
        .then(onRequestSuccess)
        .catch(onRequestError);
    }
</script>
