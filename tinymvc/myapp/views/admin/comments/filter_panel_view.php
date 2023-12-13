<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table>
            <!-- <tr>
                <td>ID Author [not id user]</td>
                <td>
                    <input class="dt_filter" type="text" data-title="ID AUTHOR" name="id_author" placeholder="ID">
                </td>
            </tr> -->
            <tr>
                <td>On</td>
                <td>
                    <select class="dt_filter" data-title="On" name="type" data-type="select">
                        <option value="" data-default="true">All</option>
                        <?php foreach ($comment_types as $comment_type) {?>
                            <option value="<?php echo $comment_type['id'];?>"><?php echo cleanOutput($comment_type['name']);?></option>
                        <?php }?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>State</td>
                <td>
                    <select class="dt_filter" data-title="State" name="state" data-type="select">
                        <option value="" data-default="true">All</option>
                        <?php foreach ($comment_states as $comment_state) {?>
                            <option value="<?php echo cleanOutput($comment_state);?>"><?php echo cleanOutput($comment_state);?></option>
                        <?php }?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Created Date</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter date-picker" type="text" name="created_from" data-title="Created from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter date-picker" type="text" name="created_to" data-title="Created to" placeholder="To">
					</div>
                </td>
            </tr>
            <tr>
                <td>Published Date</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter date-picker" type="text" name="published_from" data-title="Published from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter date-picker" type="text" name="published_to" data-title="Published to" placeholder="To">
					</div>
                </td>
            </tr>
            <tr>
                <td>ID Blogs</td>
                <td>
                    <input class="dt_filter" type="text" data-title="ID Blog" name="entities[id_blog]" placeholder="ID">
                </td>
            </tr>
            <tr>
                <td>ID News</td>
                <td>
                    <input class="dt_filter" type="text" data-title="ID News" name="entities[id_news]" placeholder="ID">
                </td>
            </tr>
            <tr>
                <td>ID Updates</td>
                <td>
                    <input class="dt_filter" type="text" data-title="ID Updates" name="entities[id_updates]" placeholder="ID">
                </td>
            </tr>
            <tr>
                <td>ID Trade News</td>
                <td>
                    <input class="dt_filter" type="text" data-title="ID Trade News" name="entities[id_trade_news]" placeholder="ID">
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
    $(function(){
        $('.date-picker').datepicker();
    });
</script>
