<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>

<script>
    var dtEReports;
    var myFilters;
    filters_has_datepicker = true;

    $(document).ready(function(){
        dataT = dtEReports = $('#dtEReports').dataTable( {
            "sDom": '<"top"i>rt<"bottom"lp><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL; ?>cr_expense_reports/ajax_operations/my_dt",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                {"sClass": "vam", "aTargets": ['dt_title'], "mData": "dt_title", "bSortable": false},
                {"sClass": "w-150 tac vam", "aTargets": ['dt_refund_amount'], "mData": "dt_refund_amount" },
                {"sClass": "w-150 tac vam", "aTargets": ['dt_created'], "mData": "dt_created"},
                {"sClass": "w-150 tac vam", "aTargets": ['dt_updated'], "mData": "dt_updated"},
                {"sClass": "w-100 tac vam", "aTargets": ['dt_status'], "mData": "dt_status" },
                {"sClass": "w-50 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions" , 'bSortable': false},
            ],
            "sorting" : [],
            "sPaginationType": "full_numbers",
            "language": {
                "paginate": {
                    "first": "<i class='ep-icon ep-icon_arrow-left'></i>",
                    "previous": "<i class='ep-icon ep-icon_arrows-left'></i>",
                    "next": "<i class='ep-icon ep-icon_arrows-right'></i>",
                    "last": "<i class='ep-icon ep-icon_arrow-right'></i>"
                }
            },
            "fnServerData": function ( sSource, aoData, fnCallback ) {
                if(!myFilters){
                    myFilters = initDtFilter();
                }

                aoData = aoData.concat(myFilters.getDTFilter());
                $.ajax( {
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if(data.mess_type == 'error' || data.mess_type == 'info'){
                            systemMessages(data.message, data.mess_type);
                        }

                        fnCallback(data, textStatus, jqXHR);

                    }
                });
            },
            "fnDrawCallback": function(oSettings) {
                hideDTbottom(this);
                mobileDataTable($('.main-data-table'));
            }
        });
        dataTableScrollPage(dataT);
    });

	var delete_ereport = function(opener){
		var $this = $(opener);
        var id_report = intval($this.data('expense-report'));
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL;?>cr_expense_reports/ajax_operations/delete',
			dataType: "JSON",
			data: {expense_report: id_report},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type == 'success') {
					dtEReports.fnDraw(false);
				}

			}
		});
	}
</script>

<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/cr/my/expense_reports/filter_view')); ?>

<div class="container-center">

    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl">Expense reports</h1>

        <div class="dashboard-line__actions">
            <a class="btn btn-primary pl-20 pr-20 fancybox.ajax fancyboxValidateModalDT" data-table="dtEReports" title="Add expense report" data-title="Add expense report" href="<?php echo __SITE_URL;?>cr_expense_reports/popup_forms/add">
                <i class="ep-icon ep-icon_plus-circle fs-20"></i>
                <span class="dn-m-min">Add report</span>
            </a>
            <a class="btn btn-dark btn-filter fancybox" href="#dtfilter-hidden" data-mw="740" data-title="Filter panel">
                <i class="ep-icon ep-icon_filter"></i> Filter
            </a>
        </div>
    </div>

    <table class="main-data-table" id="dtEReports">
        <thead>
            <tr>
				<th class="dt_title">Title</th>
				<th class="dt_refund_amount">Amount, (USD)</th>
				<th class="dt_created">Created</th>
				<th class="dt_updated">Updated</th>
				<th class="dt_status">Status</th>
				<th class="dt_actions"></th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>
