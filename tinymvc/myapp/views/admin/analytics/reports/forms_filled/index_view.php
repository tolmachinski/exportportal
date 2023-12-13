<script type="text/javascript">
var analyticsDT;
var analyticsFilter;
$(function(){
	analyticsDT = $('#analyticsDT').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>analytics/ajax_operations/forms_filled_report_dt",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "tal", "aTargets": ['dt_target'], "mData": "dt_target", "bSortable": false  },
			{ "sClass": "w-100 tac", "aTargets": ['dt_filled_users'], "mData": "dt_filled_users", "bSortable": false },
			{ "sClass": "w-100 tac", "aTargets": ['dt_filled_sessions'], "mData": "dt_filled_sessions", "bSortable": false },
			{ "sClass": "w-100 tac", "aTargets": ['dt_submits_users'], "mData": "dt_submits_users", "bSortable": false },
			{ "sClass": "w-120 tac", "aTargets": ['dt_submits_sessions'], "mData": "dt_submits_sessions", "bSortable": false },
			{ "sClass": "w-100 tac", "aTargets": ['dt_submits'], "mData": "dt_submits", "bSortable": false },
			{ "sClass": "w-120 tac", "aTargets": ['dt_success_submits'], "mData": "dt_success_submits", "bSortable": false },
		],
		"sorting": [],
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
		<?php tmvc::instance()->controller->view->display('admin/analytics/reports/forms_filled/filter_view')?>
		<div class="titlehdr">
			<span>Analytic Forms report</span>
		</div>
		<div class="wr-filter-list clearfix mt-10"></div>
		<table class="data table-striped table-bordered w-100pr" id="analyticsDT" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					 <th class="dt_target">Target</th>
					 <th class="dt_filled_users">Filled (users)</th>
					 <th class="dt_filled_sessions">Filled (sessions)</th>
					 <th class="dt_submits_users">Submited (users)</th>
					 <th class="dt_submits_sessions">Submited (sessions)</th>
					 <th class="dt_submits">Submits</th>
					 <th class="dt_success_submits">Successfull submits</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>
