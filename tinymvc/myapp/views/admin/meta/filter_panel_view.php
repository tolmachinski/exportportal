<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
		<table>
			<tr>
				<td>Languages</td>
				<td>
					<select data-title="Language" class="dt_filter pull-left w-100pr" name="id_lang">
						<option data-categories="" data-default="true"  value="0">All</option>
						<?php foreach($translations as $lang){?>
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
