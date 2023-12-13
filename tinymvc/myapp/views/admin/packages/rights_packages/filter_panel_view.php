<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table>
            <tr>
				<td>Group for</td>
				<td>
					<select class="form-control dt_filter w-200" data-title="Group for" name="gr_from" data-type="select">
						<option value="">All Groups</option>
						<?php foreach($groups as $group){?>
						<option value="<?php echo $group['idgroup']?>"><?php echo $group['gr_name']?></option>
						<?php }?>
					</select>
				</td>
			</tr>
			<tr>
                <td>Rights</td>
				<td>
					<select class="form-control dt_filter w-200" data-title="Right" name="right" data-type="select">
						<option value="">All rights</option>
                        <?php foreach($bycat as $cat){ ?>
                            <optgroup label="<?php echo $cat['name_module']?>">
                            <?php foreach($cat['rights'] as $right){?>
                                <option title="<?php echo $right['r_descr']?>" value="<?php echo $right['idright']?>"><?php echo $right['r_name']?></option>
                            <?php } ?>
                            </optgroup>
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
