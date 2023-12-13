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
				<td>Visible</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="visible" data-title="Visible" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="visible" data-title="Visible" data-value-text="Yes" value="1">
							<span class="input-group__desc">Yes</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="visible" data-title="Visible" data-value-text="No" value="0">
							<span class="input-group__desc">No</span>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<td>Create date</td>
				<td>
				 	<div class="input-group">
						<input class="form-control dt_filter js-filter-date" type="text" data-title="Create date from" name="date_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter js-filter-date" type="text" data-title="Create date to" name="date_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Updated date</td>
				<td>
				 	<div class="input-group">
						<input class="form-control dt_filter js-filter-date" type="text" data-title="Updated date from" name="date_update_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter js-filter-date" type="text" data-title="Updated date to" name="date_update_to" placeholder="To" readonly>
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
		$(".js-filter-date").datepicker();
    });
</script>
