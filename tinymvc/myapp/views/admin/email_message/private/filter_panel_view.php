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
                <td>Start date</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter date_interval" type="text" data-title="Start date from" name="date_start" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter date_interval" type="text" data-title="Start date to" name="date_end" placeholder="To" readonly>
					</div>
                </td>
            </tr>
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
                <td>Status record</td>
                <td>
                    <div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="status_record" data-title="Status" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="status_record" data-title="Status" data-value-text="Resovled" value="resovled">
							<span class="input-group__desc">Resovled</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="status_record" data-title="Status" data-value-text="Waiting" value="waiting">
							<span class="input-group__desc">Waiting</span>
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
    $(document).ready(function () { $(".date_interval").datepicker(); });
</script>
