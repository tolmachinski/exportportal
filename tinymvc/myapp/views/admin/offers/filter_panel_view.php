<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
        <table>
			<tr>
				<td>Change date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter update_from" type="text" data-title="Update date from" name="update_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter update_to" type="text" data-title="Update date to" name="update_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Search by</td>
				<td>
					<input class="keywords dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" placeholder="Keywords">
				</td>
			</tr>
			<tr>
				<td>Status offers</td>
				<td>
					<select class="dt_filter" data-title="Status" name="status">
						<option data-default="true" value="">All statuses</option>
						<option value="new">New</option>
						<option value="wait_buyer">Wait buyer</option>
						<option value="wait_seller">Wait seller</option>
						<option value="accepted">Accepted</option>
						<option value="initiated">Order initiated</option>
						<option value="declined">Declined</option>
						<option value="expired">Expired</option>
					</select>
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
$(document).ready(function(){
	$(".filter-admin-panel").find("input[name=update_from], input[name=update_to]" ).datepicker();
})
</script>
