<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
        <table>
			<tr>
				<td>Create date</td>
				<td>
					<div >
						<input type="text" data-title="Create date from" name="start_from" class="dt_filter w-90" placeholder="From" readonly>
						<span class="lh-30">-</span>
						<input type="text" data-title="Create date to" name="start_to" class="dt_filter w-90" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Change date</td>
				<td>
					<div >
						<input type="text" data-title="Update date from" name="update_from" class="dt_filter w-90" placeholder="From" readonly>
						<span class="lh-30">-</span>
						<input type="text" data-title="Update date to" name="update_to" class="dt_filter w-90" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Search by</td>
				<td>
					<span class="mr-10">
						<input class="w-190 dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" placeholder="Keywords">
					</span>
				</td>
			</tr>
			<tr>
				<td>Status request <br /> estimate</td>
				<td>
					<span class="mr-10">
						<select class="dt_filter w-190" data-title="Status" name="status">
							<option data-default="true" value="">All statuses</option>
							<option value="new">New</option>
							<option value="wait_buyer">Wait buyer</option>
							<option value="wait_seller">Wait seller</option>
							<option value="accepted">Accepted</option>
							<option value="initiated">Order initiated</option>
							<option value="declined">Declined</option>
							<option value="expired">Expired</option>
						</select>
					</span>
				</td>
			</tr>
        </table>
		<div class="wr-filter-list clearfix mt-10 "></div>
    </div>
    <div class="btn-display "> <div class="i-block"><i class="ep-icon ep-icon_filter"></i></div> <span>&laquo;</span></div>
	<div class="wr-hidden"></div>
</div>

<script>
$(document).ready(function(){
	$( ".filter-admin-panel").find(" input[name^=start_], input[name^=update_] " ).datepicker();
})
</script>
