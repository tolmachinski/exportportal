<div class="row">
    <div class="col-xs-12">
        <?php views()->display('admin/draft_extend/filter_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table id="draft-extend--list" class="data table-bordered table-striped w-100pr dataTable">
			<thead>
                <tr>
                    <th class="dt-id">#</th>
                    <th class="dt-user">User</th>
                    <th class="dt-expiration">Expires Date</th>
                    <th class="dt-extend">Extend till</th>
                    <th class="dt-status">Status</th>
                    <th class="dt-requested">Requested on</th>
                    <th class="dt-items">Items</th>
                    <th class="dt-reason">Reason</th>
                    <th class="dt-actions"></th>
                </tr>
			</thead>
			<tbody class="tabMessage" id="pageall"></tbody>
		</table>
    </div>
</div>
<script>
    var requirementFilters;
    var dtRequests;

    $(document).ready(function(){
        dtRequests = $('#draft-extend--list').dataTable( {
            sDom: '<"top"lp>rt<"bottom"ip><"clear">',
            bProcessing: false,
            bServerSide: true,
            sAjaxSource: "<?php echo __SITE_URL . "draft_extend/ajax_admin_operation/list-requests";?>",
            sServerMethod: "POST",
            aoColumnDefs: [
                { sClass: "w-100 tac vat", aTargets: "dt-id", mData: "id", bSortable: false },
                { sClass: "w-200 tac vat", aTargets: "dt-user", mData: "user", bSortable: false },
                { sClass: "w-150 tac vat", aTargets: "dt-expiration", mData: "expiration", bSortable: true },
                { sClass: "w-150 tac vat", aTargets: "dt-extend", mData: "extend", bSortable: true },
                { sClass: "w-150 tac vat", aTargets: "dt-status", mData: "status", bSortable: false },
                { sClass: "w-150 tac vam dn-lg", aTargets: "dt-requested", mData: "requested", bSortable: true },
                { sClass: "w-150 tac vam", aTargets: "dt-items", mData: "items", bSortable: false },
                { sClass: "w-150 tac vam", aTargets: "dt-reason", mData: "reason", bSortable: false },
                { sClass: "w-40 tac vam", aTargets: "dt-actions", mData: "actions", bSortable: false },
            ],
            sorting: [[0, "desc"]],
            sPaginationType: "full_numbers",
            language: {
                url: location.origin + "/public/plug/jquery-datatables-1-10-12/i18n/" + __site_lang + ".json",
                paginate: {
                    previous: '<i class="ep-icon ep-icon_arrows-left"></i>',
                    first: '<i class="ep-icon ep-icon_arrow-left"></i>',
                    next: '<i class="ep-icon ep-icon_arrows-right"></i>',
                    last: '<i class="ep-icon ep-icon_arrow-right"></i>',
                },
            },
            fnServerParams: function ( aoData ) {},
            fnServerData: function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){ dtRequests.fnDraw(); },
                        onSet: function(callerObj, filterObj){
							if (filterObj.name == 'requested_from') {
								$('input[name="requested_to"]').datepicker("option", "minDate", $('input[name="requested_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'requested_to') {
								$('input[name="requested_from"]').datepicker("option", "maxDate", $('input[name="requested_to"]').datepicker("getDate"));
							}

							if (filterObj.name == 'expiration_from') {
								$('input[name="expiration_to"]').datepicker("option", "minDate", $('input[name="expiration_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'expiration_to') {
								$('input[name="expiration_from"]').datepicker("option", "maxDate", $('input[name="expiration_to"]').datepicker("getDate"));
							}
						},
                        onDelete: function(callerObj, filterObj){
                            if (filterObj.name == 'requested_from') {
								$('input[name="requested_from"]').datepicker( "option" , {maxDate: null});
                            }

                            if (filterObj.name == 'requested_to') {
								$('input[name="requested"]').datepicker( "option" , {minDate: null});
							}

                            if (filterObj.name == 'expiration_to') {
								$('input[name="expiration_from"]').datepicker( "option" , {maxDate: null});
                            }

                            if (filterObj.name == 'expiration_from') {
								$('input[name="expiration_to"]').datepicker( "option" , {minDate: null});
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
            "lengthMenu": [[10, 50, 100, 250], [10, 50, 100, 250]],
            "fnDrawCallback": function( oSettings ) {

            }
        });
    });

    var changeStatusRequest = function(element){
        var $this = $(element);
        var request = $this.data('request');
        var url = $this.data('url');
        var status = $this.data('status');

        $.ajax({
            url: url,
            type: 'POST',
            data:  {request: request, status: status},
            dataType: 'json',
            success: function(resp){
                systemMessages(resp.message, resp.mess_type);

                if ('success' == resp.mess_type) {
                    dtRequests.fnDraw();
                }
            }
        });
    }
</script>
