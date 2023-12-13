<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>

		<table>
			<tr>
				<td>By company type:</td>
				<td>
					<select class="dt_filter" data-title="Type" name="type_company">
						<option value="" data-default="true">All</option>
						<option value="1">Company</option>
						<option value="2">Branch</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>By company visibility:</td>
				<td>
					<select class="dt_filter" data-title="Visibility" name="visibility">
						<option value="" data-default="true">All</option>
						<option value="1">Visible</option>
						<option value="0">Invisible</option>
					</select>
				</td>
			</tr>
            <tr>
				<td>Access type:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="access" data-title="Access" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="access" data-title="Access" data-value-text="Public" value="public">
							<i class="ep-icon ep-icon_sheild-nok txt-red input-group__desc"></i>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="access" data-title="Access" data-value-text="Private" value="private">
							<i class="ep-icon ep-icon_sheild-ok txt-green input-group__desc"></i>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<td>Start date</td>
				<td>
				 	<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Start date from" name="start_date" id="start_date" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Start date to" name="finish_date" id="finish_date" placeholder="To" readonly>
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

<script>
    $(document).ready(function() {
		$("#start_date, #finish_date").datepicker();
    })
</script>