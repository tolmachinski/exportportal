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
                <td>Status</td>
                <td>
                    <select class="form-control dt_filter" data-title="Status" name="status" data-type="select" id="statuses">
                        <option value="" data-default="true">All statuses</option>
                        <option value="new">New</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="declined">Declined</option>
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
