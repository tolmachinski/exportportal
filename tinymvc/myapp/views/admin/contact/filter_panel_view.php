<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table>
            <tr>
				<td>USER ID</td>
				<td>
					<input type="text" name="user" class="dt_filter" data-title="User ID" placeholder="ID" value="<?php echo $activeFilters['user'] ?? null;?>">
				</td>
			</tr>
            <tr>
                <td>Status</td>
				<td>

					<label for="all-statuses" class="lh-20"><input class="dt_filter" name="online" value="" type="radio" data-title="Status" data-value-text="" id="all-statuses"/> All</label><br>
					<label for="online-status" class="lh-20"><input class="dt_filter" name="online" value="1" type="radio" data-title="Status" data-value-text="Online" id="online-status"/> Online</label><br>
					<label for="offline-status" class="lh-20"><input class="dt_filter" name="online" value="0" type="radio" data-title="Status" data-value-text="Offline" id="offline-status"/> Offline</label>
				</td>
            </tr>
			<tr>
				<td>Date from</td>
				<td>
					<input type="text" name="date_from" class="dt_filter date-picker" value=""  data-title="Date from" placeholder="From" readonly>
				</td>
			</tr>
			<tr>
				<td>Date to</td>
				<td>
					<input type="text" name="date_to" class="dt_filter date-picker" value="" data-title="Date to" placeholder="To" readonly>
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
$(document).ready(function(){
	$( ".date-picker" ).datepicker();
})
</script>
