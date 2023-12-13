<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table>
            <tr>
                <td>Speakers</td>
                <td>
                    <select id="js-filter-by-speakers"
                            class="form-control dt_filter"
                            name="speakers"
                            data-title="speakers"
                            data-type="select"
                            multiple>
                        <?php foreach ($eventSpeakers as $speaker) { ?>
                            <option value="<?php echo $speaker['id']; ?>"><?php echo $speaker['name']; ?></option>
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

<script>
    $('#js-filter-by-speakers').select2({
        width: '215px',
        multiple: true,
        placeholder: "Select speakers"
    });
</script>
