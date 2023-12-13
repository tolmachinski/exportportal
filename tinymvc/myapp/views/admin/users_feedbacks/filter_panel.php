<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>

		<table>
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
					<input type="text" data-title="Search for" name="keywords" maxlength="50" class="keywords dt_filter" id="keywords" placeholder="Keywords">
				</td>
			</tr>

			<tr>
				<td>Moderated:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon" title="Show all reviews">
							<input class="dt_filter" type="radio" name="moderated" data-title="Status" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon" title="Show reviews which are not moderated">
							<input class="dt_filter" type="radio" name="moderated" data-title="Status" data-value-text="new" value="0">
							<i class="ep-icon ep-icon_sheild-nok txt-red input-group__desc"></i>
						</label>
						<label class="input-group-addon" title="Show reviews which are moderated">
							<input class="dt_filter" type="radio" name="moderated" data-title="Status" data-value-text="moderated" value="1">
							<i class="ep-icon ep-icon_sheild-ok txt-green input-group__desc"></i>
						</label>
					</div>
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
