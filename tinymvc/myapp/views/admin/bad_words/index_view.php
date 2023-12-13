<script type="text/javascript">

    $(function () {
        window.dtBadWords = $('#dtBadWords').dataTable({
            sDom: '<"top"lpf>rt<"bottom"ip><"clear">',
            bProcessing: true,
            bServerSide: true,
            sAjaxSource: "<?php echo __SITE_URL?>bad_words/ajax_bad_words",
            sServerMethod: "POST",
            aoColumnDefs: [
                {sClass: "w-100 tac", aTargets: ['dt_language'], mData: "dt_language", bSortable: true},
                {sClass: "", aTargets: ['dt_word'], mData: "dt_word", bSortable: true},
                {sClass: "tac w-50", aTargets: ['dt_actions'], mData: "dt_actions", bSortable: false}
            ],
            sorting: [[0, "asc"]],
            sPaginationType: "full_numbers",
            rowCallback: function(row, data, index) {
            },
            fnDrawCallback: function (oSettings) {

            }
        });
    });

</script>

<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span>Bad words</span>
            <a href="<?php echo __SITE_URL; ?>bad_words/bad_words_popup" data-title="Add bad words" class="fancyboxValidateModalDT fancybox.ajax btn btn-primary pull-right">Add bad words</a>
        </div>

        <table class="data table-striped table-bordered w-100pr" id="dtBadWords" cellspacing="0" cellpadding="0">
            <thead>
            <tr>
                <th class="dt_language">Language</th>
                <th class="dt_word">Word</th>
                <th class="dt_actions">Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>