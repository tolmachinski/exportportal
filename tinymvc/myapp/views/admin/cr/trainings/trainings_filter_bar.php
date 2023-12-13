<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>

		<table>
			<tr>
				<td>Date from:</td>
				<td>
				 	<div class="input-group">
                        <input class="form-control edit-time dt_filter" type="text" data-title="Start date from" id="start_date_from" name="start_date_from"  placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control edit-time dt_filter" type="text" data-title="Start date to" id="start_date_to" name="start_date_to" placeholder="To">
					</div>
				</td>
			</tr>
			<tr>
				<td>Date to:</td>
				<td>
				 	<div class="input-group">
                        <input class="form-control edit-time dt_filter" type="text" data-title="Finish date from" id="finish_date_from" name="finish_date_from"  placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control edit-time dt_filter" type="text" data-title="Finish date to" id="finish_date_to" name="finish_date_to" placeholder="To">
					</div>
				</td>
			</tr>

			<tr>
				<td>Creation date:</td>
				<td>
				 	<div class="input-group">
                        <input class="form-control edit-time dt_filter" type="text" data-title="Creation date from" id="date_from" name="date_from"  placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control edit-time dt_filter" type="text" data-title="Creation date to" id="date_to" name="date_to" placeholder="To">
					</div>
				</td>
			</tr>

			<tr>
				<td>Type:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="type_filter" data-value-text="All" data-title="Type filter" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="type_filter" data-value-text="Training" data-title="Type filter" value="training">
                            <span>Training</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="type_filter" data-value-text="Webinar" data-title="Type filter" value="webinar">
                            <span>Webinar</span>
						</label>
					</div>
				</td>
			</tr>

			<tr>
				<td>Search by</td>
				<td>
					<input class="keywords dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" id="keywords" placeholder="Keywords">
				</td>
			</tr>

		</table>
		<div class="wr-filter-list clearfix mt-10"></div>
	</div>

	<div class="btn-display ">
		<div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
		<span>&laquo;</span>
	</div>

	<div class="wr-hidden"></div>
</div>

<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/jquery-timepicker-addon-1-6-3/js/jquery-ui-timepicker-addon.min.js"></script>
<script>
    $(document).ready(function(){
        $( "#start_date, #finish_date" ).datepicker();
    })

    $('.edit-time').datetimepicker({
        timeFormat: "hh:mm:00 TT",
        millisec_slider: false,
        numberOfMonths: 1
    });
</script>
