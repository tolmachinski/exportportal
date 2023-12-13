<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
        <table>
            <tr>
				<td>Search</td>
				<td>
					<input class="w-100pr dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" placeholder="Keywords">
				</td>
			</tr>
            <tr>
				<td>Date of creation</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter filter-datetime" type="text" data-title="Created from" id="filter-created-from" name="created_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter filter-datetime" type="text" data-title="Created to" id="filter-created-to" name="created_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
            <tr>
				<td>Date of update</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter filter-datetime" type="text" data-title="Updated from" id="filter-updated-from" name="updated_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter filter-datetime" type="text" data-title="Updated to" id="filter-updated-to" name="updated_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
            <tr>
                <td>Account activated</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter filter-datetime" type="text" name="activated_from" data-title="Activated from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter filter-datetime" type="text" name="activated_to" data-title="Activated to" placeholder="To">
					</div>
                </td>
            </tr>
            <tr>
                <td>Status</td>
                <td>
                    <select class="form-control dt_filter" data-title="User status" name="status" data-type="select">
                        <option value="" data-default="true">All statuses</option>
                        <option value="new">New</option>
                        <option value="pending">Pending</option>
                        <option value="active">Activated</option>
                        <option value="restricted">Restricted</option>
                        <option value="blocked">Blocked</option>
                        <option value="deleted">Deleted</option>
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
<script type="application/javascript">
    $(function(){
        $(".filter-admin-panel").find(".filter-datetime" ).datepicker();
    });
</script>
