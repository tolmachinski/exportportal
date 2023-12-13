<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>

		<table>
			<tr>
				<td>Created</td>
				<td>
				 	<div class="input-group">
                        <input class="form-control dt_filter" type="text" data-title="Created from" name="start_from"  placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Created to" name="start_to" placeholder="To">
					</div>
				</td>
			</tr>

			<tr>
				<td>Status</td>
                <td>
                    <select class="dt_filter" data-title="Status" name="status_filter">
                        <option value="">All</option>
                        <option value="init">Init</option>
                        <option value="in_progress">In Progress</option>
                        <option value="declined">Declined</option>
                        <option value="processed">Processed</option>
                    </select>
                </td>
			</tr>

			<tr>
				<td>Search</td>
				<td>
					<input class="keywords dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" id="keywords" placeholder="Keywords">
				</td>
			</tr>

			<tr>
				<td>Refund amount</td>
				<td>
				 	<div class="input-group">
                        <input class="form-control dt_filter" type="text" data-title="Refund amount from" name="refund_amount_from"  placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Refund amount to" name="refund_amount_to" placeholder="To">
					</div>
				</td>
            </tr>

           <tr>
                <td>OnLine/OffLine</td>
                <td>
                    <select class="form-control dt_filter" data-title="OnLine/OffLine" name="online"  data-type="select" id="online">
                        <option value="" data-defaul-value="true">All</option>
                        <option value="1">Online</option>
                        <option value="0">Offline</option>
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

<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/jquery-timepicker-addon-1-6-3/js/jquery-ui-timepicker-addon.min.js"></script>
<script>
    $(document).ready(function(){
        $( 'input[name="start_from"], input[name="start_to"]' ).datepicker();
    })
</script>
