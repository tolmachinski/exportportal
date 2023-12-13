<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table>
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
                <td>Title</td>
                <td>
                    <input class="dt_filter" type="text" data-title="Title" name="title" placeholder="Title">
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
