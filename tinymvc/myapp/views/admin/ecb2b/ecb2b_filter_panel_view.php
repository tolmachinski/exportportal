<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>
		<table>
			<tr>
				<td>Search</td>
				<td>
					<input class="dt_filter pull-left" type="text" name="search" value="" data-title="Keywords" placeholder="Keywords">
				</td>
			</tr>
            <tr>
                <td>Request date</td>
                <td>
                    <div class="input-group">
                        <input class="form-control dt_filter date-picker" type="text" data-title="Add date from" name="request_from" placeholder="From" readonly>
                        <div class="input-group-addon">-</div>
                        <input class="form-control dt_filter date-picker" type="text" data-title="Add date to" name="request_to" placeholder="To" readonly>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Processed date</td>
                <td>
                    <div class="input-group">
                        <input class="form-control dt_filter date-picker" type="text" data-title="Add date from" name="processed_from" placeholder="From" readonly>
                        <div class="input-group-addon">-</div>
                        <input class="form-control dt_filter date-picker" type="text" data-title="Add date to" name="processed_to" placeholder="To" readonly>
                    </div>
                </td>
            </tr>
			<tr>
				<td>Type</td>
				<td>
					<select class="form-control dt_filter" name="type" data-type="select">
						<option value="" data-default="true">All</option>
						<option value="short deck">Short deck</option>
						<option value="business plan">Business plan</option>
					</select>
				</td>
			</tr>
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
	$('.filter-admin-panel .date-picker').datepicker();
})
</script>
