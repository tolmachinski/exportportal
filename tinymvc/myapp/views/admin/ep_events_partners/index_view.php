<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr">
            <span>EP Events - partners</span>
            <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="ep_events_partners/popup_forms/add_partner" data-table="dtPartners" data-title="Add Partner">
            </a>
        </div>

        <?php views('admin/ep_events_partners/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped table-bordered w-100pr" id="dtPartners" cellspacing="0" cellpadding="0">
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
    var dtPartners;

    $(document).ready(function() {
        dtPartners = $('#dtPartners').dataTable({
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . 'ep_events_partners/ajax_dt_administration'; ?>",
            "sServerMethod": "POST",
            "aoColumnDefs": [{
                    "sClass": "tac vam w-30",
                    "aTargets": ['dt_id'],
                    "mData": "dt_partner_id"
                },
                {
                    "sClass": "tac vam",
                    "aTargets": ['dt_image'],
                    "mData": "dt_partner_image",
                    "bSortable": false
                },
                {
                    "sClass": "tac vam",
                    "aTargets": ['dt_name'],
                    "mData": "dt_partner_name",
                    "bSortable": false
                },
                {
                    "sClass": "tac vam w-90",
                    "aTargets": ['dt_actions'],
                    "mData": "dt_partner_actions",
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
                            dtPartners.fnDraw();
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
