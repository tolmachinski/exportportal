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
				<td>From</td>
				<td>
				 	<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Add date from" name="start_date" id="start_date" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Add date to" name="finish_date" id="finish_date" placeholder="To" readonly>
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
