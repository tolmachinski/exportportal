<script type="text/javascript">
	var dtFilter;
	var dtTable;

	$(document).ready(function(){
		dtTable = $('#dtTable').dataTable( {
			"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
			"bProcessing": true,
			"bServerSide": true,
			"bSortCellsTop": true,
			"sAjaxSource": "<?php echo __SITE_URL?>cr_users/ajax_operations/list_requests_dt",
			"sServerMethod": "POST",
			"iDisplayLength": 10,
			"aLengthMenu": [
				[10, 25, 50, 100, 0],
				[10, 25, 50, 100, 'All']
			],
			"aoColumnDefs": [
				{ "sClass": "vam w-30 tac", "aTargets": ['dt_request'], "mData": "dt_request", "bSortable": false},
				{ "sClass": "vam", "aTargets": ["dt_fullname"], "mData": "dt_fullname" },
				{ "sClass": "vam w-200", "aTargets": ["dt_email"], "mData": "dt_email" },
				{ "sClass": "w-70 tac vam", "aTargets": ["dt_country"], "mData": "dt_country" , "bSortable": false},
				{ "sClass": "w-80 tac vam", "aTargets": ["dt_registered"], "mData": "dt_registered" },
				{ "sClass": "w-90 tac vam", "aTargets": ["dt_status"], "mData": "dt_status", "bSortable": false },
				{ "sClass": "w-50 tar vam", "aTargets": ["dt_actions"], "mData": "dt_actions", "bSortable": false },

			],
			"sorting" : [[0,'desc']],
			"fnServerData": function ( sSource, aoData, fnCallback ) {
				if(!dtFilter){
					dtFilter = $('.dt_filter').dtFilters('.dt_filter',{
						'container': '.wr-filter-list',
						'debug'	: false,
						'autoApply': true,
						callBack: function(filter){
							dtTable.fnDraw();
						},
						onSet: function(callerObj, filterObj){
							if(filterObj.name == 'group'){
								$('.menu-level3 a[data-value="' + filterObj.value + '"]').parent('li')
									.addClass('active').siblings().removeClass('active');
							}

							if (filterObj.name == 'reg_date_from') {
								$('input[name="reg_date_to"]').datepicker("option", "minDate", $('input[name="reg_date_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'reg_date_to') {
								$('input[name="reg_date_from"]').datepicker("option", "maxDate", $('input[name="reg_date_to"]').datepicker("getDate"));
							}
						},
						onDelete: function(filterObj){
							if(filterObj.name == 'group'){
								$('a[data-value="' + filterObj.default + '"]').parent('li')
									.addClass('active').siblings().removeClass('active');
							}
						},
						onReset: function(){
							$('.filter-admin-panel .hasDatepicker').datepicker( "option" , {
								minDate: null,
								maxDate: null
							});
						}
					});
				}

				aoData = aoData.concat(dtFilter.getDTFilter());

				$.ajax( {
					"dataType": 'json',
					"type": "POST",
					"url": sSource,
					"data": aoData,
					"success": function (data, textStatus, jqXHR) {
						if(data.mess_type == 'error')
							systemMessages(data.message, 'message-' + data.mess_type);

						fnCallback(data, textStatus, jqXHR);
						$('.menu-level3 li > a[data-group="all"] span.users_counter').text(data.iTotalRecords);
						$.each(data.groups_users_count, function(id_group, group_obj){
							$('.menu-level3 li > a[data-group="'+id_group+'"] span.users_counter').text(group_obj.counter);
						});
					}
				} );
			},
			"sPaginationType": "full_numbers",
			"fnDrawCallback": function( oSettings ) {}
		});
	});

	var delete_request = function(obj){
		var $this = $(obj);
		var id_request = $this.data('request');
		$.ajax({
			url: '<?php echo __SITE_URL;?>cr_users/ajax_operations/delete_request',
			type: 'POST',
			data:  {id_request:id_request},
			dataType: 'json',
			success: function(resp){
				systemMessages(resp.message, 'message-' + resp.mess_type );
                if(resp.mess_type == 'success'){
					dtTable.fnDraw(false);
				}
			}
		});
	}
</script>
<div class="row">
	<div class="col-xs-12">
		<?php tmvc::instance()->controller->view->display('admin/cr/users_requests/filters_view')?>
		<div class="titlehdr h-30">
			<span>Brand Ambassador requests</span>
		</div>
		<div class="wr-filter-list clearfix mt-10"></div>
		<table class="data table-bordered table-striped w-100pr" id="dtTable">
			<thead>
				<tr>
					<th class="dt_request"><input type="checkbox" class="check-all-users mt-1"></th>
					<th class="dt_fullname">Full name</th>
					<th class="dt_email">Email</th>
					<th class="dt_country"><span class="ep-icon ep-icon_globe fs-22 m-0"></span></th>
					<th class="dt_registered">Registered</th>
					<th class="dt_status">Status</th>
					<th class="dt_actions">Actions</th>
				</tr>
			</thead>
			<tbody class="tabMessage" id="pageall">
			</tbody>
		</table>
	</div>
</div>
