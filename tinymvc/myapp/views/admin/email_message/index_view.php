<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span>The message list from email</span>
            <?php if($isAdmin){?>
            <a class="pull-right ep-icon ep-icon_items fs-24 mr-4" href="<?php echo __SITE_URL?>category_support/administration" title="List category of support"></a>
            <?php }else{?>
            <a class="pull-right ep-icon ep-icon_items fs-24 mr-4" href="<?php echo __SITE_URL?>email_message/my" title="Personal message"></a>
            <?php }?>
            <a class="pull-right ep-icon ep-icon_plus-circle txt-green mr-5 confirm-dialog" data-callback="import_email_message" data-message="Are you sure you want to import email message? <br/>Please wait, this may take some time..." title="Import email message"></a>
        </div>
        <?php tmvc::instance()->controller->view->display('admin/email_message/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped table-bordered w-100pr" id="dtMailMessage" cellspacing="0" cellpadding="0" >
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
                    <?php if($isAdmin){?>
                    <th class="dt_category">Category</th>
                    <th class="dt_ep_staff">EP Staff</th>
                    <?php }?>
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
    var dtMailMessage;
    var remove_email_message = function(obj){
        var $this = $(obj);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>email_message/ajax_emai_mess_operation/remove_email_message',
            data: {record: $this.data('record')},
            beforeSend: function(){ },
            dataType: 'json',
            success: function(data){
                systemMessages( data.message, 'message-' + data.mess_type );
                if(data.mess_type == 'success') dtMailMessage.fnDraw(false);
            }
        });
    }

    var import_email_message = function($this){
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>cron/get_new_message/cronaction',
            beforeSend: function(){ },
            dataType: 'json',
            beforeSend: function () {
                $this.removeClass('txt-green')
                     .addClass('txt-green-lighter')
                     .prop('disabled', true)
                     .after('<span class="loader-message pull-right mr-5 ajax-loader"><i style="width: 25px;height: 35px;background-size: 25px;"></i></span>');
            },
            success: function(data){
                $this.removeClass('txt-green-lighter')
                     .addClass('txt-green')
                     .prop('disabled', false);
                $('.loader-message').remove();
                systemMessages( data.message, 'message-' + data.mess_type );
                if(data.mess_type == 'success') dtMailMessage.fnDraw(false);
            }
        });
    }

    var assign_message = function (obj){
        var $this = $(obj);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>email_message/private_ajax_operation/assign_me_message',
            data: {record: $this.data('record')},
            beforeSend: function(){ },
            dataType: 'json',
            success: function(data){
                systemMessages( data.message, 'message-' + data.mess_type );
                if(data.mess_type == 'success') dtMailMessage.fnDraw(false);
            }
        });
    }

    $(document).ready(function(){
        dtMailMessage = $('#dtMailMessage').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>email_message/ajax_dt_email_message",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "w-40 tac vam",  "aTargets": ['dt_id_record'], "mData": "dt_id_record" },
                { "sClass": "w-150 tac vam", "aTargets": ['dt_email_from'],"mData": "dt_email_from"},
                { "sClass": "w-150 tac vam", "aTargets": ['dt_email_to'],  "mData": "dt_email_to"  },
                { "sClass": "w-100 tac vam", "aTargets": ['dt_user_name'], "mData": "dt_user_name","bSortable": false },
                { "sClass": "w-250 tac vam", "aTargets": ['dt_subject'],   "mData": "dt_subject",  "bSortable": false },
                { "sClass": "w-50 tac vam",  "aTargets": ['dt_message'],   "mData": "dt_message",  "bSortable": false },
                { "sClass": "w-80 tac vam",  "aTargets": ['dt_date_time'], "mData": "dt_date_time" },
                { "sClass": "w-50 tac vam",  "aTargets": ['dt_file_att'],  "mData": "dt_file_att" ,"bSortable": false },
                <?php if($isAdmin){?>
                { "sClass": "w-100 tac vam", "aTargets": ['dt_category'],  "mData": "dt_category" ,"bSortable": false },
                { "sClass": "w-120 tac vam", "aTargets": ['dt_ep_staff'],  "mData": "dt_ep_staff" ,"bSortable": false },
                <?php }?>
                { "sClass": "w-100 tac vam", "aTargets": ['dt_status'],    "mData": "dt_status" ,  "bSortable": false },
                { "sClass": "w-100 tac vam", "aTargets": ['dt_actions'],   "mData": "dt_actions",  "bSortable": false }
            ],
            "sorting": [[6, "desc"]],
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){ dtMailMessage.fnDraw(); },
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
                    $("#dtMailMessage tbody *").highlight(keywordsSearch, "highlight");
            }
        });
    });

</script>
