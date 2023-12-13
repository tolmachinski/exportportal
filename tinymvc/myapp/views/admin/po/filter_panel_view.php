<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
        <table>
			<tr>
				<td>Create date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Create date from" name="start_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Create date to" name="start_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Change date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Update date from" name="update_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Update date to" name="update_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Search by</td>
				<td>
					<input class="dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" placeholder="Keywords">
				</td>
			</tr>
			<tr>
				<td>Status PO</td>
				<td>
					<select class="dt_filter" data-title="Status" name="status">
						<option data-default="true" value="">All statuses</option>
						<option value="initiated">Initiated</option>
						<option value="po_processing">In process</option>
						<option value="prototype_confirmed">Prototype confirmed</option>
						<option value="order_initiated">Order initiated</option>
						<option value="declined">Declined</option>
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
	$(".filter-admin-panel").find("input[name^=start_], input[name^=update_]" ).datepicker();
})
</script>
