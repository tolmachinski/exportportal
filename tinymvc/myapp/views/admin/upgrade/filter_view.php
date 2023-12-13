<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>
		<table class="w-100pr">
			<tr>
				<td>Request Type</td>
				<td>
					<select class="form-control w-100pr dt_filter" data-title="Request Type" name="request_type" data-type="select">
						<option value="">All</option>
						<option value="upgrade">Upgrade</option>
						<option value="extend">Extend</option>
						<option value="downgrade">Downgrade</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Request Status</td>
				<td>
					<select class="form-control w-100pr dt_filter" data-title="Request Status" name="request_status" data-type="select">
						<option value="">All</option>
						<option value="new">New</option>
						<option value="confirmed">Confirmed</option>
						<option value="canceled">Canceled</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>User ID</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
							<input type="text" name="id_user" class="dt_filter form-control" value=""  data-title="User ID" placeholder="User ID">
							<span class="input-group-btn">
								<a class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0"><i class="ep-icon ep-icon_magnifier "></i></a>
							</span>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>Created date</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
							<input type="text" class="form-control dt_filter date-picker" name="created_date_from" data-title="Created date from" placeholder="From">
							<div class="input-group-addon">-</div>
							<input type="text" class="form-control dt_filter date-picker" name="created_date_to" data-title="Created date to" placeholder="To">
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>Updated date</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
							<input type="text" class="form-control dt_filter date-picker" name="updated_date_from" data-title="Updated date from" placeholder="From">
							<div class="input-group-addon">-</div>
							<input type="text" class="form-control dt_filter date-picker" name="updated_date_to" data-title="Updated date to" placeholder="To">
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>Expire date</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
							<input type="text" class="form-control dt_filter date-picker" name="expire_date_from" data-title="Expire date from" placeholder="From">
							<div class="input-group-addon">-</div>
							<input type="text" class="form-control dt_filter date-picker" name="expire_date_to" data-title="Expire date to" placeholder="To">
						</div>
					</div>
				</td>
			</tr>
		</table>
		<div class="wr-filter-list clearfix mt-10"></div>
	</div>
	<div class="btn-display" <?php echo addQaUniqueIdentifier("admin-users-request_filters-open-close_btn")?>>
		<div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
		<span>&laquo;</span>
	</div>
	<div class="wr-hidden" style="display: none;"></div>
</div>
<script>
    $(document).ready(function(){
        $( ".date-picker" ).datepicker();
    })
</script>
