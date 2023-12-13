<table class="main-data-table dataTable mt-15">
    <?php if(!empty($timeline)) { ?>
        <?php foreach($timeline as $timeframe) { ?>
            <tr>
                <td>
                    <strong>
                        <?php echo cleanOutput($timeframe['user']); ?>
                    </strong>
                </td>
                <td class="w-150 txt-gray">
                    <?php echo getDateFormat($timeframe['date'], 'Y-m-d H:i:s'); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="grid-text">
                        <div class="grid-text__item">
                            <?php echo cleanOutput($timeframe['message']); ?>
                        </div>
                    </div>
                </td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr>
            <td colspan="2">There are no messages for now.</td>
        </tr>
    <?php } ?>
</table>