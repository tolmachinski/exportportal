<script type="text/javascript">

    var groupsFilters, dtPickOfTheMonth, requirementFilters;

    $(document).ready(function(){
        dtPickOfTheMonth = $('#dtPickOfTheMonth').dataTable({
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . $url;?>",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "tac w-30", "aTargets": ['dt_id'], "mData": "dt_id", "bSortable": true},
                { "sClass": "tal w-150", "aTargets": ['dt_resource'], "mData": "dt_resource", "bSortable": false},
                { "sClass": "tal", "aTargets": ['dt_start'], "mData": "dt_start", "bSortable": true },
                { "sClass": "tac", "aTargets": ['dt_end'], "mData": "dt_end", "bSortable": true },
                { "sClass": "tac", "aTargets": ['dt_email'], "mData": "dt_email", "bSortable": false },
                { "sClass": "tac", "aTargets": ['dt_id_seller'], "mData": "dt_id_seller", "bSortable": false },
                { "sClass": "tac", "aTargets": ['dt_seller'], "mData": "dt_seller", "bSortable": false },
                { "sClass": "tac", "aTargets": ['dt_phone'], "mData": "dt_phone", "bSortable": false },
                { "sClass": "w-80 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
            ],
            "fnServerParams": function ( aoData ) {},
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug': false,
                        callBack: function(){
                            dtPickOfTheMonth.fnDraw()
                        },
                        onSet: function(callerObj, filterObj){
							if (filterObj.name == 'created_from') {
								$('input[name="created_to"]').datepicker("option", "minDate", $('input[name="created_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'created_to') {
								$('input[name="created_from"]').datepicker("option", "maxDate", $('input[name="created_to"]').datepicker("getDate"));
							}
						},
                        onDelete: function(callerObj, filterObj){
                            if (filterObj.name == 'created_to') {
								$('input[name="created_from"]').datepicker( "option" , {maxDate: null});
                            }

                            if (filterObj.name == 'created_from') {
								$('input[name="created_to"]').datepicker( "option" , {minDate: null});
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

    function delete_record(e) {
        $.ajax({
            type: 'POST',
            url: e.data('delete-link'),
            dataType: 'JSON',
            success: function(resp) {
                systemMessages(resp.message, 'message-' + resp.mess_type);
                dtPickOfTheMonth.fnDraw(true);
            }
        });
    }

</script>

<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span><?php echo $title; ?></span>
        </div>

        <?php //views()->display('admin/downloadable_materials/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table id="dtPickOfTheMonth"
               class="data table-striped table-bordered w-100pr"
               cellspacing="0"
               cellpadding="0">
            <thead>
                <tr>
                    <th class="dt_id w-30">#</th>
                    <th class="dt_resource">Resource</th>
                    <th class="dt_start">Start date</th>
                    <th class="dt_end">End date</th>
                    <th class="dt_email">Email</th>
                    <th class="dt_id_seller">Id Seller</th>
                    <th class="dt_seller">Seller</th>
                    <th class="dt_phone">Phone</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
