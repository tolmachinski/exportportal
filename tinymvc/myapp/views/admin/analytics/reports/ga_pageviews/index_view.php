<script type="text/javascript" src="<?php echo __FILES_URL;?>public/plug/jquery-datatables-1-10-12/dataTable.rowsGroup.js"></script>
<script type="text/javascript">
var analyticsDT;
var analyticsFilter;
$(function(){
	analyticsDT = $('#analyticsDT').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>analytics/ajax_operations/google_report_dt",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "tal", "aTargets": ['dt_target'], "mData": "dt_target", "bSortable": false  },
			{ "sClass": "w-100 tac", "aTargets": ['dt_users'], "mData": "dt_users", "bSortable": false },
			{ "sClass": "w-100 tac vam", "aTargets": ['dt_registered'], "mData": "dt_registered" , "bSortable": false},
			{ "sClass": "w-100 tac", "aTargets": ['dt_new_users'], "mData": "dt_new_users", "bSortable": false },
			{ "sClass": "w-100 tac", "aTargets": ['dt_new_visitors'], "mData": "dt_new_visitors", "bSortable": false },
			{ "sClass": "w-100 tac", "aTargets": ['dt_returning_visitors'], "mData": "dt_returning_visitors", "bSortable": false },
			{ "sClass": "w-100 tac", "aTargets": ['dt_sessions'], "mData": "dt_sessions", "bSortable": false },
			{ "sClass": "w-100 tac", "aTargets": ['dt_bounces'], "mData": "dt_bounces", "bSortable": false },
			{ "sClass": "w-100 tac", "aTargets": ['dt_pageviews'], "mData": "dt_pageviews", "bSortable": false },
			{ "sClass": "w-100 tac", "aTargets": ['dt_avg_time_on_page'], "mData": "dt_avg_time_on_page", "bSortable": false },
			{ "sClass": "w-100 tac", "aTargets": ['dt_entrances'], "mData": "dt_entrances", "bSortable": false },
			{ "sClass": "w-100 tac", "aTargets": ['dt_exits'], "mData": "dt_exits", "bSortable": false },
		],
		'rowsGroup': [2],
		'sorting' : [],
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			if(!analyticsFilter){
				analyticsFilter = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug'	: false,
					'autoApply': true,
					callBack: function(filter){
						analyticsDT.fnDraw();
					},
					onSet: function(callerObj, filterObj){
					},
					onDelete: function(filterObj){
						if(filterObj.name == 'analytic_date'){
							$('input[name="'+filterObj.name+'"]').val('<?php echo date("m/d/Y", strtotime("yesterday"));?>').trigger('change');
						}
					},
					onReset: function(){
						$('input[name="analytic_date"]').datepicker( "option" , {
							maxDate: '<?php echo date("m/d/Y", strtotime("yesterday"));?>',
							minDate: '09/01/2018',
						});
					}
				});
			}

			aoData = aoData.concat(analyticsFilter.getDTFilter());
			$.ajax( {
				"dataType": 'JSON',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if(data.mess_type != 'success'){
						systemMessages(data.message, 'message-' + data.mess_type);
					}

                    $(data.aaData).each(function(index, data) {
                        console.log(data.dt_registered);
                    });

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
		<?php tmvc::instance()->controller->view->display('admin/analytics/reports/ga_pageviews/filter_view')?>
		<div class="titlehdr">
			<span>Analytic report</span>
		</div>
		<div class="wr-filter-list clearfix mt-10"></div>
		<table class="data table-striped table-bordered w-100pr" id="analyticsDT" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					 <th class="dt_target">Target</th>
					 <th class="dt_users">Users</th>
					 <th class="dt_registered">Registered</th>
					 <th class="dt_new_users">New Users</th>
					 <th class="dt_new_visitors">New Visitors</th>
					 <th class="dt_returning_visitors">Returning Visitors</th>
					 <th class="dt_sessions">Sessions</th>
					 <th class="dt_bounces">Bounces</th>
					 <th class="dt_pageviews">Page views</th>
					 <th class="dt_avg_time_on_page">AVG time, sec.</th>
					 <th class="dt_entrances">Entrances</th>
					 <th class="dt_exits">Exits</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>
