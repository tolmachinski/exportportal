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
            <?php if($isAdmin){?>
            <tr id="select-category">
                <td>Category of support</td>
                <td>
                    <select class="dt_filter" name="category" data-title="Category of support">
                        <?php if(!empty($list_category)){?>
                        <option value="">Select category</option>
                        <?php foreach($list_category as $category){?>
                        <option value="<?php echo $category['id_spcat']?>"><?php echo $category['category']?></option>
                        <?php }}else{?>
                        <option value="">Category not found</option>
                        <?php }?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Export Portal staff</td>
                <td class="ep-user-staff">
                    <select class="dt_filter" name="user_staff" data-title="Export Portal staff">
                        <?php if(!empty($list_ep_staff)){?>
                        <option value="">Select user</option>
                        <?php foreach($list_ep_staff as $staff){?>
                        <option value="<?php echo $staff['idu']?>"><?php echo $staff['fname']?> <?php echo $staff['lname']?> - <?php echo $staff['gr_name']?></option>
                        <?php }}else{?>
                        <option value="">User not found</option>
                        <?php }?>
                    </select>
                </td>
            </tr>
            <?php }?>
            <tr>
                <td>Email account</td>
                <td class="ep-user-staff">
                    <select class="dt_filter" name="email_account" data-title="Email account">
                        <?php if(!empty($list_email_account)){?>
                        <option value="">Select account</option>
                        <?php foreach($list_email_account as $email_account){?>
                        <option value="<?php echo $email_account['email_account']?>"><?php echo $email_account['email_account']?></option>
                        <?php }}else{?>
                        <option value="">Account not fount</option>
                        <?php }?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Start date</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter date_interval" type="text" data-title="Start date from" name="date_start" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter date_interval" type="text" data-title="Start date to" name="date_end" placeholder="To" readonly>
					</div>
                </td>
            </tr>
            <?php if($isAdmin){?>
            <tr>
                <td>Status record</td>
                <td>
                    <div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="status_record" data-title="Status" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="status_record" data-title="Status" data-value-text="New" value="0">
							<span class="input-group__desc">New</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="status_record" data-title="Status" data-value-text="Resovled" value="1">
							<span class="input-group__desc">Resovled</span>
						</label>
					</div>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="status_record" data-title="Status" data-value-text="Not resolved" value="2">
							<span class="input-group__desc">Not resolved</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="status_record" data-title="Status" data-value-text="Waiting" value="3">
							<span class="input-group__desc">Waiting</span>
						</label>
					</div>
                </td>
            </tr>
            <?php }?>
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
    $(document).ready(function () {
        $(".date_interval").datepicker();

        $("#select-category").on('change', 'select[name="category"]', function(){
            var $this = $(this),
                $container = $('.ep-user-staff');

            $.ajax({
                type: "POST",
                url: "<?php echo __SITE_URL?>email_message/get_users_category",
                data: {id_category: $this.val()},
                success: function(html) {
                    requirementFilters.removeFilter('user_staff');
                    $container.find('select[name="user_staff"]').remove();
                    $container.html('<select class="w-310 dt_filter" name="user_staff" data-title="Export Portal staff"><option value="">Select user</option>' + html + '</select>');
                }
            });
        });
    });
</script>
