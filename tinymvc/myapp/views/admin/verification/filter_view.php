<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>
		<table class="w-100pr">
			<tr>
				<td>Search</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
							<input type="text" <?php echo addQaUniqueIdentifier("admin-verification__filter__search-input")?> name="search" class="dt_filter form-control" value=""  data-title="Search" placeholder="Keywords">
							<span class="input-group-btn">
								<a <?php echo addQaUniqueIdentifier("admin-verification__filter__search-apply-button")?> class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0"><i class="ep-icon ep-icon_magnifier "></i></a>
							</span>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>User ID</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
							<input <?php echo addQaUniqueIdentifier("admin-verification__filter__user-id-input")?> type="text" name="id_user" class="dt_filter form-control" value="<?php echo !empty($filters['user']['value']) ? $filters['user']['value'] : null; ?>"  data-title="User ID" placeholder="User ID">
							<span class="input-group-btn">
								<a <?php echo addQaUniqueIdentifier("admin-verification__filter__user-id-apply-button")?> class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0"><i class="ep-icon ep-icon_magnifier "></i></a>
							</span>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>CRM Contact ID</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
							<input type="text" name="crm_contact_id" class="dt_filter form-control" value=""  data-title="Contact ID" placeholder="Contact ID">
							<span class="input-group-btn">
								<a class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0"><i class="ep-icon ep-icon_magnifier "></i></a>
							</span>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>User group</td>
				<td>
					<select <?php echo addQaUniqueIdentifier("admin-verification__filter__user-group-select")?> class="form-control w-100pr dt_filter" data-title="Group" name="group" data-type="select">
						<option value="" data-default="true">All groups</option>
						<option value="1">Buyer</option>
						<option value="2">Verified Seller</option>
						<option value="3">Certified Seller</option>
						<option value="5">Verified Manufacturer</option>
						<option value="6">Certified Manufacturer</option>
						<option value="31">Freight Forwarder</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Upload status</td>
				<td>
					<select class="form-control w-100pr dt_filter" data-title="Upload documents status" name="upload_status" data-type="select">
						<option value="" data-default="true">All statuses</option>
						<option value="none">Not uploaded</option>
						<option value="partial">Some documents uploaded</option>
						<option value="full">All documents uploaded</option>
					</select>
				</td>
			</tr>
            <tr>
				<td>Email status</td>
				<td>
					<select class="form-control w-100pr dt_filter" data-title="Email status" name="email_status" data-type="select">
                        <option value="" data-default="true">All statuses</option>
                        <option value="Ok">Ok</option>
                        <option value="Unknown">Unknown</option>
                        <option value="Bad">Bad</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>OnLine/OffLine</td>
				<td>
					<select class="form-control w-100pr dt_filter" data-title="OnLine/OffLine" name="online"  data-type="select" id="online">
						<option value="" data-default="true">All</option>
						<option value="1">Online</option>
						<option value="0">Offline</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Registered</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
							<input type="text" class="form-control dt_filter date-picker" name="reg_date_from" data-title="Registered from" placeholder="From">
							<div class="input-group-addon">-</div>
							<input type="text" class="form-control dt_filter date-picker" name="reg_date_to" data-title="Registered to" placeholder="To">
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>Last resend date</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
							<input type="text" class="form-control dt_filter date-picker" name="resend_date_from" data-title="Last resend date from" placeholder="From">
							<div class="input-group-addon">-</div>
							<input type="text" class="form-control dt_filter date-picker" name="resend_date_to" data-title="Last resend date to" placeholder="To">
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>Last upload date</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
							<input type="text" class="form-control dt_filter date-picker" name="upload_date_from" data-title="Last upload date from" placeholder="From">
							<div class="input-group-addon">-</div>
							<input type="text" class="form-control dt_filter date-picker" name="upload_date_to" data-title="Last upload date to" placeholder="To">
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>Calling status</td>
				<td>
					<select class="form-control w-100pr dt_filter" data-title="Calling status" name="calling_status" data-type="select">
						<option value="" data-default="true">All statuses</option>
						<?php foreach($calling_statuses as $calling_status) { ?>
							<option value="<?php echo $calling_status['id_status']; ?>">
								<?php echo $calling_status['status_title']; ?>
							</option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Last calling date</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
							<input type="text" class="form-control dt_filter date-picker" name="calling_from_date" data-title="Last calling date from" placeholder="From">
							<div class="input-group-addon">-</div>
							<input type="text" class="form-control dt_filter date-picker" name="calling_to_date" data-title="Last calling date to" placeholder="To">
						</div>
					</div>
				</td>
            </tr>
            <tr>
                <td>Location status</td>
                <td>
                    <select class="form-control dt_filter" data-title="Location status" name="location_completion">
                        <option value="" data-default="true">N/A</option>
                        <option value="1">Completed</option>
                        <option value="0">Incompleted</option>
                    </select>
                </td>
            </tr>
		</table>
		<div class="wr-filter-list clearfix mt-10"></div>
	</div>
	<div class="btn-display" <?php echo addQaUniqueIdentifier("admin-users-verification__filters-open-close-button")?>>
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
