<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table>
            <tr>
                <td>Category</td>
                <td class="select_category">
					<select class="categ1 dt_filter" data-title="Categories" level="1" name="parent">
						<option data-categories="" data-default="true" value="0">All</option>
						<?php foreach($categories as $category){?>
							<option data-categories="<?php echo $category['category_id'];?>" value="<?php echo $category['category_id'];?>">
								<?php if($category['id_article']){?>*<?php }?>
								<?php echo capitalWord($category['name']); ?>
							</option>
						<?php } ?>
					</select>
				</td>
            </tr>

            <tr>
                <td>Visible</td>
                <td>
                    <div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="visible" data-title="Visible" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="visible" data-title="Visible" data-value-text="Yes" value="1">
							<span class="input-group__desc">Yes</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="visible" data-title="Visible" data-value-text="No" value="0">
							<span class="input-group__desc">No</span>
						</label>
					</div>
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
