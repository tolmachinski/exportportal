<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel" >
        <div class="title-b">Filter panel</div>
        <table class="w-100pr">
            <tr>
                <td>Search</td>
                <td>
                    <div class="form-group mb-0">
						<div class="input-group">
							<input type="text" name="search" class="dt_filter form-control" value=""  data-title="Search" placeholder="Keywords">
							<span class="input-group-btn">
								<a class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0"><i class="ep-icon ep-icon_magnifier "></i></a>
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
							<input type="text" name="id_user" class="dt_filter form-control" value=""  data-title="User ID" placeholder="User ID">
							<span class="input-group-btn">
								<a class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0"><i class="ep-icon ep-icon_magnifier "></i></a>
							</span>
						</div>
					</div>
				</td>
			</tr>
            <tr>
                <td>Status</td>
                <td>
                    <select class="form-control dt_filter" data-title="Status" name="status" data-type="select" id="statuses">
                        <option value="" data-default="true">All statuses</option>
                        <option value="new">New</option>
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Email Status</td>
                <td>
                    <select class="form-control dt_filter" data-title="Email status" name="email_status" data-type="select" id="email_statuses">
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
                    <select class="form-control dt_filter" data-title="OnLine/OffLine" name="online"  data-type="select" id="online">
                        <option value="" data-default="true">All</option>
                        <option value="1">Online</option>
                        <option value="0">Offline</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Registered</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter" type="text" name="reg_date_from" data-title="Registered from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" name="reg_date_to" data-title="Registered to" placeholder="To">
					</div>
                </td>
            </tr>
            <tr>
                <td>Last activity</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter" type="text" name="activity_date_from" data-title="Activity from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" name="activity_date_to" data-title="Activity to" placeholder="To">
					</div>
                </td>
            </tr>
            <?php if(!isset($filter_country)){?>
            <tr>
                <td>Country</td>
                <td>
                    <select class="form-control dt_filter" data-title="Country" name="country">
                        <option value="" data-default="true">Select Country</option>
                        <?php if(!empty($countries)){?>
                            <?php foreach($countries as $country){?>
                                <option value="<?php echo $country['id']?>"><?php echo $country['country']?></option>
                            <?php }?>
                        <?php } else{?>
                            <option value="">Country not found</option>
                        <?php }?>
                    </select>
                </td>
            </tr>
            <?php }?>
        </table>
        <div class="wr-filter-list clearfix mt-10 "></div>
    </div>
    <div class="btn-display">
        <div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
        <span>&laquo;</span>
    </div>
    <div class="wr-hidden" style="display: none;"></div>
</div>
<script>
$(document).ready(function(){
    $('input[name="resend_date_from"],input[name="resend_date_to"],input[name="reg_date_from"],input[name="reg_date_to"],input[name="activity_date_from"],input[name="activity_date_to"]').datepicker();
})
</script>
