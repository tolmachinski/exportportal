<script>
	var dtEReportsList;
    var myFilters;

    $(document).ready(function() {
		dtEReportsList = $('#dtEReportsList').dataTable({
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL;?>cr_expense_reports/ajax_operations/admin_dt",
            "sServerMethod": "POST",
            "sorting": [],
            "aoColumnDefs": [
                {"sClass": "w-50 tac vam", "aTargets": ['dt_id'], "mData": "dt_id" , 'bSortable': false},
                {"sClass": "", "aTargets": ['dt_title'], "mData": "dt_title" , 'bSortable': false},
                {"sClass": "w-70 tac vam", "aTargets": ['dt_useravatar'], "mData": "dt_useravatar" , 'bSortable': false},
                {"sClass": "w-100 tac vam", "aTargets": ['dt_refund_amount'], "mData": "dt_refund_amount" },
                {"sClass": "w-250 vam", "aTargets": ['dt_userinfo'], "mData": "dt_userinfo" , 'bSortable': false},
                {"sClass": "w-100 tac vam", "aTargets": ['dt_status'], "mData": "dt_status" },
                {"sClass": "w-100 tac vam", "aTargets": ['dt_created'], "mData": "dt_created" },
                {"sClass": "w-100 tac vam", "aTargets": ['dt_updated'], "mData": "dt_updated" },
                {"sClass": "w-100 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions" , 'bSortable': false}
            ],
            "fnServerData": function ( sSource, aoData, fnCallback ) {
                if(!myFilters){
					myFilters = $('.dt_filter').dtFilters('.dt_filter',{
						'container': '.wr-filter-list',
						callBack: function(){
							dtEReportsList.fnDraw();
						},
						onSet: function(callerObj, filterObj){},
					});
			    }

			    aoData = aoData.concat(myFilters.getDTFilter());

				$.ajax( {
					"dataType": 'json',
					"type": "POST",
					"url": sSource,
					"data": aoData,
					"success": function (data, textStatus, jqXHR) {
						if(data.mess_type == 'error')
							systemMessages(data.message, 'message-' + data.mess_type);

						fnCallback(data, textStatus, jqXHR);

					}
				});
			},

            "sPaginationType": "full_numbers",
            "fnDrawCallback": function(oSettings) {}
		});
    });

    function fnFormatDetails(nTr){
        var aData = dtEReportsList.fnGetData(nTr);
        var sOut = '<div class="dt-details">\
                        <table class="dt-details__table w-100pr">\
                        '+aData['dt_details']+'\
                        </table>\
                    </div>';
        return sOut;
    }

    var toggle_details = function(btn){
        var $this = $(btn);
        var nTr = $this.parents('tr')[0];

        if (dtEReportsList.fnIsOpen(nTr)) {
            dtEReportsList.fnClose(nTr);
        } else {
            dtEReportsList.fnOpen(nTr, fnFormatDetails(nTr), 'details');
        }

        $this.toggleClass('ep-icon_plus ep-icon_minus');
    }

    function change_status(opener){
        var $this = $(opener);
        var id_ereport = $this.data("id_ereport");
        var er_status = $this.data("status");

        $.ajax({
            type: "POST",
            url: "cr_expense_reports/ajax_operations/change_status",
            data: { id_ereport: id_ereport, status:er_status},
            dataType: 'JSON',
            success: function(resp){
                if (resp.mess_type == 'success'){
                    dtEReportsList.fnDraw(false);
                }

                systemMessages(resp.message, resp.mess_type);
            }
        });
    }
</script>

<div class="row">
    <div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>Expense reports</span>
            <!--<a class="ep-icon ep-icon_plus-circle fancyboxValidateModalDT fancybox.ajax pull-right" title="Add new expense report" data-table="dtEReportsList" title="Add new expense report" href="cr_expense_reports/popup_forms/add_expense_report_admin" data-title="Add new expense report"></a>-->
			<div class="pull-right btns-actions-all display-n">
				<a class="ep-icon ep-icon_remove txt-red pull-right confirm-dialog mr-5" data-message="Are you sure want delete selected expense reports?" data-callback="delete_ereports" title="Delete expense reports"></a>
			</div>
		</div>
		<?php tmvc::instance()->controller->view->display('admin/cr/expense_reports/expense_reports_filter_bar'); ?>

		<div class="wr-filter-list mt-10 clearfix"></div>

        <table id="dtEReportsList" class="data table-bordered table-striped w-100pr" >
            <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_useravatar">Logo</th>
                    <th class="dt_userinfo">User</th>
                    <th class="dt_title">Title</th>
                    <th class="dt_refund_amount">Refund amount</th>
                    <th class="dt_created">Created</th>
                    <th class="dt_updated">Updated</th>
                    <th class="dt_status">Status</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
