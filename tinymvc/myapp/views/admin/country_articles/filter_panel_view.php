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
				<td>Country</td>
				<td>
					<select class="dt_filter" data-title="Country" name="country" >
						<option data-default="true" value="">All countries</option>
						<?php foreach($port_country as $country){?>
							<option value="<?php echo $country['id']?>"><?php echo $country['country']?></option>
						<?php }?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Type</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="type_blog" data-title="Type" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="type_blog" data-title="Type" data-value-text="Export" value="0">
							<span class="input-group__desc">Export</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="type_blog" data-title="Type" data-value-text="Import" value="1">
							<span class="input-group__desc">Import</span>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<td>Visible</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="visible_blog" data-title="Visible" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="visible_blog" data-title="Visible" data-value-text="Yes" value="1">
							<span class="input-group__desc">Yes</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="visible_blog" data-title="Visible" data-value-text="No" value="0">
							<span class="input-group__desc">No</span>
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
