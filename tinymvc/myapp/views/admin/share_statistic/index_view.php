<script type="text/javascript">
var dtShareStatistic, groupsFilters;

$(document).ready(function(){

	dtShareStatistic = $('#js-dt-share-statistic').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>share_statistic/ajaxDtAdministration",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-50 tac", "aTargets": ['dt_id'], "mData": "dt_id"},
			{ "sClass": "w-80", "aTargets": ['dt_type'], "mData": "dt_type"},
			{ "sClass": "w-100", "aTargets": ['dt_type_sharing'], "mData": "dt_type_sharing"},
			{ "sClass": "", "aTargets": ['dt_item'], "mData": "dt_item", "bSortable": false},
			{ "sClass": "", "aTargets": ['dt_user'], "mData": "dt_user", "bSortable": false},
			{ "sClass": "tac w-150", "aTargets": ['dt_date'], "mData": "dt_date"},
		],
        "sorting": [[3, "desc"]],
		"fnServerParams": function ( aoData ) {},
		"fnServerData": function ( sSource, aoData, fnCallback ) {
            if (!groupsFilters) {
                groupsFilters = $('.dt_filter').dtFilters('.dt_filter',{
                    'container': '.wr-filter-list',
                    'debug': false,
                    callBack: function () { dtShareStatistic.fnDraw(); },
                    onSet: function (callerObj, filterObj) {},
                    onDelete: function (filter) {}
                });
            }

            aoData = aoData.concat(groupsFilters.getDTFilter());
			$.ajax( {
				"dataType": 'JSON',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if(data.mess_type == 'error' || data.mess_type == 'info') {
						systemMessages(data.message, 'message-' + data.mess_type);
                        }

					fnCallback(data, textStatus, jqXHR);
				}
			});
		},
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {}
	});
});
</script>

<div class="row">
	<div class="col-xs-12">
    	<div class="titlehdr h-30">
    		<span>Share statistic</span>
    	</div>

		<?php tmvc::instance()->controller->view->display('admin/share_statistic/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

		<table id="js-dt-share-statistic" class="data table-striped table-bordered w-100pr" cellspacing="0" cellpadding="0" >
			<thead>
				<tr>
					<th class="dt_id w-50">#</th>
					<th class="dt_type_sharing">Type sharing</th>
					<th class="dt_user">User</th>
					<th class="dt_date">Date</th>
					<th class="dt_type">Type</th>
					<th class="dt_item">Item</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
