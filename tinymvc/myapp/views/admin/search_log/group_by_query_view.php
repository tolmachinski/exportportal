<script type="text/javascript">
var dtSearchLog, groupsFilters;

$(document).ready(function(){

	dtSearchLog = $('#dtSearchLog').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>search_log/ajax_operation/administration_search_log_group_by_query",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "", "aTargets": ['dt_query'], "mData": "dt_query", "bSortable": false},
			{ "sClass": "w-150 tac", "aTargets": ['dt_page'], "mData": "dt_page", "bSortable": false},
			{ "sClass": "w-150 tac", "aTargets": ['dt_count'], "mData": "dt_count", "bSortable": true},
		],
		"fnServerParams": function ( aoData ) {},
		"fnServerData": function ( sSource, aoData, fnCallback ) {
            if (!groupsFilters) {
                groupsFilters = $('.dt_filter').dtFilters('.dt_filter',{
                    'container': '.wr-filter-list',
                    'debug': false,
                    callBack: function () { dtSearchLog.fnDraw(); },
                    onSet: function (callerObj, filterObj) {
                        if(filterObj.name == 'group'){
                            $('.users-groups-counters a[data-value="' + filterObj.value + '"]').addClass('active').siblings().removeClass('active');
                            $('.menu-level3 a[data-value="' + filterObj.value + '"]').parent('li').addClass('active').siblings().removeClass('active');
                        }
                    },
                    onDelete: function (filterObj) {
                        if(filterObj.name == 'group'){
                            $('.users-groups-counters a[data-value="' + filterObj.default + '"]').addClass('active').siblings().removeClass('active');
                            $('.menu-level3 a[data-value="' + filterObj.default + '"]').parent('li').addClass('active').siblings().removeClass('active');
                        }
                    }
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
        "sorting" : [[2,'desc']],
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {}
	});
});

</script>

<div class="row">
	<div class="col-xs-12">
    	<div class="titlehdr h-30">
    		<span>Search log group by query</span>
    	</div>
        <div class="wr-filter-list clearfix mt-10"></div>
        <?php tmvc::instance()->controller->view->display('admin/search_log/filter_panel_group_by_query_view'); ?>

		<table id="dtSearchLog" class="data table-striped table-bordered w-100pr" cellspacing="0" cellpadding="0" >
			<thead>
				<tr>
					<th class="dt_query">Query</th>
					<th class="dt_page">Pgae</th>
					<th class="dt_count">Count</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
