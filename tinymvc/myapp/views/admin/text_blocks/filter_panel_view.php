<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel w-350">
        <div class="title-b">Filter panel</div>
        <table>
			<tr>
				<td>Short key</td>
				<td>
					<input class="dt_filter" type="text" data-title="Short key" name="short_name" placeholder="Short key">
				</td>
			</tr>
			<tr>
				<td>Search</td>
				<td>
					<input class="dt_filter" type="text" data-title="Search for" name="keywords" placeholder="Search for ...">
				</td>
			</tr>
			<tr>
				<td>EN update date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="English text update from" name="en_updated_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="English text update to" name="en_updated_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Translated in</td>
				<td>
					<select class="dt_filter" data-title="Translated in" name="translated_in">
						<option data-default="true" value="">All languages</option>
						<?php foreach($languages as $lang){ ?>
							<option value="<?php echo $lang['lang_iso2'];?>"><?php echo $lang['lang_name'];?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Not translated in</td>
				<td>
					<select class="dt_filter" data-title="Not translated in" name="not_translated_in">
						<option data-default="true" value="">All languages</option>
						<?php foreach($languages as $lang){ ?>
							<option value="<?php echo $lang['lang_iso2'];?>"><?php echo $lang['lang_name'];?></option>
						<?php } ?>
					</select>
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
$(document).ready(function() {
	$(".filter-admin-panel").find("input[name^=en_updated_]" ).datepicker();
});
</script>
