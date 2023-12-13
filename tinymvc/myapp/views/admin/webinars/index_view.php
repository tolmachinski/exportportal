
<script type="text/javascript">

    var groupsFilters, dtWebinar, requirementFilters;

    $(document).ready(function(){
        dtWebinar = $('#dtWebinar').dataTable({
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . 'webinars/ajaxDtAdministration';?>",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "tac w-30", "aTargets": ['dt_id'], "mData": "dt_id", "bSortable": true},
                { "sClass": "tal w-150", "aTargets": ['dt_title'], "mData": "dt_title", "bSortable": false},
                { "sClass": "tal w-50", "aTargets": ['dt_start_date'], "mData": "dt_start_date", "bSortable": true},
                { "sClass": "tal w-150", "aTargets": ['dt_link'], "mData": "dt_link", "bSortable": false },
                { "sClass": "tac w-50", "aTargets": ['dt_requested'], "mData": "dt_requested", "bSortable": true },
                { "sClass": "tac w-50", "aTargets": ['dt_attended'], "mData": "dt_attended", "bSortable": true },
                { "sClass": "tac w-50", "aTargets": ['dt_leads'], "mData": "dt_leads", "bSortable": true },
                { "sClass": "tac w-50", "aTargets": ['dt_created'], "mData": "dt_created", "bSortable": true },
                { "sClass": "w-80 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
            ],
            "fnServerParams": function ( aoData ) {},
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug': false,
                        callBack: function(){
                            dtWebinar.fnDraw()
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

</script>

<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span>Webinars</span>
            <?php if(have_right('webinars_administration')) { ?>
                <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="webinars/popup_forms/add_webinar" data-title="Add webinar"></a>
            <?php } ?>
        </div>

        <?php views()->display('admin/webinars/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table id="dtWebinar"
               class="data table-striped table-bordered w-100pr"
               cellspacing="0"
               cellpadding="0">
            <thead>
                <tr>
                    <th class="dt_id w-30">#</th>
                    <th class="dt_title">Title</th>
                    <th class="dt_start_date">Start date</th>
                    <th class="dt_link">Link</th>
                    <th class="dt_requested">Count requested</th>
                    <th class="dt_attended">Count attended</th>
                    <th class="dt_leads">Count converted leads</th>
                    <th class="dt_created">Created</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
