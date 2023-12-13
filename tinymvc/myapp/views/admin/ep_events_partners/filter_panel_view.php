<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table>
            <tr>
                <td>Partners</td>
                <td>
                    <select id="js-filter-by-partners"
                            class="form-control dt_filter"
                            name="partners"
                            data-title="Partners"
                            data-type="select"
                            multiple>
                        <?php foreach ($eventPartners as $partner) { ?>
                            <option value="<?php echo $partner['id']; ?>"><?php echo $partner['name']; ?></option>
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
    $('#js-filter-by-partners').select2({
        width: '215px',
        multiple: true,
        placeholder: "Select partners"
    });
</script>
