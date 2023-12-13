<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
        <table>
			<tr>
				<td>EN update date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="English text update from" name="base_update_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="English text update to" name="base_update_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Category</td>
				<td>
					<select class="dt_filter" data-title="Category" name="category">
						<option data-default="true" value="">All categories</option>
						<?php foreach ($categories as list($category, $categoryLabel)) { ?>
							<option value="<?php echo cleanOutput((string) $category); ?>"><?php echo cleanOutput($categoryLabel); ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Translated in</td>
				<td>
					<select class="dt_filter" data-title="Translated in" name="lang">
						<option data-default="true" value="">All languages</option>
						<?php foreach ($languages as $lang) { ?>
							<option value="<?php echo $lang['lang_iso2']; ?>"><?php echo $lang['lang_name']; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Not translated in</td>
				<td>
					<select class="dt_filter" data-title="Not translated in" name="not_lang">
						<option data-default="true" value="">All languages</option>
						<?php foreach ($languages as $lang) { ?>
							<option value="<?php echo $lang['lang_iso2']; ?>"><?php echo $lang['lang_name']; ?></option>
						<?php } ?>
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
        $(".filter-admin-panel").find("input[name^=base_update_]" ).datepicker();
    })
</script>
