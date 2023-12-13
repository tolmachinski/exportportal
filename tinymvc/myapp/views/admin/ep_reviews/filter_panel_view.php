<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table class="w-100pr">
            <tr>
                <td>User ID</td>
                <td>
                    <input type="text" class="form-control dt_filter" placeholder="ID" data-title="User ID" name="user">
                </td>
            </tr>
            <tr>
                <td>User status</td>
                <td>
                    <select class="form-control dt_filter" data-title="User status" name="user_status" data-type="select">
                        <option value="" data-default="true">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="active">Activated</option>
                        <option value="restricted">Restricted</option>
                        <option value="blocked">Blocked</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Moderated</td>
                <td>
                    <select class="form-control dt_filter" data-title="Moderated" name="moderated" data-type="select">
                        <option value="" data-default="true">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Published</td>
                <td>
                    <select class="form-control dt_filter" data-title="Published" name="published" data-type="select">
                        <option value="" data-default="true">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Write date</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter date-picker" type="text" name="added_from" data-title="Writed from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter date-picker" type="text" name="added_to" data-title="Writed to" placeholder="To">
					</div>
                </td>
            </tr>
            <tr>
                <td>Publish date</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter date-picker" type="text" name="published_from" data-title="Published from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter date-picker" type="text" name="published_to" data-title="Published to" placeholder="To">
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
<script>
    $(function(){
        $('.date-picker').datepicker();
    });
</script>
