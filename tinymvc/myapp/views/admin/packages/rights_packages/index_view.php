<script type="text/javascript">
    var dtRightsPackages, rightsFilters;

    function renew_right_packages_table() {
        dtRightsPackages.fnDraw();
    }

    $(document).ready(function () {
        dtRightsPackages = $('#dtRightsPackages').dataTable({
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL ?>rights_packages/ajax_rights_packages_dt",
            "sServerMethod": "POST",
            "aoColumnDefs": [
				{"sClass": "", "aTargets": ['dt_group'], "mData": "dt_group"},
				{"sClass": "w-150 tac", "aTargets": ['dt_right'], "mData": "dt_right"},
				{"sClass": "w-150 tac", "aTargets": ['dt_period'], "mData": "dt_period"},
				{"sClass": "w-150 tac", "aTargets": ['dt_price'], "mData": "dt_price"},
				{"sClass": "tac w-80", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
			],
            "sorting": [[0, "desc"]],
            "fnServerData": function (sSource, aoData, fnCallback) {
                if (!rightsFilters) {
                    rightsFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug': false,
                        callBack: function () {
                            renew_right_packages_table();
                        },
                        onSet: function (callerObj, filterObj) {

                        },
                        onDelete: function (filter) {

                        }
                    });
                }

                aoData = aoData.concat(rightsFilters.getDTFilter());
                $.ajax({
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if (data.mess_type == 'error')
                            systemMessages(data.message, 'message-' + data.mess_type);
                        if (data.mess_type == 'info')
                            systemMessages(data.message, 'message-' + data.mess_type);

                        fnCallback(data, textStatus, jqXHR);

                    }
                });
            },
            "sPaginationType": "full_numbers",
            "fnDrawCallback": function (oSettings) {

            }
        });

        remove_right_package = function (obj) {
            var $this = $(obj);
            $.ajax({
                type: 'POST',
                url: '/rights_packages/ajax_rights_packages_operations/delete_right_package',
                data: {
                    id: $this.data('id')
                },
                beforeSend: function () {
                    showLoader('#dtRightsPackages');
                },
                dataType: 'json',
                success: function (data) {
                    hideLoader('#dtRightsPackages');
                    systemMessages(data.message, 'message-' + data.mess_type);
                    if (data.mess_type == 'success') {
                        renew_right_packages_table();
                    }
                }
            });
        }
    });

</script>

<div class="row">
	<div class="col-xs-12">
        <div class="titlehdr h-30">
			<span>Rights packages</span>
			<a class="ep-icon ep-icon_plus-circle fancyboxValidateModalDT fancybox.ajax fs-18 txt-green pull-right" data-table="dtRightsPackages" title="Add right package" href="<?php echo __SITE_URL;?>rights_packages/popup_forms/insert_right_package" data-title="Add right package"></a>
		</div>
		<?php tmvc::instance()->controller->view->display('admin/packages/rights_packages/filter_panel_view'); ?>
		<div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped w-100pr datatable" id="dtRightsPackages" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th class="dt_group tal">Group for</th>
                    <th class="dt_right">Right</th>
                    <th class="dt_period">Period</th>
                    <th class="dt_price">Price</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
