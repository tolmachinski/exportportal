<div class="row">
    <div class="col-xs-12">
        <?php views()->display('admin/mail_messages/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped table-bordered w-100pr" id="dtMailMessage" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id_record w-20 tac vam">#</th>
                    <th class="dt_subject w-450 tac vam">Subject</th>
                    <th class="dt_email_to w-200 tac vam">To</th>
                    <th class="dt_email_from w-100">From</th>
                    <th class="dt_view_url w-100 tac vam">View email</th>
                    <th class="dt_sent_date w-80 tac vam">Date</th>
                    <th class="dt_is_sent w-50 tac vam">Is sent</th>
                    <?php if (config('env.APP_ENV') === 'dev') {?>
                        <th class="dt_actions w-50 tac vam">Actions</th>
                    <?php }?>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<script>
    var requirementFilters;
    var dtMailMessage;

    <?php if (config('env.APP_ENV') === 'dev') {?>
        var sendEmailMessage = function (element) {
            var onRequestSuccess = function (data) {
                systemMessages(data.message, data.mess_type);
                if ('success' === data.mess_type) {
                    dtMailMessage.fnDraw();
                }
            };

            postRequest(__site_url + 'mail_messages/send_email', { id: $(element).data('id') })
                    .then(onRequestSuccess)
                    .catch(onRequestError);
        }
    <?php }?>

    $(document).ready(function(){
        dtMailMessage = $('#dtMailMessage').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . "mail_messages/ajax_dt_email_message";?>",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "w-20 tac vam",  "aTargets": ['dt_id_record'], "mData": "dt_id_record", "bSortable": false },
                { "sClass": "w-450 tac vam", "aTargets": ['dt_subject'],   "mData": "dt_subject", "bSortable": false },
                { "sClass": "w-200 tac vam", "aTargets": ['dt_email_to'],  "mData": "dt_email_to", "bSortable": false },
                { "sClass": "w-150 tac vam", "aTargets": ['dt_email_from'],"mData": "dt_email_from", "bSortable": false },
                { "sClass": "w-100 tac vam", "aTargets": ['dt_view_url'], "mData": "dt_view_url", "bSortable": false },
                { "sClass": "w-100 tac vam",  "aTargets": ['dt_sent_date'], "mData": "dt_sent_date" },
                { "sClass": "w-50 tac vam",  "aTargets": ['dt_is_sent'],  "mData": "dt_is_sent", "bSortable": false },
                <?php if (config('env.APP_ENV') === 'dev') {?>
                    { "sClass": "w-50 tac vam",  "aTargets": ['dt_actions'],  "mData": "dt_actions", "bSortable": false }
                <?php }?>
            ],
            "sorting": [[5, "desc"]],
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){ dtMailMessage.fnDraw(); },
                        onSet: function(callerObj, filterObj){
							if (filterObj.name == 'sent_date_from') {
								$('input[name="sent_date_to"]').datepicker("option", "minDate", $('input[name="sent_date_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'sent_date_to') {
								$('input[name="sent_date_from"]').datepicker("option", "maxDate", $('input[name="sent_date_to"]').datepicker("getDate"));
							}
						},
                        onDelete: function(callerObj, filterObj){
                            if (filterObj.name == 'sent_date_to') {
								$('input[name="sent_date_from"]').datepicker( "option" , {maxDate: null});
                            }

                            if (filterObj.name == 'sent_date_from') {
								$('input[name="sent_date_to"]').datepicker( "option" , {minDate: null});
							}
                        },
						onReset: function(){
							$('.dt_filter .hasDatepicker').datepicker( "option" , {
								minDate: null,
								maxDate: null
							});
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
            "lengthMenu": [[50, 100, 250], [50, 100, 250]],
            "fnDrawCallback": function( oSettings ) {

            }
        });
    });

</script>
