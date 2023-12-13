<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
		<table>
			<tr>
				<td>Search by</td>
				<td>
					<input class="dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" placeholder="Keywords">
				</td>
			</tr>

			<tr>
				<td>Visible:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="status" data-title="Visible" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="status" data-title="Visible" data-value-text="No" value="disabled">
							<i class="ep-icon ep-icon_invisible txt-blue input-group__desc"></i>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="status" data-title="Visible" data-value-text="Yes" value="enabled">
							<i class="ep-icon ep-icon_visible txt-blue input-group__desc"></i>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<td>Locked:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="blocked" data-title="Locked" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="blocked" data-title="Locked" data-value-text="No" value="0">
							<i class="ep-icon ep-icon_unlocked txt-blue input-group__desc"></i>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="blocked" data-title="Locked" data-value-text="Yes" value="1">
							<i class="ep-icon ep-icon_locked txt-blue input-group__desc"></i>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<td>Create date</td>
				<td>
				 	<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Create date from" name="start_date_from" id="start_date" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Create date to" name="start_date_to" id="finish_date" placeholder="To" readonly>
					</div>
				</td>
			</tr>
		</table>
		<div class="wr-filter-list clearfix mt-10 "></div>
	</div>
	<div class="btn-display ">
		<div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
		<span>&laquo;</span>
	</div>

	<div class="wr-hidden"></div>
</div>
<script>
$("#start_date, #finish_date").datepicker();
</script>
