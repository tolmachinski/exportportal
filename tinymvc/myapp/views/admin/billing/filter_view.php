<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
        <div class="title-b">Filter panel</div>

        <?php if (!empty($filters['bill'])) { ?>

            <input
                class="form-control dt_filter"
                type="hidden"
                name="bill"
                value="<?php echo cleanOutput($filters['bill']); ?>"
                data-title="Bill"
                data-value-text="<?php echo cleanOutput(orderNumber($filters['bill'])); ?>">
        <?php } ?>

		<table>
			<tr>
				<td>Search</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" name="search" value="" data-title="Keywords" placeholder="Keywords">
						<div class="input-group-btn">
							<button class="btn btn-primary m-0 dt-filter-apply dt-filter-apply-buttons">&gt;&gt;</button>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>By bills status:</td>
				<td>
					<select class="dt_filter" data-title="Bill status" name="status">
						  <option value="" data-default="true">All</option>
						  <option data-value-text="Initiated" value="init">Initiated</option>
						  <option data-value-text="Paid" value="paid">Paid</option>
						  <option data-value-text="Confirmed" value="confirmed">Confirmed</option>
						  <option data-value-text="Cancelled" value="unvalidated">Cancelled</option>
					</select>
				</td>
			</tr>

			<tr>
				<td>Bills type <br> (default all):</td>
				<td>
                    <label class="display-b">
                        <input type="radio" data-default="true" name="bill_type" class="dt_filter" data-title="Bills type" data-value-text="all" value="all">
                        All
					</label>

                    <?php foreach($bills_types as $item){?>
					<label class="display-b">
						<input type="radio" name="bill_type" class="dt_filter" data-title="Bills type" data-value-text="<?php echo $item['show_name'];?>" value="<?php echo $item['id_type'];?>">
						<?php echo $item['show_name'];?>
					</label>
                    <?php }?>
				</td>
			</tr>

			<tr>
				<td>Amount</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Add amount from" name="amount_from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Add amount to" name="amount_to" placeholder="To">
					</div>
				</td>
			</tr>

			<tr>
				<td>Date</td>
				<td>
				    <div class="clearfix">
                        <label class="display-b">
                            <input type="radio" name="date_column" class="dt_filter" data-title="Date type" data-value-text="Create date" value="create_date" checked="checked">
                            Create date
                        </label>
                        <label class="display-b">
                            <input type="radio" name="date_column" class="dt_filter" data-title="Date type" data-value-text="Due date" value="due_date">
                            Pay due
                        </label>
                        <label class="display-b">
                            <input type="radio" name="date_column" class="dt_filter" data-title="Date type" data-value-text="Paid date" value="pay_date">
                            Paid date
                        </label>
                        <label class="display-b">
                            <input type="radio" name="date_column" class="dt_filter" data-title="Date type" data-value-text="Confirmed date" value="confirmed_date">
                            Confirmed date
                        </label>
                        <label class="display-b">
                            <input type="radio" name="date_column" class="dt_filter" data-title="Date type" data-value-text="Declined date" value="declined_date">
                            Declined date
                        </label>
                        <label class="display-b">
                            <input type="radio" name="date_column" class="dt_filter" data-title="Date type" data-value-text="Change date" value="change_date">
                            Change date
                        </label>
				    </div>

					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Add date from" name="date_from" id="date_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Add date to" name="date_to" id="date_to" placeholder="To" readonly>
					</div>
				</td>
            </tr>

            <tr>
				<td>Real Users:</td>
				<td>
					<select class="dt_filter" data-title="Real users" name="real_users">
						<option data-value-text="All" value="2">All</option>
						<option data-value-text="Yes" data-default="true" value="1" selected>Yes</option>
						<option data-value-text="No" value="0">No</option>
					</select>
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
	$("#date_from, #date_to").datepicker();
})
</script>
