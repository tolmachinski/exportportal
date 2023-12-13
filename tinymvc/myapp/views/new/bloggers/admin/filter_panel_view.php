<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
        <table>
			<tr>
				<td>Create date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Create date from" name="created_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Create date to" name="created_to" placeholder="To" readonly>
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
				<td>Category</td>
				<td>
					<select class="dt_filter" data-title="Category" name="category">
						<option data-default="true" value="">All categories</option>
						<?php foreach($categories as $category){ ?>
							<option value="<?php echo $category['id_category'];?>"><?php echo $category['name'];?></option>
						<?php } ?>
                        <option value="0">None</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Country</td>
				<td>
					<select class="dt_filter" data-title="Country" name="country">
						<option data-default="true" value="">All countries</option>
						<option value="0">No country</option>
						<?php foreach($countries as $country){ ?>
							<option value="<?php echo $country['id'];?>"><?php echo $country['country'];?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Language</td>
				<td>
					<select class="dt_filter" data-title="Language" name="lang">
						<option data-default="true" value="">All languages</option>
						<?php foreach($languages as $lang){ ?>
							<option value="<?php echo $lang['id_lang'];?>"><?php echo $lang['lang_name'];?></option>
						<?php } ?>
						<option data-default="true" value="0">Other</option>
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
	$(".filter-admin-panel").find("input[name^=created_]" ).datepicker();
})
</script>
