<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr">
            <span>EP Events - speakers</span>
            <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="ep_events_speakers/popup_forms/add_speaker" data-table="dtSpeakers" data-title="Add Speaker">
            </a>
        </div>

        <?php views('admin/ep_events_speakers/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped table-bordered w-100pr" id="dtSpeakers" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th class="dt_id tac w-30 vam">#</th>
                    <th class="dt_image w-120 tac vam">Image</th>
                    <th class="dt_name tac vam">Name</th>
                    <th class="dt_actions tac vam w-90">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<?php views('admin/file_upload_scripts'); ?>

<script>
    var requirementFilters;
    var dtSpeakers;

    $(document).ready(function() {
        dtSpeakers = $('#dtSpeakers').dataTable({
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . 'ep_events_speakers/ajax_dt_administration'; ?>",
            "sServerMethod": "POST",
            "aoColumnDefs": [{
                    "sClass": "tac vam w-30",
                    "aTargets": ['dt_id'],
                    "mData": "dt_speaker_id"
                },
                {
                    "sClass": "tac vam",
                    "aTargets": ['dt_image'],
                    "mData": "dt_speaker_photo",
                    "bSortable": false
                },
                {
                    "sClass": "tac vam",
                    "aTargets": ['dt_name'],
                    "mData": "dt_speaker_name",
                    "bSortable": false
                },
                {
                    "sClass": "tac vam w-90",
                    "aTargets": ['dt_actions'],
                    "mData": "dt_speaker_actions",
                    "bSortable": false
                },
            ],
            "sorting": [
                [0, "desc"]
            ],
            "fnServerData": function(sSource, aoData, fnCallback) {
                if (!requirementFilters) {
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter', {
                        'container': '.wr-filter-list',
                        'debug': false,
                        callBack: function() {
                            dtSpeakers.fnDraw();
                        },
                        onSet: function(callerObj, filterObj) {},
                        onDelete: function(callerObj, filterObj) {},
                        onReset: function() {}
                    });
                }

                aoData = aoData.concat(requirementFilters.getDTFilter());
                $.ajax({
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function(data, textStatus, jqXHR) {
                        if (data.mess_type == 'error' || data.mess_type == 'info')
                            systemMessages(data.message, 'message-' + data.mess_type);

                        fnCallback(data, textStatus, jqXHR);

                    }
                });
            },
            "sPaginationType": "full_numbers",
            "lengthMenu": [
                [25, 50, 100, 250],
                [25, 50, 100, 250]
            ],
            "fnDrawCallback": function(oSettings) {

            }
        });
    });
</script>
