<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr">
			<span>EP Events</span>
            <div class="dropdown pull-right">
				<button class="btn btn-default dropdown-toggle mb-5" type="button" id="actionsMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					Actions
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" aria-labelledby="actionsMenu">
                    <li>
                        <a class="fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL . 'ep_events/popup_forms/add_online_event';?>" data-title="Online event" title="Add online event" data-table="dtEvents">
                            <i class="fs-12 lh-12 ep-icon ep-icon_events "></i>
                            Add online event
                        </a>
                    </li>
                    <li>
                        <a class="fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL . 'ep_events/popup_forms/add_offline_event';?>" data-title="Offline event" title="Add offline event" data-table="dtEvents">
                            <i class="fs-12 lh-12 ep-icon ep-icon_events "></i>
                            Add offline event
                        </a>
                    </li>
                    <li>
                        <a class="fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL . 'ep_events/popup_forms/add_webinar';?>" data-title="Webinar" title="Add webinar" data-table="dtEvents">
                            <i class="fs-12 lh-12 ep-icon ep-icon_events "></i>
                            Add webinar
                        </a>
                    </li>
                </ul>
			</div>
		</div>

        <?php views('admin/ep_events/filter_panel_view'); ?>

        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped table-bordered w-100pr" id="dtEvents" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id tac w-30 vam">#</th>
                    <th class="dt_type tac vam">Type</th>
                    <th class="dt_image tac vam">Image</th>
                    <th class="dt_title tac vam">Title</th>
                    <th class="dt_location tac vam">Location</th>
                    <th class="dt_price tac vam">Ticket price</th>
                    <th class="dt_start_date tac vam">Start Date</th>
                    <th class="dt_end_date tac vam">End Date</th>
                    <th class="dt_is_recomended tac vam w-130">Recommended by EP</th>
                    <th class="dt_is_upcoming tac vam w-95">Upcoming by EP</th>
                    <th class="dt_is_attended tac vam w-90">Attended by EP</th>
                    <?php if (have_right('view_event_statistic')) { ?>
                        <th class="dt_event_views tac vam w-90">Views</th>
                    <?php } ?>
                    <th class="dt_actions tac vam w-90">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<?php views('admin/file_upload_scripts');?>

