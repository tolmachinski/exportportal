<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/select2-4-0-3/js/select2.min.js"></script>

<script type="text/javascript">
	var complainsFilters, dtReportsThemes;
    $(document).ready(function () {
		remove_complain_theme = function (obj) {
            var $this = $(obj);
            var id = $this.data('theme');

            $.ajax({
                type: 'POST',
                url: '<?php echo __SITE_URL ?>complains/ajax_complains_operations/delete_theme',
                data: {id_theme: id},
                beforeSend: function () {
                    showLoader(dtReportsThemes);
                },
                dataType: 'json',
                success: function (data) {
                    hideLoader(dtReportsThemes);
                    systemMessages(data.message, 'message-' + data.mess_type);
                    if (data.mess_type == 'success') {
                        dtReportsThemes.fnDraw();
                    }
                }
            });
        }

        dtReportsThemes = $('#dt-complains-themes').dataTable({
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL ?>complains/ajax_complains_themes_dt",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                {"sClass": "w-40 tac", "aTargets": ['dt_id'], "mData": "dt_id"},
                {"sClass": "tac w-400", "aTargets": ['dt_theme'], "mData": "dt_theme"},
                {"sClass": "tac", "aTargets": ['dt_type'], "mData": "dt_type", "bSortable": false},
                {"sClass": "tac w-80", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
            ],
            "sorting": [[0, "desc"]],
            "fnServerData": function (sSource, aoData, fnCallback) {

                if (!complainsFilters) {
                    complainsFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug': false,
                        callBack: function () {
                            dtReportsThemes.fnDraw();
                        },
                        onSet: function (callerObj, filterObj) {

                        },
                        onDelete: function (filter) {

                        }
                    });
                }

                aoData = aoData.concat(complainsFilters.getDTFilter());
                $.ajax({
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if (data.mess_type == 'info' || data.mess_type == 'error')
                            systemMessages(data.message, 'message-' + data.mess_type);

                        fnCallback(data, textStatus, jqXHR);

                    }
                });
            },
            "sPaginationType": "full_numbers",
            "fnDrawCallback": function (oSettings) {

                var keywordsSearch = $('.filter-admin-panel').find('input[name=keywords]').val();
                if (keywordsSearch !== '')
                    $("#dt-country-blogs tbody *").highlight(keywordsSearch, "highlight");
        	}
		});
});
</script>

<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
			<span>Reports themes</span>
			<a class="fancyboxValidateModal fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="complains/popup_forms/add_complain_theme" data-title="Add complain theme"></a>
		</div>

        <?php tmvc::instance()->controller->view->display('admin/complains/filter_panel_themes_view'); ?>
        <div class="wr-filter-list clearfix mt-10 "></div>

        <table class="data table-striped table-bordered w-100pr" id="dt-complains-themes" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id">#</th>
					<th class="dt_theme">Theme</th>
					<th class="dt_type">Type</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
