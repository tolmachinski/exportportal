<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel w-350">
        <div class="title-b">Filter panel</div>
        <table>
            <tr>
				<td>From group</td>
				<td>
					<select class="form-control dt_filter w-200" data-title="Group from" name="gr_from" data-type="select">
						<option data-default="true" value="">All Groups</option>
						<option value="0">Default</option>
						<?php foreach($groups as $group){?>
						<option value="<?php echo $group['idgroup']?>" <?php echo selected($group['idgroup'], $package_info['gr_from'])?>><?php echo $group['gr_name']?></option>
						<?php }?>
					</select>
				</td>
			</tr>
			<tr>
                <td>On group</td>
				<td>
					<select class="form-control dt_filter w-200" data-title="On group" name="gr_to" data-type="select">
						<option data-default="true" value="">All Groups</option>
						<option value="0">Default</option>
						<?php foreach($groups as $group){?>
						<option value="<?php echo $group['idgroup']?>" <?php echo selected($group['idgroup'], $package_info['gr_from'])?>><?php echo $group['gr_name']?></option>
						<?php }?>
					</select>
				</td>
            </tr>
			<tr>
                <td>Period</td>
				<td>
					<select class="form-control dt_filter w-200" data-title="Period" name="period" data-type="select">
						<option data-default="true" value="">All Periods</option>
						<?php foreach($periods as $period){?>
                            <option value="<?php echo $period['id']?>" <?php echo selected($period['id'], $package_info['period'])?>><?php echo $period['full']?></option>
                        <?php }?>
					</select>
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
			<tr>
				<td>Default</td>
				<td>
					<select class="dt_filter" data-title="Default" name="default">
							<option data-default="true" value="">All</option>
							<option value="0">No</option>
							<option value="1">Yes</option>
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
