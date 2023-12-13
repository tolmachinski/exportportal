<script type="text/javascript">
    var dtMyPopups;
    var ordersFilters;

    $(document).ready(function(){
        dtMyPopups = $('#js-dt-my-orders').dataTable( {
            "sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>popups/ajax_admin_dt/<?php echo $id_popup;?>",
            "sServerMethod": "POST",
            "sorting": [[ 0, "desc" ]],
            "aoColumnDefs": [
                {"sClass": "w-80 vam tac", "aTargets": ['dt_id_data'], "mData": "dt_id_data"},
                {"sClass": "w-150 vat tal", "aTargets": ['dt_users'], "mData": "dt_users"},
                {"sClass": "w-80 vat tac", "aTargets": ['dt_status'], "mData": "dt_status"},
                {"sClass": "w-130 vat tal", "aTargets": ['dt_gr_name'], "mData": "dt_gr_name"},
                {"sClass": "w-50 vat tac", "aTargets": ['dt_rate'], "mData": "dt_rate", "bSortable": false},
                {"sClass": "w-80 vat tal", "aTargets": ['dt_gr_name_selected'], "mData": "dt_gr_name_selected", "bSortable": false},
                {"sClass": "vat tal", "aTargets": ['dt_description'], "mData": "dt_description", "bSortable": false},
                {"sClass": "w-110 vat tal", "aTargets": ['dt_date'], "mData": "dt_date"}
            ],
            "sPaginationType": "full_numbers",
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!ordersFilters) {
                    ordersFilters = $('.dt_filter').dtFilters('.dt_filter', {
                        'container': '.wr-filter-list',
                        callBack: function() {
                            dtMyPopups.fnDraw();
                        },
                        onSet: function(callerObj, filterObj) {
                            if (filterObj.name == 'start_date') {
                                $("#finish_date").datepicker("option", "minDate", $("#start_date").datepicker("getDate"));
                            }

                            if (filterObj.name == 'finish_date') {
                                $("#start_date").datepicker("option", "maxDate", $("#finish_date").datepicker("getDate"));
                            }
                        },
                        onReset: function(){
                            $('.filter-admin-panel .hasDatepicker').datepicker( "option" , {
                                minDate: null,
                                maxDate: null
                            });
                        }
                    });
                }

                aoData = aoData.concat(ordersFilters.getDTFilter());

                $.ajax( {
					"dataType": 'JSON',
					"type": "POST",
					"url": sSource,
					"data": aoData,
					"success": function (data, textStatus, jqXHR) {
						if(data.mess_type == 'error'){
							systemMessages(data.message, 'message-' + data.mess_type);
                        }

						fnCallback(data, textStatus, jqXHR);
					}
				});
             },
            "fnDrawCallback": function(oSettings) { }
        });
    });
</script>

<?php tmvc::instance()->controller->view->display('new/popups/admin/filter_view'); ?>
<div class="mt-10 wr-filter-list clearfix"></div>

<div class="row">
    <div class="col-12">
        <h3 class="titlehdr">Feedbacks <?php echo ($id_popup == 5)?'Registration':'Upgrade';?></h3>

        <table id="js-dt-my-orders" class="data table-bordered table-striped w-100pr" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th class="dt_id_data">Nr.</th>
                    <th class="dt_users">User</th>
                    <th class="dt_status">User status</th>
                    <th class="dt_gr_name">Type Registered</th>
                    <th class="dt_gr_name_selected">Type Selected</th>
                    <th class="dt_rate">Rate</th>
                    <th class="dt_description">Description</th>
                    <th class="dt_date">Date</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
