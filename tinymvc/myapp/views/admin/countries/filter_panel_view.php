<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table>
            <tr>
                <td>Country</td>
                <td>
                    <input class="dt_filter" type="text" data-title="Country" name="country" placeholder="Country">
                </td>
            </tr>
            <tr>
                <td>Code</td>
                <td>
                    <input class="dt_filter" type="text" data-title="Country code" name="code" placeholder="Country code">
                </td>
            </tr>
            <tr>
                <td>Focus Countries</td>
                <td>
                    <select class="dt_filter" data-title="Focus Country" name="focus_country" data-type="select">
                        <option value="" data-default="true">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Has a special position</td>
                <td>
                    <select class="dt_filter" data-title="Has a special position" name="special_position" data-type="select">
                        <option value="" data-default="true">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Continent</td>
                <td>
                    <select class="dt_filter" data-title="Continent" name="continent" data-type="select">
                        <option value="" data-default="true">All</option>
                        <?php foreach ($continents as $continent) {?>
                            <option value="<?php echo $continent['id_continent'];?>" data-default="true"><?php echo $continent['name_continent'];?></option>
                        <?php }?>
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
