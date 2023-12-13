<script type="text/javascript">

    $(function () {
        window.editFields = ['description_text', 'export_text', 'import_text', 'destination_text', 'origin_text', 'image'];
        window.dtExportImportStatistic = $('#dtExportImportStatistic').dataTable({
            sDom: '<"top"lpf>rt<"bottom"ip><"clear">',
            bProcessing: true,
            bServerSide: true,
            sAjaxSource: "<?php echo __SITE_URL?>library_country_statistic/ajax_export_import_info",
            sServerMethod: "POST",
            aoColumnDefs: [
                {sClass: "w-200 country relative-b tac vam", aTargets: ['country'], mData: "country"},
                {sClass: "w-100 tac vam image relative-b pr-20", aTargets: ['image'], mData: "image", bSortable: false},
                {sClass: "w-300 description_text relative-b pr-20", aTargets: ['description_text'], mData: "description_text", bSortable: false},
                {sClass: "w-300 export_text relative-b pr-20", aTargets: ['export_text'], mData: "export_text", bSortable: false},
                {sClass: "w-300 import_text relative-b pr-20", aTargets: ['import_text'], mData: "import_text", bSortable: false},
                {sClass: "w-300 destination_text relative-b pr-20", aTargets: ['destination_text'], mData: "destination_text", bSortable: false},
                {sClass: "w-300 origin_text relative-b pr-20", aTargets: ['origin_text'], mData: "origin_text", bSortable: false}
            ],
            sorting: [[0, "asc"]],
            sPaginationType: "full_numbers",
            rowCallback: function(row, data, index) {
                for (var key in data) {
                    var $cell = $('.' + key , row);
                    if (data.hasOwnProperty(key) && data[key] === '-') {
                        $cell.addClass('tac vam').text('Default text');
                    }

                    if (window.editFields.indexOf(key) !== -1) {
                        $cell.append('<a href="<?php echo __SITE_URL; ?>library_country_statistic/ajax_edit_text_modal?type=' + key + '&id=' + data['id_item'] + '" data-title="Edit" title="Edit" class="ep-icon ep-icon_pencil export-import-text-edit fancybox.ajax fancyboxValidateModal"></a>');
                    }
                }
            },
            fnDrawCallback: function (oSettings) {

            }
        });
    });

</script>

<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span>Country statistic</span>
            <a href="<?php echo __SITE_URL; ?>library_country_statistic/ajax_edit_templates_modal" data-title="Edit templates" class="fancyboxValidateModalDT fancybox.ajax btn btn-primary pull-right">Default templates</a>
        </div>

        <table class="data table-striped table-bordered w-100pr" id="dtExportImportStatistic" cellspacing="0" cellpadding="0">
            <thead>
            <tr>
                <th class="country">Country</th>
                <th class="image">Image</th>
                <th class="description_text">Description</th>
                <th class="export_text">Export text</th>
                <th class="import_text">Import text</th>
                <th class="destination_text">Destination text</th>
                <th class="origin_text">Origin text</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
