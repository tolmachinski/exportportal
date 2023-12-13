<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
        <table>
			<tr>
				<td>Requested at date</td>
				<td>
					<div>
						<input type="text" data-title="Requested date from" name="start_from" class="dt_filter w-90" placeholder="From" readonly>
						<span class="lh-30">-</span>
						<input type="text" data-title="Requested date to" name="start_to" class="dt_filter w-90" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Status</td>
				<td>
					<span class="mr-10">
						<select class="dt_filter w-190" data-title="Status" name="status">
							<option data-default="true" value="">All statuses</option>
							<option value="init">New</option>
							<option value="confirmed">Confirmed</option>
							<option value="canceled">Canceled</option>
						</select>
					</span>
				</td>
			</tr>
        </table>
		<div class="wr-filter-list clearfix mt-10 "></div>
    </div>
    <div class="btn-display">
		<div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
		<span>&laquo;</span>
	</div>

	<div class="wr-hidden"></div>
</div>

<script>
$(document).ready(function(){
	$(".filter-admin-panel").find("input[name^=start_]" ).datepicker();
})
</script>
