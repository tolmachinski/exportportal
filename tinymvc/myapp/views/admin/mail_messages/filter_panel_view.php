<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table>
            <tr>
                <td>Sender</td>
                <td>
                    <input class="dt_filter" type="text" data-title="Sender" name="sender" maxlength="256" placeholder="Sender email">
                </td>
            </tr>
            <tr>
                <td>Recipient</td>
                <td>
                    <input class="dt_filter" type="text" data-title="Recipient" name="recipient" maxlength="256" placeholder="Recipient email">
                </td>
            </tr>
            <tr>
                <td>Is sent</td>
                <td>
                    <select class="dt_filter" data-title="Is sent" name="is_sent" data-type="select">
                        <option value="" data-default="true">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Sent date</td>
                <td>
                    <div class="input-group">
						<input class="dt_filter date-picker" type="text" data-title="Sent date from" name="sent_date_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="dt_filter date-picker" type="text" data-title="Sent date to" name="sent_date_to" placeholder="To" readonly>
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
    $(document).ready(function () {
        $('.date-picker').datepicker();
    });
</script>
