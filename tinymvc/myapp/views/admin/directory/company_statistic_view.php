<div class="wr-form-content w-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr mt-15 mb-15 vam-table">
        <tbody>
            <tr>
                <td class="w-150">
                    <span class="ep-icon ep-icon_photo txt-blue pull-left"></span> Pictures
                </td>
                <td><?php echo (int)$company_statistic['photos_nr'];?></td>
            </tr>
            <tr>
                <td>
                    <span class="ep-icon ep-icon_video txt-blue pull-left"></span> Videos
                </td>
                <td><?php echo (int)$company_statistic['videos_nr'];?></td>
            </tr>
            <tr>
                <td>
                    <span class="ep-icon ep-icon_news txt-blue pull-left"></span> News
                </td>
                <td><?php echo (int)$company_statistic['news_nr'];?></td>
            </tr>
            <tr>
                <td>
                    <span class="ep-icon ep-icon_branches txt-blue pull-left"></span> Branches
                </td>
                <td><?php echo (int)$company_statistic['branches_nr'];?></td>
            </tr>
            <tr>
                <td>
                    <span class="ep-icon ep-icon_item txt-blue pull-left"></span> Items
                </td>
                <td><?php echo (int)$company_statistic['items_nr'];?></td>
            </tr>
        </tbody>
    </table>
</div>
