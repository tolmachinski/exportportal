<script>
	/* Formating function for row details */
	function fnFormatDetails(nTr){
		var aData = dtEventsList.fnGetData(nTr);
		var sOut = '<div class="dt-details"><table class="dt-details__table">';
			sOut += '<tr><td class="tar w-135">Event title:</td><td>' + aData['dt_full_title'] + '</td></tr>';
			sOut += '<tr><td class="tar">Event description:</td><td>' + aData['dt_full_description'] +'</td></tr>';
			sOut += '<tr><td class="tar">Event pictures:</td><td>' + aData['dt_images'] +'</td></tr>';
			sOut += '</table> </div>';
		return sOut;
	}

	var feature_event = function(opener){
		var $this = $(opener);
		var event = $this.data("event");
		$.ajax({
			type: "POST",
			dataType: "JSON",
			url: "events/ajax_make_event_featured",
			data: { event: event },
			success: function(resp){
				if(resp.mess_type == 'success'){
					 dtEventsList.fnDraw(false);
				}
				systemMessages(resp.message, 'message-' + resp.mess_type);
			}
		});
	}

	var change_visibility = function(opener){
		var $this = $(opener);
		var dataEvent = $this.data("event");
		$.ajax({
			type: "POST",
			url: "events/ajax_administration/change_visibility",
			data: { dataEvent: dataEvent },
			dataType: 'JSON',
			success: function(resp){
				if(resp.mess_type == 'success'){
					 dtEventsList.fnDraw();
				}
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		});
	}

	var change_blocked = function(opener){
		var $this = $(opener);
		var id = $this.data("event");
		var status = $this.data("state");

		$.ajax({
			type: "POST",
			url: "events/ajax_administration/change_blocked",
			data: { id: id, status: status },
			dataType: 'JSON',
			success: function(resp){
				if(resp.mess_type == 'success'){
					 dtEventsList.fnDraw();
				}
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		});
	}

	var moderate_event = function(opener){
		var $this = $(opener);
		var dataEvent = $this.data("event");
		$.ajax({
			type: "POST",
			context: $(this),
			url: "events/ajax_make_event_moderated",
			data: { event: dataEvent },
			dataType: 'JSON',
			success: function(resp){
				if(resp.mess_type == 'success'){
					dtEventsList.fnDraw();
				}
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		});
	}

	var delete_event = function(opener){
		var $this = $(opener);
		var dataEvent = $this.data('event');
		$.ajax({
			type: "POST",
			context: $(this),
			url: "events/ajax_event_operation/delete_event",
			data: { event: dataEvent },
			dataType: "JSON",
			success: function(resp){
				if(resp.mess_type == 'success'){
					dtEventsList.fnDraw();
				}
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		});
	}

	var delete_image = function(opener){
		var $this = $(opener);
		var dataImage = $this.data('image');
		$.ajax({
			type: "POST",
			context: $(this),
			url: "events/ajax_events_delete_existing_image",
			data: { fileinfo: dataImage },
			dataType: "JSON",
			success: function(resp){
				if(resp.mess_type == 'success'){
					$this.parent('div').fadeOut('slow', function(){$(this).remove();});
					dtEventsList.fnDraw();
				}
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		});
	}

	var dtEventsList;
	var eventsFilters;
	$(document).ready(function() {
		dtEventsList = $('#dtEventsList').dataTable({
			"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "<?php echo __SITE_URL;?>events/ajax_list_dt",
			"sServerMethod": "POST",
			"aoColumnDefs": [
				{"sClass": "w-40 tac", "aTargets": ['dt_id_event'], "mData": "dt_id_event"},
				{"sClass": "w-100 tal", "aTargets": ['dt_author'], "mData": "dt_author"},
				{"sClass": "tal vam", "aTargets": ['dt_title'], "mData": "dt_title"},
				{"sClass": "w-150 tal", "aTargets": ['dt_category'], "mData": "dt_category"},
				{"sClass": "w-60 tac", "aTargets": ['dt_type'], "mData": "dt_type"},
				{"sClass": "w-30 tac", "aTargets": ['dt_country'], "mData": "dt_country"},
				{"sClass": "w-120 tac vam", "aTargets": ['dt_date_event'], "mData": "dt_date_event"},
				{"sClass": "w-30 tac vam", "aTargets": ['dt_comments'], "mData": "dt_comments"},
				{"sClass": "w-30 tac vam", "aTargets": ['dt_followers'], "mData": "dt_followers"},
				{"sClass": "w-30 tac vam", "aTargets": ['dt_registered'], "mData": "dt_registered"},
				{"sClass": "w-80 tac", "aTargets": ['dt_actions'], "mData": "dt_actions" , 'bSortable': false}
			],
			"fnServerData": function ( sSource, aoData, fnCallback ) {
				if(!eventsFilters){
					eventsFilters = $('.plug').dtFilters('.dt_filter',{
						'container': '.wr-filter-list',
						callBack: function(){
							dtEventsList.fnDraw();
						},
						onSet: function(callerObj, filterObj){

							if(filterObj.name == 'date_type'){
								if(filterObj.value != filterObj.default && filterObj.value != "")
									$('.date_event').fadeIn('slow');
								else
									$('.date_event').fadeOut('slow');
							}
							if(filterObj.name == 'start_from'){
								$(".start_to").datepicker("option","minDate", $(".start_from").datepicker("getDate"));
							}
							if(filterObj.name == 'start_to'){
								$(".start_from").datepicker("option","maxDate", $(".start_to").datepicker("getDate"));
							}
							if(filterObj.name == 'end_from'){
								$(".end_to").datepicker("option","minDate", $(".end_from").datepicker("getDate"));
							}
							if(filterObj.name == 'end_to'){
								$(".end_from").datepicker("option","maxDate", $(".end_to").datepicker("getDate"));
							}
						},
						onDelete: function(filter){
							if(filter.name == 'date_type'){
								$li = $('a[data-name=' + filter.name + '][data-value="' + filter.default + '"]').parent('li');
								$li.siblings('li').removeClass('active');
								$li.addClass('active');
								$('.status_date_filter').fadeOut('slow');
								eventsFilters.removeFilter('date_to');
								eventsFilters.removeFilter('date_from');
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

				aoData = aoData.concat(eventsFilters.getDTFilter());
				$.ajax( {
					"dataType": 'JSON',
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

		$('body').on('click', 'a[rel=event_details]', function() {
			var $thisBtn = $(this);
			var nTr = $thisBtn.parents('tr')[0];

			if (dtEventsList.fnIsOpen(nTr))
				dtEventsList.fnClose(nTr);
			else
				dtEventsList.fnOpen(nTr, fnFormatDetails(nTr), 'details');

			$thisBtn.toggleClass('ep-icon_plus ep-icon_minus');
		});
		idStartItemNew = <?php echo $last_events_id;?>;
		startCheckAdminNewItems('events/ajax_event_operation/check_new', idStartItemNew);
	});
</script>
<div class="row">
	<div class="col-xs-12">
		<h3 class="titlehdr">
			<span>All Events</span>
		</h3>
		<?php tmvc::instance()->controller->view->display('admin/events/events_filters_view'); ?>
		<div class="wr-filter-list clearfix mt-10"></div>

		<table id="dtEventsList" class="data table-bordered table-striped w-100pr" >
			<thead>
				<tr>
					<th class="w-40 tac dt_id_event">#</th>
					<th class="w-150 tac dt_author">Author</th>
					<th class="tac dt_title">Title</th>
					<th class="w-150 tac dt_category">Category</th>
					<th class="w-80 tac dt_type">Type</th>
					<th class="w-40 tac dt_country pt-3 pb-3 pr-10"><span class="ep-icon ep-icon_globe lh-22 fs-25"></span></th>
					<th class="w-100 tac dt_date_event">Date</th>
					<th class="w-40 tal dt_comments"><span title="Comments count">C</span></th>
					<th class="w-40 tal dt_followers"><span title="Followers count">F</span></th>
					<th class="w-40 tal dt_registered"><span title="Registered customers">R</span></th>
					<th class="w-80 tac dt_actions">Actions</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
