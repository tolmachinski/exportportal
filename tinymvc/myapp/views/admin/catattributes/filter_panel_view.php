<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel" >
        <div class="title-b">Filter panel</div>
        <table class="w-100pr">
            <tr>
                <td>Category</td>
                <td>
                    <select class="form-control" data-level="0" data-title="Category" name="category_parent">
                        <option value="">Select category</option>
                        <?php if(!empty($categories)){?>
                            <?php foreach($categories as $category){?>
                                <option value="<?php echo $category['category_id'];?>"><?php echo $category['name'];?></option>
                            <?php }?>
                        <?php }?>
                    </select>
                    <div class="sub-categories-wr"></div>
                    <div class="category_filter_wr"></div>
                </td>
            </tr>
        </table>
        <div class="wr-filter-list clearfix mt-10 "></div>
    </div>
    <div class="btn-display">
        <div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
        <span>&laquo;</span>
    </div>
    <div class="wr-hidden" style="display: none;"></div>
</div>