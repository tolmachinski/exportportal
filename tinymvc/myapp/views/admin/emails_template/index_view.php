<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span>The email templates</span>
            <a class="ep-icon ep-icon_download pull-right ml-10 txt-gray call-function" data-callback="export_excel" title="Export excel"></a>
            <a
                class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green"
                href="<?php echo __SITE_URL;?>emails_template/popup_forms/add_template"
                data-title="Add email template"
                data-table="dtEmailsTemplate"
            ></a>
            <iframe src="" id="js-download-report"></iframe>
        </div>

        <?php tmvc::instance()->controller->view->display('admin/emails_template/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table id="dt-emails-template" class="data table-striped table-bordered w-100pr">
            <thead>
                <tr>
                    <th class="dt_id_template">#</th>
                    <th class="dt_template_structure">Structure</th>
                    <th class="dt_template_name">Template</th>
                    <th class="dt_alias">Alias</th>
                    <th class="dt_subject">Subject</th>
                    <th class="dt_header">Header</th>
                    <th class="dt_triggered_information">Triggered information</th>
                    <th class="dt_proofread">Proofread</th>
                    <th class="dt_created">Created</th>
                    <th class="dt_updated">Updated</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
    var requirementFilters;
    var dtEmailsTemplate;

    $(document).ready(function(){
        dtEmailsTemplate = $('#dt-emails-template').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>emails_template/ajax_dt_emails_template",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "w-40 tac vam",  "aTargets": ['dt_id_template'], "mData": "dt_id_template" },
                { "sClass": "tac", "aTargets": ['dt_template_structure'], "mData": "dt_template_structure" },
                { "sClass": "w-100", "aTargets": ['dt_template_name'], "mData": "dt_template_name" },
                { "sClass": "w-100", "aTargets": ['dt_alias'], "mData": "dt_alias" },
                { "sClass": "w-150", "aTargets": ['dt_subject'], "mData": "dt_subject" },
                { "sClass": "w-150", "aTargets": ['dt_header'], "mData": "dt_header" },
                { "sClass": "", "aTargets": ['dt_triggered_information'], "mData": "dt_triggered_information","bSortable": false },
                { "sClass": "w-50 tac", "aTargets": ['dt_proofread'], "mData": "dt_proofread" },
                { "sClass": "w-80 tac", "aTargets": ['dt_created'], "mData": "dt_created" },
                { "sClass": "w-80 tac", "aTargets": ['dt_updated'], "mData": "dt_updated" },
                { "sClass": "w-80 tac vam", "aTargets": ['dt_actions'],   "mData": "dt_actions",  "bSortable": false }
            ],
            "sorting": [[0, "desc"]],
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug': false,
                        callBack: function(){ dtEmailsTemplate.fnDraw(); },
                        onSet: function(callerObj, filterObj){

                        },
                        onDelete: function(filter){

                        }
                    });
                }

                aoData = aoData.concat(requirementFilters.getDTFilter());
                $.ajax( {
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if(data.mess_type == 'error' || data.mess_type == 'info')
                            systemMessages(data.message, 'message-' + data.mess_type);

                        fnCallback(data, textStatus, jqXHR);

                    }
                });
            },
            "sPaginationType": "full_numbers",
            "fnDrawCallback": function( oSettings ) {  }
        });
    });

    var export_excel = function(){
        var exportUrl = "<?php echo  __SITE_URL?>emails_template/export_emails";
        $('#js-download-report').attr('src', exportUrl);
    }
</script>
