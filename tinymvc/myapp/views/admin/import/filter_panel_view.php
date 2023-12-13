<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
        <table class="w-300">
			<tr>
				<td>Search by</td>
				<td>
					<input class="dt_filter" type="text" data-title="Search for" maxlength="50" placeholder="Keywords">
				</td>
			</tr>
			<tr>
				<td>Status</td>
				<td>
					<select class="dt_filter" data-title="Status" name="status">
						<option value="" data-value-text="">Select status</option>
						<option value="new" data-value-text="New">New</option>
						<option value="updated" data-value-text="Updated">Updated</option>
						<option value="ready" data-value-text="Ready to notify">Ready to notify</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Type</td>
				<td>
					<select class="dt_filter" data-title="Type" name="type">
						<option value="" data-value-text="">Select type</option>
						<option value="company_seller" data-value-text="Company, Seller">Company, Seller</option>
						<option value="seller" data-value-text="Seller">Seller</option>
						<option value="buyer" data-value-text="Buyer">Buyer</option>
						<option value="shipper" data-value-text="Freight Forwarder">Freight Forwarder</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Start date</td>
				<td>
				 	<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Start date from" name="start" id="start_date" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Start date to" name="finish" id="finish_date" placeholder="To" readonly>
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
    $(function(){
        $( "#start_date, #finish_date" ).datepicker();
    });
</script>
