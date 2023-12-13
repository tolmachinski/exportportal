<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table>
            <tr>
                <td>Start Date</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter date-picker" type="text" name="start_from" data-title="Start from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter date-picker" type="text" name="start_to" data-title="Start to" placeholder="To">
					</div>
                </td>
            </tr>
        </table>
        <div class="wr-filter-list clearfix mt-10 "></div>
        <?php if(isset($webinar) && !empty($webinar)){?>
            <a data-title="Webinar" data-name="id" class="dt_filter display-n" data-value-text="<?php echo $webinar['title'];?>" data-value="<?php echo $webinar['id'];?>"></a>
        <?php }?>
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
