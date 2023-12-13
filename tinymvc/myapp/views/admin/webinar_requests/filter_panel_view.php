<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table>
            <tr>
                <td>Requested Date</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter date-picker" type="text" name="requested_from" data-title="Requested from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter date-picker" type="text" name="requested_to" data-title="Requested to" placeholder="To">
					</div>
                </td>
            </tr>
            <tr>
                <td>Email</td>
                <td>
                    <input class="dt_filter" type="text" data-title="Email" name="email" placeholder="Email">
                </td>
            </tr>

            <tr>
                <td>Status</td>
				<td>
					<select class="form-control dt_filter" data-title="Status" name="status" data-type="select" id="status">
						<option value="" data-default="true">All statuses</option>
                        <option value="new">New</option>
                        <option value="attended">Attended</option>
                        <option value="not_attended">Not attended</option>
                    </select>
                </td>
            </tr>

			<tr>
				<td>Registered:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="registered" data-title="Registered" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="registered" data-title="Registered" data-value-text="No" value="0">
							No
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="registered" data-title="Registered" data-value-text="Yes" value="1">
							Yes
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
    $(function(){
        $('.date-picker').datepicker();
    });
</script>
