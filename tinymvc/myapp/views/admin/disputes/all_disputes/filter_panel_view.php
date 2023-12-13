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
				<td>Status</td>
				<td>
					<select data-title="Status" name="status" class="dt_filter">
						<option value="" data-value-text="All" data-default="true">All</option>
						<?php foreach($statuses as $status_key => $status){?>
							<option value="<?php echo $status_key;?>" data-value-text="<?php echo $status['title'];?>" <?php echo empty($cur_order) ? selected($status_key, 'init') : '';?>><?php echo $status['title'];?></option>
						<?php }?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Dispute nr.</td>
				<td>
					<input class="dt_filter" type="text" data-title="Dispute" name="id_disput" placeholder="Dispute" value="<?php echo $cur_disput?>">
				</td>
			</tr>
			<tr>
				<td>Order nr.</td>
				<td>
					<input class="dt_filter" type="text" data-title="Order" name="order" placeholder="Order" value="<?php echo $cur_order?>">
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

			<tr>
				<td>Changed date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Changed date from" name="start_changed" id="start_changed" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Changed date to" name="finish_changed" id="finish_changed" placeholder="To" readonly>
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
    $("#start_date, #finish_date, #start_changed, #finish_changed").datepicker();
</script>
