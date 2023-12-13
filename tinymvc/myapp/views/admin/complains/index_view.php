<script type="text/javascript">
	var complainsFilters, dtReports;
    $(document).ready(function () {
		remove_complain = function (obj) {
            var $this = $(obj);
            var id = $this.data('complain');

            $.ajax({
                type: 'POST',
                url: '<?php echo __SITE_URL ?>complains/ajax_complains_operations/remove_complain',
                data: {id_complain: id},
                beforeSend: function () {
                    showLoader(dtReports);
                },
                dataType: 'json',
                success: function (data) {
                    hideLoader(dtReports);
                    systemMessages(data.message, 'message-' + data.mess_type);
                    if (data.mess_type == 'success') {
                        dtReports.fnDraw();
                    }
                }
            });
        }

        dtReports = $('#dtReports').dataTable({
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL ?>complains/ajax_complains_dt",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                {"sClass": "w-40 tac", "aTargets": ['dt_id'], "mData": "dt_id"},
                {"sClass": "w-200 tac", "aTargets": ['dt_type'], "mData": "dt_type"},
                {"sClass": "w-100 tac", "aTargets": ['dt_id_item'], "mData": "dt_id_item"},
                {"sClass": "w-250 tac", "aTargets": ['dt_from'], "mData": "dt_from"},
                {"sClass": "w-250 tac", "aTargets": ['dt_to'], "mData": "dt_to"},
                {"sClass": "tac", "aTargets": ['dt_theme'], "mData": "dt_theme", "bSortable": false},
                {"sClass": "tac w-200", "aTargets": ['dt_status'], "mData": "dt_status"},
                {"sClass": "tac w-200", "aTargets": ['dt_date_time'], "mData": "dt_date_time"},
                {"sClass": "tac w-80", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
            ],
            "sorting": [[0, "desc"]],
            "fnServerData": function (sSource, aoData, fnCallback) {

                if (!complainsFilters) {
                    complainsFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug': false,
                        callBack: function () {
                            dtReports.fnDraw();
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
                        if (data.mess_type == 'error' || data.mess_type == 'info')
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

    $('body').on('click', 'a[rel=complains_detail]', function() {
        var $aTd = $(this);
        var nTr = $aTd.parents('tr')[0];
        if (dtReports.fnIsOpen(nTr))
            dtReports.fnClose(nTr);
        else
            dtReports.fnOpen(nTr, fnFormatDetails(nTr), 'details');

        $aTd.toggleClass('ep-icon_plus ep-icon_minus');
    });
});


function fnFormatDetails(nTr){
	var aData = dtReports.fnGetData(nTr);
	var sOut = '<div class="dt-details"><table class="dt-details__table">';
		sOut += '<tr><td class="w-100">Text:</td><td>' +  aData['dt_text'] + '</td></tr>';
		sOut += '</table> </div>';
	return sOut;
}
</script>

<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30"><span>Reports themes</span></div>

        <?php tmvc::instance()->controller->view->display('admin/complains/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10 "></div>

        <table class="data table-striped table-bordered w-100pr" id="dtReports" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_type">Type</th>
                    <th class="dt_id_item">ID resource</th>
                    <th class="dt_from">From user</th>
                    <th class="dt_to">On user</th>
                    <th class="dt_theme">Theme</th>
                    <th class="dt_status">Status</th>
                    <th class="dt_date_time">Date</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
