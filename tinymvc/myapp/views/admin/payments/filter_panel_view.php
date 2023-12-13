<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
        <table>
			<tr>
				<td>Translated in</td>
				<td>
					<select class="dt_filter" data-title="Translated in" name="lang">
						<option data-default="true" value="">All languages</option>
						<?php foreach($languages as $lang){ ?>
							<option value="<?php echo $lang['id_lang'];?>"><?php echo $lang['lang_name'];?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Not translated in</td>
				<td>
					<select class="dt_filter" data-title="Not translated in" name="not_lang">
						<option data-default="true" value="">All languages</option>
						<?php foreach($languages as $lang){ ?>
							<option value="<?php echo $lang['id_lang'];?>"><?php echo $lang['lang_name'];?></option>
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