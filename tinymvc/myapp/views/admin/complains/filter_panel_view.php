<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table>
            <tr>
				<td>Search</td>
				<td>
					<input type="text" name="search" class="form-control dt_filter pull-left" value=""  data-title="Search" placeholder="Keywords">
					<a class="dt-filter-apply dt-filter-apply-buttons">>></a>
				</td>
			</tr>
            <tr>
                <td>Status</td>
				<td>
					<select class="form-control dt_filter" data-title="Status" name="status" data-type="select">
						<option value="" data-default="true">All statuses</option>
						<option value="new" selected="selected">New</option>
						<option value="in_process">In process</option>
						<option value="confirmed">Confirmed</option>
						<option value="declined">Declined</option>
					</select>
				</td>
            </tr>
			<tr>
                <td>Type</td>
				<td>
					<select class="form-control dt_filter" data-title="Type" name="type" data-type="select">
						<option value="" data-default="true">All types</option>
						<?php foreach($types as $type){?>
							<option value="<?php echo $type['id_type']?>"><?php echo $type['type']?></option>
						<?php }?>
					</select>
				</td>
            </tr>
			<tr>
				<td>Date from</td>
				<td>
					<input class="dt_filter date-picker" type="text" name="complains_date_from" value=""  data-title="Date from" placeholder="From" readonly>
				</td>
			</tr>
			<tr>
				<td>Date to</td>
				<td>
					<input class="dt_filter date-picker" type="text" name="complains_date_to" value="" data-title="Date to" placeholder="To" readonly>
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
