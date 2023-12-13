<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>

		<table>
			<tr>
				<td>By order status:</td>
				<td>
					<select class="dt_filter" data-title="Order status" name="order_status">
						<option value="" data-default="true">All</option>
                        <?php foreach($orders_status as $status){?>
						  <option data-value-text="<?php echo $status['status'];?>" value="<?php echo $status['id'];?>"><?php echo $status['status'];?></option>
                        <?php }?>
					</select>
				</td>
			</tr>

			<tr>
				<td>Price</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Price from" name="price_from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Price to" name="price_to" placeholder="To">
					</div>
				</td>
			</tr>

			<tr>
				<td>Start date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Start date from" name="start_date" id="start_date" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Start date to" name="finish_date" id="finish_date" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Search by</td>
				<td>
					<input class="keywords dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" id="keywords" placeholder="Keywords">
				</td>
			</tr>
			<tr>
				<td>Cancel requests</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="cancel_request" data-title="Cancel requests" data-value-text="Without requests" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="cancel_request" data-title="Cancel requests" data-value-text="New requests" value="1">
							<span class="input-group__desc">New</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="cancel_request" data-title="Cancel requests" data-value-text="Processed requests" value="2">
							<span class="input-group__desc">Processed</span>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<td>Disputes</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="dispute_opened" data-title="Disputes" data-value-text="Without disputes" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="dispute_opened" data-title="Disputes" data-value-text="New disputes" value="1">
							<span class="input-group__desc">New</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="dispute_opened" data-title="Disputes" data-value-text="Processed disputes" value="2">
							<span class="input-group__desc">Processed</span>
						</label>
					</div>
				</td>
			</tr>
            <tr>
				<td>EP manager</td>
				<td>
					<input class="dt_filter" type="text" data-title="EP manager" name="manager_email" placeholder="Ep manager email">
				</td>
			</tr>
            <tr>
				<td><?php echo translate('ep_administration_real_users_text'); ?></td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="real_users" data-title="<?php echo translate('ep_administration_real_users_text', null, true); ?>" data-value-text="All" value="2">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="real_users" data-default="true" data-title="<?php echo translate('ep_administration_real_users_text', null, true); ?>" data-value-text="Yes" value="1" checked="checked">
							<span class="input-group__desc">Yes</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="real_users" data-title="<?php echo translate('ep_administration_real_users_text', null, true); ?>" data-value-text="No" value="0">
							<span class="input-group__desc">No</span>
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
