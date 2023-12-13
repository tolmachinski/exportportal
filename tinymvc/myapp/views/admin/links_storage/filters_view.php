<div class="wr-filter-admin-panel">

	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>

		<table>
			<tr>
				<td>By Country</td>
				<td>
					<select data-title="Country" name="country" class="w-230 dt_filter">
						<option data-default="true" value="">Select Country</option>
						<?php foreach($port_country as $conutry) { ?>
							<option value='<?php echo $conutry['id']?>'>
								<?php echo $conutry['country']?>
							</option>
						<?php } ?>
					</select>
				</td>
			</tr>

			<tr>
				<td>Search by</td>
				<td>
					<span >
						<input class="dt_filter w-230" type="text" data-title="Search for" name="keywords" maxlength="50" placeholder="Keywords">
					</span>
				</td>
			</tr>

			<tr>
				<td>Paid:</td>
				<td>
					<label class="pull-left pr-10">
						<input class="pull-left m-0 dt_filter" type="radio" name="paid" data-title="Paid" data-value-text="All" value="" checked="checked">
						<span class="pull-left ml-5">All</span>
					</label>
					<label class="pull-left pr-10">
						<input class="pull-left m-0 dt_filter"  type="radio" name="paid"data-title="Paid" data-value-text="No" value="0">
						<a class="pull-left ml-5 ep-icon ep-icon_remove txt-red"></a>
					</label>
					<label class="pull-left pr-10">
						<input class="pull-left m-0 dt_filter" type="radio" name="paid" data-title="Paid" data-value-text="Yes" value="1">
						<a class="pull-left ml-5 ep-icon ep-icon_ok txt-green"></a>
					</label>
				</td>
			</tr>
		</table>
		<div class="wr-filter-list mt-10 clearfix"></div>
	</div>

	<div class="btn-display">
		<div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
		<span>&laquo;</span>
	</div>

	<div class="wr-hidden"></div>
</div>
