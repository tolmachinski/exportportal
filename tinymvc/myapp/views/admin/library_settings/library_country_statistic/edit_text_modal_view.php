<div class="wr-modal-b">
    <form class="modal-b__form" id="export-import-text-form">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="type" value="<?php echo $type; ?>">
        <div class="modal-b__content w-700">
            <div class="mb-20 clearfix">
                <label class="modal-b__label">Enter the text</label>
                <textarea name="text" rows="10"><?php echo $text; ?></textarea>
            </div>

            <div class="fs-12">
                <strong>Available variables:</strong>
                <ul>
                    <li>[country_name] - current country name</li>
                    <li>[year] - statistic year</li>
                    <li>[most_export_product_name] - The name of the most exported product, from this country to all others</li>
                    <li>[most_export_product_amount] - The amount of the most exported product, from this country to all others</li>
                    <li>[most_import_product_name] - The name of the most imported product, from all countries to current country</li>
                    <li>[most_import_product_amount] - The amount of the most imported product, from all countries to current country</li>
                    <li>[top_export_countries] - Top countries where current country exports products</li>
                    <li>[top_import_countries] - Top countries where current country imports products</li>
                    <li>[top_export_products] - Top products exported from current country</li>
                    <li>[top_import_products] - Top products imported into current country</li>
                </ul>
            </div>
        </div>

        <div class="modal-b__btns clearfix">
            <button class="btn btn-primary pull-right" type="submit"><i class="ep-icon ep-icon_ok"></i> Save</button>
        </div>
    </form>
</div>

<script>
    $('#export-import-text-form').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            type: 'post',
            url: '<?php echo __SITE_URL; ?>library_country_statistic/ajax_save_text',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.mess_type === 'success') {
                    closeFancyBox();
                    window.dtExportImportStatistic && window.dtExportImportStatistic.fnDraw(false);
                } else {
                    systemMessages(response.message, 'message-error');
                }
            }
        });
    });
</script>
