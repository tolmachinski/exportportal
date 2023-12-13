<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span>The message list from email</span>
            <a class="pull-right ep-icon ep-icon_items fs-24 mr-4" href="<?php echo __SITE_URL?>email_message/administration" title="List of message"></a>
        </div>
        <?php tmvc::instance()->controller->view->display('admin/email_message/private/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>
        <table class="data table-striped table-bordered w-100pr" id="dtPrivateMessage" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id_record">#</th>
                    <th class="dt_email_from">Email box</th>
                    <th class="dt_email_to">User Email</th>
                    <th class="dt_user_name">EP User</th>
                    <th class="dt_subject">Subject</th>
                    <th class="dt_message">Message</th>
                    <th class="dt_date_time">Date</th>
                    <th class="dt_file_att">Attachment</th>
                    <th class="dt_status">Status</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<script>
    var requirementFilters;
    var dtPrivateMessage;

    $(document).ready(function(){
        dtPrivateMessage = $('#dtPrivateMessage').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>email_message/ajax_dt_user_staff",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "w-40 tac vam",  "aTargets": ['dt_id_record'], "mData": "dt_id_record" },
                { "sClass": "w-150 tac vam", "aTargets": ['dt_email_from'],"mData": "dt_email_from"},
                { "sClass": "w-150 tac vam", "aTargets": ['dt_email_to'],  "mData": "dt_email_to"  },
                { "sClass": "w-120 tac vam", "aTargets": ['dt_user_name'], "mData": "dt_user_name","bSortable": false },
                { "sClass": "tac vam",       "aTargets": ['dt_subject'],   "mData": "dt_subject",  "bSortable": false },
                { "sClass": "w-50 tac vam",  "aTargets": ['dt_message'],   "mData": "dt_message",  "bSortable": false },
                { "sClass": "w-100 tac vam", "aTargets": ['dt_date_time'], "mData": "dt_date_time","bSortable": false },
                { "sClass": "w-50 tac vam",  "aTargets": ['dt_file_att'],  "mData": "dt_file_att" ,"bSortable": false },
                { "sClass": "w-100 tac vam", "aTargets": ['dt_status'],    "mData": "dt_status",   "bSortable": false },
                { "sClass": "w-100 tac vam", "aTargets": ['dt_actions'],   "mData": "dt_actions",  "bSortable": false }
            ],
            "sorting": [[0, "asc"]],
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){ dtPrivateMessage.fnDraw(); },
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
            "fnDrawCallback": function( oSettings ) {

                var keywordsSearch = $('.filter-admin-panel').find('input[name=keywords]').val();
                if( keywordsSearch !== '' )
                    $("#dtPrivateMessage tbody *").highlight(keywordsSearch, "highlight");
            }
        });
    });

</script>
