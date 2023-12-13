
<script type="text/javascript">

    var groupsFilters, dtWebinarRequests, requirementFilters;

    $(document).ready(function(){
        dtWebinarRequests = $('#dtWebinarRequests').dataTable({
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . 'webinar_requests/ajaxDtAdministration';?>",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "tac w-30", "aTargets": ['dt_id'], "mData": "dt_id", "bSortable": true},
                { "sClass": "tal w-150", "aTargets": ['dt_user'], "mData": "dt_user", "bSortable": false},
                { "sClass": "tal w-100", "aTargets": ['dt_webinar'], "mData": "dt_webinar", "bSortable": false},
                { "sClass": "tal w-150", "aTargets": ['dt_contacts'], "mData": "dt_contacts", "bSortable": false },
                { "sClass": "tac w-100", "aTargets": ['dt_country'], "mData": "dt_country", "bSortable": false },
                { "sClass": "tac w-50", "aTargets": ['dt_user_type'], "mData": "dt_user_type", "bSortable": true },
                { "sClass": "tac w-70", "aTargets": ['dt_status'], "mData": "dt_status", "bSortable": false },
                { "sClass": "tac w-50", "aTargets": ['dt_requested'], "mData": "dt_requested", "bSortable": true },
                { "sClass": "w-80 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
            ],
            "fnServerParams": function ( aoData ) {},
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug': false,
                        callBack: function(){
                            dtWebinarRequests.fnDraw()
                        },
                        onSet: function(callerObj, filterObj){
							if (filterObj.name == 'requested_from') {
								$('input[name="requested_to"]').datepicker("option", "minDate", $('input[name="requested_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'requested_to') {
								$('input[name="requested_from"]').datepicker("option", "maxDate", $('input[name="requested_to"]').datepicker("getDate"));
							}
						},
                        onDelete: function(callerObj, filterObj){
                            if (filterObj.name == 'requested_to') {
								$('input[name="requested_from"]').datepicker( "option" , {maxDate: null});
                            }

                            if (filterObj.name == 'requested_from') {
								$('input[name="requested_to"]').datepicker( "option" , {minDate: null});
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

                $.ajax({
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if(data.mess_type == 'error' || data.mess_type == 'info') {
                            systemMessages(data.message, 'message-' + data.mess_type);
                        }

                        fnCallback(data, textStatus, jqXHR);
                    }
                });
            },
            "sorting" : [[0,'desc']],
            "sPaginationType": "full_numbers",
            "fnDrawCallback": function( oSettings ) {}
        });
    });

    function status_record(e)
    {
        var status = $(e).data('status');
        var id = $(e).data('id');
        $.ajax({
            type: 'POST',
            url: e.data('link'),
            dataType: 'JSON',
            data: {status: status, id: id},
            success: function(resp) {
                systemMessages(resp.message, 'message-' + resp.mess_type);
                dtWebinarRequests.fnDraw(false);
            }
        });
    }
</script>

<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span>Webinar Requests</span>
        </div>

        <?php views()->display('admin/webinar_requests/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table id="dtWebinarRequests"
               class="data table-striped table-bordered w-100pr"
               cellspacing="0"
               cellpadding="0">
            <thead>
                <tr>
                    <th class="dt_id w-30">#</th>
                    <th class="dt_user">User</th>
                    <th class="dt_webinar">Webinar</th>
                    <th class="dt_contacts">Contacts</th>
                    <th class="dt_country">Country</th>
                    <th class="dt_user_type">User type</th>
                    <th class="dt_status">Status</th>
                    <th class="dt_requested">Requested on</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
