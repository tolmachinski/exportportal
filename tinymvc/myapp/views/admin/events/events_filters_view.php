<div class="wr-filter-admin-panel">

	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>

		<table>
			<tr>
				<td>By Categories</td>
				<td>
					<select class="dt_filter" data-title="Categories" name="category" id="category">
						<option data-default="true" value="">All categories</option>
						<?php foreach($categories as $category){?>
							<option value="<?php echo $category['id_category'];?>"><?php echo $category['title_category'];?></option>
						<?php }?>
					</select>
				</td>
			</tr>
			<tr>
				<td>By Status</td>
				<td>
					<select class="dt_filter" data-title="Event status" name="status_event" id="status_event">
						<option data-default="true" value="">All statuses</option>
						<option value="new">New</option>
						<option value="processing">Processing</option>
						<option value="sold">Sold</option>
						<option value="finished">Finished</option>
					</select>
				</td>
			</tr>

			<tr>
				<td>By Type</td>
				<td>
					<select class="dt_filter" data-title="Type event" name="type_event" id="type_event">
						<option data-default="true" value="">All types</option>
						<option value="public">Public</option>
						<option value="private">Private</option>
					</select>
				</td>
			</tr>

			<tr>
				<td>By Start date</td>
				<td>
					<div class="input-group date_event">
						<input class="form-control dt_filter start_from" type="text" data-title="Start date from" name="start_from" id="start_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter start_to" type="text" data-title="Start date to" name="start_to" id="start_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>

			<tr>
				<td>By Finish date</td>
				<td>
					<div class="input-group date_event">
						<input class="form-control dt_filter end_from" type="text" data-title="Finish date from" name="end_from" id="end_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter end_to" type="text" data-title="Finish date to" name="end_to" id="end_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>

			<tr>
				<td>Search by</td>
				<td>
					<input class="keywords dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" id="keywords" placeholder="Keywords">
				</td>
			</tr>

			<tr>
				<td>Moderated:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="moderated" data-title="Moderated" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="moderated" data-title="Moderated" data-value-text="No" value="0">
							<i class="ep-icon ep-icon_sheild-nok txt-red input-group__desc"></i>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="moderated" data-title="Moderated" data-value-text="Yes" value="1">
							<i class="ep-icon ep-icon_sheild-ok txt-green input-group__desc"></i>
						</label>
					</div>
				</td>
			</tr>

			<tr>
				<td>Featured:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="featured" data-title="Featured" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon" title="Unfeatured">
							<input class="dt_filter" type="radio" name="featured" data-title="Featured" data-value-text="No" value="0">
							<i class="ep-icon ep-icon_star-empty txt-blue input-group__desc"></i>
						</label>
						<label class="input-group-addon" title="Featured">
							<input class="dt_filter" type="radio" name="featured" data-title="Featured" data-value-text="Yes" value="1">
							<i class="ep-icon ep-icon_star txt-blue input-group__desc"></i>
						</label>
					</div>
				</td>
			</tr>

			<tr>
				<td>Visible:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="visible" data-title="Visible" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="visible" data-title="Visible" data-value-text="No" value="0">
							<i class="ep-icon ep-icon_invisible txt-blue input-group__desc"></i>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="visible" data-title="Visible" data-value-text="Yes" value="1">
							<i class="ep-icon ep-icon_visible txt-blue input-group__desc"></i>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<td>Locked:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="blocked" data-title="Locked" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="blocked" data-title="Locked" data-value-text="No" value="0">
							<i class="ep-icon ep-icon_locked txt-blue input-group__desc"></i>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="blocked" data-title="Locked" data-value-text="Yes" value="1">
							<i class="ep-icon ep-icon_unlocked txt-blue input-group__desc"></i>
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
$(document).ready(function(){
	$( ".start_from, .start_to, .end_from, .end_to" ).datepicker();
})
</script>
