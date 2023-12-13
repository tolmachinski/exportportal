<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table>
            <tr>
				<td>Search</td>
				<td>
					<input type="text" name="search" class="dt_filter pull-left" value=""  data-title="Search" placeholder="Keywords">
					<a class="dt-filter-apply dt-filter-apply-buttons m-0">>></a>
				</td>
			</tr>
			<tr>
                <td>Type</td>
				<td>
					<select class="form-control dt_filter w-175" data-title="Type" name="type" data-type="select">
						<option value="">All types</option>
						<?php foreach($types as $type){?>
							<option value="<?php echo $type['id_type']?>"><?php echo $type['type']?></option>
						<?php }?>
					</select>
				</td>
            </tr>
			<tr>
                <td>Theme</td>
				<td>
					<select class="form-control dt_filter w-175" data-title="Type" name="theme" data-type="select">
						<option value="">All themes</option>
						<?php foreach($themes as $theme){?>
							<option value="<?php echo $theme['id_theme']?>"><?php echo $theme['theme']?></option>
						<?php }?>
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
