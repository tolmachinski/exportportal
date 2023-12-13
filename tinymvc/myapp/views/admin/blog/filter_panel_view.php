<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
    <table>
			<tr>
				<td>Language</td>
				<td>
					<select class="dt_filter" data-title="Language" name="blog_lang">
						<option data-default="true" value="">All languages</option>
						<?php foreach($languages as $language){?>
							<option value="<?php echo $language['lang_iso2'];?>"><?php echo $language['lang_name'];?></option>
						<?php }?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Country</td>
				<td>
					<select class="dt_filter" data-title="Country" name="country">
						<option data-default="true" value="">All countries</option>
						<option value="0">No country</option>
						<?php foreach($blog_countries as $country){?>
							<option value="<?php echo $country['id_country'];?>"><?php echo $country['country_name'];?></option>
						<?php }?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Category</td>
				<td>
					<select class="dt_filter" data-title="Category" name="category">
						<option data-default="true" value="">All categories</option>
						<?php foreach($blog_categories as $item_category){?>
							<option value="<?php echo $item_category['id_category'];?>"><?php echo $item_category['name'];?></option>
						<?php }?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Create date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Create date from" name="start_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Create date to" name="start_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Publish on</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Publish date from" name="publish_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Publish date to" name="publish_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Search by</td>
				<td>
					<input class="dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" placeholder="Keywords">
				</td>
			</tr>
			<tr>
				<td>Visible</td>
				<td>
					<select class="dt_filter" data-title="Visible" name="visibility">
						<option data-default="true" value="">All</option>
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Published</td>
				<td>
					<select class="dt_filter" data-title="Published" name="published">
						<option data-default="true" value="">All</option>
						<option value="1">Yes</option>
						<option value="0">No</option>
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

<script>
$(document).ready(function(){
	$(".filter-admin-panel").find("input[name^=start_]" ).datepicker();
	$(".filter-admin-panel").find("input[name^=publish_]" ).datepicker();
})
</script>
