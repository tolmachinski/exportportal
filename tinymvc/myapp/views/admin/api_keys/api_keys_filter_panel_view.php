<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>
		<table>
		    <tr>
                <td>Registered</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Add date from" name="start_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Add date to" name="start_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Search</td>
				<td>
					<input class="dt_filter pull-left" type="text" name="search" value=""  data-title="Keywords" placeholder="Keywords">
					<a class="dt-filter-apply dt-filter-apply-buttons">>></a>
				</td>
			</tr>
			<tr>
				<td>State</td>
				<td>
					<select class="form-control dt_filter" data-title="Checked/Unchecked" name="moderated"  data-type="select">
						<option value="" data-default="true">All</option>
						<option value="1">Moderated</option>
						<option value="0">Not Moderated</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Status</td>
				<td>
					<select class="form-control dt_filter" data-title="Enabled/Disabled" name="enable"  data-type="select">
						<option value="" data-default="true">All</option>
						<option value="1" data-value-text="Enabled">Enabled</option>
						<option value="0" data-value-text="Disabled">Disabled</option>
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
	$( ".filter-admin-panel" ).find('input[name^=start_]').datepicker();
})
</script>
