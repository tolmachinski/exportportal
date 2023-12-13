<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>

		<table>
			<tr>
				<td>By user status:</td>
				<td>
					<select class="dt_filter" data-title="User status" name="user_status">
						<option value="" data-default="true">All</option>
                        <?php foreach($statuses as $statuses_item){?>
						  <option data-value-text="<?php echo $statuses_item['name'];?>" value="<?php echo strtolower($statuses_item['name']);?>"><?php echo $statuses_item['name'];?></option>
                        <?php }?>
					</select>
				</td>
			</tr>
			<tr>
				<td>By type registered:</td>
				<td>
					<select class="dt_filter" data-title="User status" name="user_type">
						<option value="" data-default="true">All</option>
                        <?php foreach($usergroup as $usergroup_item){?>
						  <option data-value-text="<?php echo $usergroup_item['gr_name'];?>" value="<?php echo $usergroup_item['idgroup'];?>"><?php echo $usergroup_item['gr_name'];?></option>
                        <?php }?>
					</select>
				</td>
			</tr>

			<tr>
				<td>Date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Start date from" name="start_date" id="js-start-date" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Start date to" name="finish_date" id="js-finish-date" placeholder="To" readonly>
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
		$("#js-start-date, #js-finish-date").datepicker();
    })
</script>
