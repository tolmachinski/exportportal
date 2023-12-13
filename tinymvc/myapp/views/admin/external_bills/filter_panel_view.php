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
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="status" data-title="Status" data-value-text="All" value="">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="status" data-title="Status" data-value-text="Processed" value="processed">
							<span class="input-group__desc">Processed</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="status" data-title="Status" data-value-text="Waiting" value="waiting" checked="checked">
							<span class="input-group__desc">Waiting</span>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<td>User type</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="type" data-title="User type" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="type" data-title="User type" data-value-text="Seller" value="seller">
							<span class="input-group__desc">Seller</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="type" data-title="User type" data-value-text="Buyer" value="buyer">
							<span class="input-group__desc">Buyer</span>
						</label>
					</div>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="type" data-title="User type" data-value-text="Freight Forwarder" value="shipper">
							<span class="input-group__desc">Freight Forwarder</span>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<td>Add date</td>
				<td>
				 	<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Add date from" name="start" id="start_date" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Add date to" name="finish" id="finish_date" placeholder="To" readonly>
					</div>
				</td>
			</tr>
            <tr>
				<td><?php echo translate('ep_administration_real_users_text') ?></td>
				<td>
					<div class="input-group input-group--checks">
                        <label class="input-group-addon">
                            <input class="dt_filter" type="radio" name="real_users" data-title="<?php echo translate('ep_administration_real_users_text', null, true) ?>" data-value-text="All" value="2">
                            <span class="input-group__desc">All</span>
                        </label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="real_users" data-default="true" data-title="<?php echo translate('ep_administration_real_users_text', null, true) ?>" data-value-text="Yes" value="1" checked="checked">
							<span class="input-group__desc">Yes</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="real_users" data-title="<?php echo translate('ep_administration_real_users_text', null, true) ?>" data-value-text="No" value="0">
							<span class="input-group__desc">No</span>
						</label>
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