<script type="text/javascript" src="<?php echo asset("/public/plug_admin/jquery-timepicker-addon-1-6-3/js/jquery-ui-timepicker-addon.min.js", "legacy"); ?>"></script>
<script>
    var requirementFilters;
    var dtEvents;

    $(document).ready(function(){
        var additionalColumns = [];
        var showStatisticColumn = Boolean(~~parseInt('<?php echo (int) have_right('view_event_statistic'); ?>'));
        if (showStatisticColumn) {
            additionalColumns.push({ "sClass": "tac vam w-90", "aTargets": ['dt_event_views'], "mData": "dt_event_views", "bSortable": true });
        }
        dtEvents = $('#dtEvents').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . "ep_events/ajax_dt_administration";?>",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "tac vam w-30",  "aTargets": ['dt_id'], "mData": "dt_event_id"},
                { "sClass": "tac vam", "aTargets": ['dt_type'], "mData": "dt_event_type", "bSortable": false },
                { "sClass": "tac vam", "aTargets": ['dt_image'], "mData": "dt_event_image", "bSortable": false },
                { "sClass": "tac vam", "aTargets": ['dt_title'], "mData": "dt_event_title", "bSortable": false },
                { "sClass": "tac vam", "aTargets": ['dt_location'], "mData": "dt_event_location", "bSortable": false },
                { "sClass": "tac vam", "aTargets": ['dt_price'], "mData": "dt_event_price"},
                { "sClass": "tac vam", "aTargets": ['dt_start_date'], "mData": "dt_event_start_date"},
                { "sClass": "tac vam", "aTargets": ['dt_end_date'], "mData": "dt_event_end_date"},
                { "sClass": "tac vam w-130", "aTargets": ['dt_is_recomended'], "mData": "dt_event_is_recommended", "bSortable": false },
                { "sClass": "tac vam w-95", "aTargets": ['dt_is_upcoming'], "mData": "dt_event_is_upcoming", "bSortable": false },
                { "sClass": "tac vam w-90", "aTargets": ['dt_is_attended'], "mData": "dt_event_is_attended", "bSortable": false },
                { "sClass": "tac vam w-90", "aTargets": ['dt_actions'], "mData": "dt_event_actions", "bSortable": false },
                ...additionalColumns
            ],
            "sorting": [[0, "desc"]],
            "fnServerData": function ( sSource, aoData, fnCallback ) {
                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){
                            dtEvents.fnDraw();
                        },
                        onSet: function(callerObj, filterObj){
                            switch (filterObj.name) {
                                case 'start_date_from':
                                    $('input[name="start_date_to"]').datepicker("option", "minDate", $('input[name="start_date_from"]').datepicker("getDate"));
                                break;
                                case 'start_date_to':
                                    $('input[name="start_date_from"]').datepicker("option", "maxDate", $('input[name="start_date_to"]').datepicker("getDate"));
                                break;
                                case 'end_date_from':
                                    $('input[name="end_date_to"]').datepicker("option", "minDate", $('input[name="end_date_from"]').datepicker("getDate"));
                                break;
                                case 'end_date_to':
                                    $('input[name="end_date_from"]').datepicker("option", "maxDate", $('input[name="end_date_to"]').datepicker("getDate"));
                                break;
                            }
						},
                        onDelete: function(callerObj, filterObj){
                            switch (filterObj.name) {
                                case 'start_date_from':
                                    $('input[name="start_date_to"]').datepicker("option", {minDate: null});
                                break;
                                case 'start_date_to':
                                    $('input[name="start_date_from"]').datepicker("option", {maxDate: null});
                                break;
                                case 'end_date_from':
                                    $('input[name="end_date_to"]').datepicker("option", {minDate: null});
                                break;
                                case 'end_date_to':
                                    $('input[name="end_date_from"]').datepicker("option", {maxDate: null});
                                break;
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

                aoData = aoData.concat(requirementFilters.getDTFilter());
                $.ajax( {
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if (data.mess_type == 'error' || data.mess_type == 'info'){
                            systemMessages(data.message, 'message-' + data.mess_type);
                        }

                        fnCallback(data, textStatus, jqXHR);

                    }
                });
            },
            "sPaginationType": "full_numbers",
            "lengthMenu": [[10, 50, 100, 250], [10, 50, 100, 250]],
            "fnDrawCallback": function( oSettings ) {

            }
        });

        globalThis.addEventListener('events:update', function () {
            dtEvents.fnDraw(false);
        });
    });

    var togleRecommendedStatus = function (element) {
        var btn = $(element);
        var eventId = btn.data('id');

        $.ajax({
			type: 'POST',
            url: '<?php echo __SITE_URL . 'ep_events/ajax_operations/togle_recommended_status';?>',
            data: { event : eventId},
			dataType: 'json',
			success: function(response){
				systemMessages( response.message, 'message-' + response.mess_type );

				if (response.mess_type == 'success') {
					dtEvents.fnDraw(false);
				}
			}
		});
    }

    var togleUpcomingStatus = function (element) {
        var btn = $(element);
        var eventId = btn.data('id');

        $.ajax({
			type: 'POST',
            url: '<?php echo __SITE_URL . 'ep_events/ajax_operations/togle_upcoming_status';?>',
            data: { event : eventId},
			dataType: 'json',
			success: function(response){
				systemMessages( response.message, 'message-' + response.mess_type );

				if (response.mess_type == 'success') {
					dtEvents.fnDraw(false);
				}
			}
		});
    }

    var togleAttendedStatus = function (element) {
        var btn = $(element);
        var eventId = btn.data('id');

        $.ajax({
			type: 'POST',
            url: '<?php echo __SITE_URL . 'ep_events/ajax_operations/togle_attended_status';?>',
            data: { event : eventId},
			dataType: 'json',
			success: function(response){
				systemMessages( response.message, 'message-' + response.mess_type );

				if (response.mess_type == 'success') {
					dtEvents.fnDraw(false);
				}
			}
		});
    }

    var highlightEvent = function (element) {
        var btn = $(element);
        var eventId = btn.data('id');

        $.ajax({
			type: 'POST',
            url: '<?php echo __SITE_URL . 'ep_events/ajax_operations/highlight_event';?>',
            data: { event : eventId},
			dataType: 'json',
			success: function(response){
				systemMessages( response.message, 'message-' + response.mess_type );

				if (response.mess_type == 'success') {
					dtEvents.fnDraw(false);
				}
			}
		});
    }

    var deleteEventPromotion = function (element) {
        var button = $(element);
        var url = button.data('actionUrl');
        var onRequestSuccess = function (response) {
            systemMessages(response.message, response.mess_type);
            if(response.mess_type === 'success'){
                dtEvents.fnDraw(false);
            }
        };

        if (null === url) {
            return Promise.resolve();
        }

        return postRequest(url).then(onRequestSuccess).catch(onRequestError)
    };
</script>
