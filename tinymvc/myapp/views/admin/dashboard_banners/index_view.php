<?php views('admin/dashboard_banners/filter_view'); ?>
<?php views()->display('new/file_upload_scripts'); ?>

<div class="row">
    <div class="ship col-xs-12">
        <div class="titlehdr">
            <span>Banners</span>
            <a
            class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green"
            href="<?php echo __SITE_URL; ?>dashboard_banner/popup_forms/add_banner"
            data-title="Add banners"
            data-table="dashboardBannersTable"></a>
        </div>

        <div class="clearfix mt-10 wr-filter-list"></div>

        <table cellspacing="0" cellpadding="0" id="dashboardBannersTable" class="data table-bordered table-striped w-100pr">
            <thead>
                <tr>
                    <th class="dt_banner_id">ID</th>
                    <th class="dt_banner_subtitle">Subtitle</th>
                    <th class="dt_banner_title">Title</th>
                    <th class="dt_banner_img">Image</th>
                    <th class="dt_banner_url">Url</th>
                    <th class="dt_banner_text_button">Text Button</th>
                    <th class="dt_banner_is_visible">Visible</th>
                    <th class="dt_banner_created_at">Created At</th>
                    <th class="dt_banner_updated_at">Updated At</th>
                    <th class="dt_actions tac vam">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>


<script type="text/javascript">
    var dashboardBannersTable;
    var dashboardBannersFilters;

    $(function() {
        dashboardBannersTable = $('#dashboardBannersTable').dataTable( {
            "sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "searching": false,
            "sAjaxSource": "<?php echo __SITE_URL . 'dashboard_banner/ajax_admin_all_dashboard_banners_dt'; ?>",
            "sServerMethod": "POST",
            "sorting": [[ 0, "asc" ]],
            "aoColumnDefs": [
                {"sClass": "w-35 vam tac", "aTargets": ['dt_banner_id'], "mData": "id", 'bSortable': true},
                {"sClass": "w-200 tac vam", "aTargets": ['dt_banner_subtitle'], "mData": "subtitle", 'bSortable': false},
                {"sClass": "w-300 tac vam", "aTargets": ['dt_banner_title'], "mData": "title", 'bSortable': false},
                {"sClass": "w-80 tac vam", "aTargets": ['dt_banner_img'], "mData": "image", 'bSortable': false},
                {"sClass": "w-300 tac vam", "aTargets": ['dt_banner_url'], "mData": "url", 'bSortable': false},
                {"sClass": "w-150 tac vam", "aTargets": ['dt_banner_text_button'], "mData": "buttonText", 'bSortable': false},
                {"sClass": "w-50 tac vam", "aTargets": ['dt_banner_is_visible'], "mData": "isVisible", 'bSortable': true},
                {"sClass": "w-110 tac vam", "aTargets": ['dt_banner_created_at'], "mData": "createdAt", 'bSortable': true},
                {"sClass": "w-110 tac vam", "aTargets": ['dt_banner_updated_at'], "mData": "updatedAt", 'bSortable': true},
                {"sClass": "w-30 tac vam", "aTargets": ['dt_actions'], "mData": "dt_banner_actions", "bSortable": false},

            ],

            "sPaginationType": "full_numbers",
            "aLengthMenu": [[25, 50, 100], [25, 50, 100]],
            "fnServerData": async function ( sSource, aoData, fnCallback ) {
                let renderData = { aaData: [] };

                if (!dashboardBannersFilters) {
                    dashboardBannersFilters = $('.dt_filter').dtFilters('.dt_filter', {
                        'container': '.wr-filter-list',
                        callBack: function() {
                            dashboardBannersTable.fnDraw();
                        },
                        onDelete: function(filter){
                            switch (filter.name) {
                                case 'user_groups':
                                    selectGroups.multipleSelect('uncheckAll');
                                break;
                            }
                        },
                        onReset: function(){
							selectGroups.multipleSelect('uncheckAll');
						}

                    });
                }

                aoData = aoData.concat(dashboardBannersFilters.getDTFilter());

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
                dashboardBannersTable.fnDraw(false);
            }
        };

        postRequest(__site_url + 'dashboard_banner/ajax_operations/visible_status', { id: $(element).data('id')})
        .then(onRequestSuccess)
        .catch(onRequestError);
    }


    var deleteBanner = function (element) {
        var onRequestSuccess = function (data) {
            systemMessages(data.message, data.mess_type);
            if ('success' === data.mess_type) {
                dashboardBannersTable.fnDraw();
            }
        };

        postRequest(__site_url + 'dashboard_banner/ajax_operations/delete', { id: $(element).data('id')})
        .then(onRequestSuccess)
        .catch(onRequestError);
    }
</script>
