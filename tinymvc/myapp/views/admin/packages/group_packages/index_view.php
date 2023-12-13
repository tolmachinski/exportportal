<script type="text/javascript" src="<?php echo __FILES_URL;?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="text/javascript">
    var dtGroupPackages, groupsFilters;

    function renew_group_packages_table() {
        dtGroupPackages.fnDraw();
    }

    $(document).ready(function () {
		dtGroupPackages = $('#dtGroupPackages').dataTable({
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL ?>group_packages/ajax_group_packages_dt",
            "sServerMethod": "POST",
            "displayLength": 100,
            "lengthMenu": [[25, 50, 75, 100, -1], [25, 50, 75, 100, "All"]],
            "aoColumnDefs": [
				{"sClass": "w-70 tac", "aTargets": ['dt_id'], "mData": "dt_id"},
				{"sClass": "tac", "aTargets": ['dt_from'], "mData": "dt_from"},
				{"sClass": "tac", "aTargets": ['dt_to'], "mData": "dt_to"},
				{"sClass": "tac", "aTargets": ['dt_downgrade_to'], "mData": "dt_downgrade_to"},
				{"sClass": "w-250 tac", "aTargets": ['dt_period'], "mData": "dt_period"},
				{"sClass": "w-250 tac", "aTargets": ['dt_price'], "mData": "dt_price"},
                { "sClass": "w-150 tac", "aTargets": ['dt_tlangs_list'], "mData": "dt_tlangs_list", "bSortable": false  },
				{"sClass": "tac w-150", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
			],
            "sorting": [[0, "desc"]],
            "fnServerData": function (sSource, aoData, fnCallback) {
                if (!groupsFilters) {
                    groupsFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug': false,
                        callBack: function () {
                            renew_group_packages_table();
                        },
                        onSet: function (callerObj, filterObj) {

                        },
                        onDelete: function (filter) {

                        }
                    });
                }

                aoData = aoData.concat(groupsFilters.getDTFilter());
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
    });

    var change_status_group_package = function (obj) {
        var $this = $(obj);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . 'group_packages/ajax_group_packages_operations/change_status';?>',
            data: {id: $this.data('id'), param: $this.data('package-param')},
            beforeSend: function () {
                showLoader('#dtGroupPackages');
            },
            dataType: 'json',
            success: function (data) {
                hideLoader('#dtGroupPackages');
                systemMessages(data.message, 'message-' + data.mess_type);
                if (data.mess_type == 'success') {
                    renew_group_packages_table();
                }
            }
        });
    }

    var change_default_status = function (obj) {
        var $this = $(obj);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL;?>group_packages/ajax_group_packages_operations/change_default',
            data: {id: $this.data('id')},
            beforeSend: function () {
                showLoader('#dtGroupPackages');
            },
            dataType: 'json',
            success: function (data) {
                hideLoader('#dtGroupPackages');
                systemMessages(data.message, 'message-' + data.mess_type);
                if (data.mess_type == 'success') {
                    renew_group_packages_table();
                }
            }
        });
    }

    var remove_group_package_i18n = function(obj){
        var $this = $(obj);
        var idpack = $this.data('idpack');
        var lang_pack = $this.data('lang');

        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>group_packages/ajax_group_packages_operations/delete_group_package_i18n',
            data: {idpack: idpack, lang:lang_pack},
            dataType: 'json',
            type: 'POST',
            beforeSend: function () {
                showLoader('#dtGroupPackages');
            },
            success: function (data) {
                hideLoader('#dtGroupPackages');
                systemMessages(data.message, 'message-' + data.mess_type);
                if (data.mess_type == 'success') {
                    renew_group_packages_table();
                }
            }
        });
    }

    var remove_group_package = function (obj) {
        var $this = $(obj);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL;?>group_packages/ajax_group_packages_operations/delete_group_package',
            data: {
                id: $this.data('id')
            },
            beforeSend: function () {
                showLoader('#dtGroupPackages');
            },
            dataType: 'json',
            success: function (data) {
                hideLoader('#dtGroupPackages');
                systemMessages(data.message, 'message-' + data.mess_type);
                if (data.mess_type == 'success') {
                    renew_group_packages_table();
                }
            }
        });
    }
</script>
<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
			<span>Account upgrade packages</span>
            <?php if(have_right('manage_grouprights')) { ?>
			    <a class="ep-icon ep-icon_plus-circle fancyboxValidateModalDT fancybox.ajax fs-18 txt-green pull-right" data-table="dtGroupPackages" title="Add Account upgrade package" href="<?php echo __SITE_URL;?>group_packages/popup_forms/insert_group_package" data-title="Add package info"></a>
            <?php } ?>
		</div>
		<?php tmvc::instance()->controller->view->display('admin/packages/group_packages/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped w-100pr datatable" id="dtGroupPackages" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th class="dt_id">ID</th>
                    <th class="dt_from">From group</th>
                    <th class="dt_to">On group</th>
                    <th class="dt_downgrade_to">Downgrade to</th>
                    <th class="dt_period">Period</th>
                    <th class="dt_price">Price</th>
					<th class="dt_tlangs_list">Translated to</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
