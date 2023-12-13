<script type="text/javascript">
    var dtMyOrders;
    var ordersFilters;

	function dt_redraw_callback(){
        dtMyOrders.fnDraw(false);
	}

    $(document).ready(function(){
        dtMyOrders = $('#dtMyOrders').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . 'sample_orders/ajax_dt_administration';?>",
            "sServerMethod": "POST",
            "sorting": [[ 5, "desc" ]],
            "aoColumnDefs": [
                {"sClass": "w-100 vam tac", "aTargets": ['dt_id_order'], "mData": "dt_id_order"},
                {"sClass": "w-200 vat tal", "aTargets": ['dt_users'], "mData": "dt_users", "bSortable": false},
                {"sClass": "w-200 vam tac", "aTargets": ['dt_shiper'], "mData": "dt_shiper", 'bSortable': false},
                {"sClass": "w-100 vam tal", "aTargets": ['dt_price'], "mData": "dt_price"},
                {"sClass": "w-120 tac vam", "aTargets": ['dt_create_date'], "mData": "dt_create_date"},
                {"sClass": "w-120 tac vam", "aTargets": ['dt_update_date'], "mData": "dt_update_date"},
                {"sClass": "w-50 tac vam", "aTargets": ['dt_problems'], "mData": "dt_problems", 'bSortable': false},
                {"sClass": "w-150 tac vam", "aTargets": ['dt_status'], "mData": "dt_status", 'bSortable': false},
                {"sClass": "w-50 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
            ],
            "sPaginationType": "full_numbers",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
				if(!ordersFilters) {
                    ordersFilters = $('.dt_filter').dtFilters('.dt_filter', {
                        'container': '.wr-filter-list',
                        callBack: function() {
                            dtMyOrders.fnDraw();
                        },
                        onSet: function(callerObj, filterObj) {
                            if (filterObj.name == 'created_from') {
                                $("#created_to").datepicker("option", "minDate", $("#created_from").datepicker("getDate"));
                            }

                            if (filterObj.name == 'created_to') {
                                $("#created_from").datepicker("option", "maxDate", $("#created_to").datepicker("getDate"));
                            }

                            if (filterObj.name == 'updated_from') {
                                $("#updated_to").datepicker("option", "minDate", $("#updated_from").datepicker("getDate"));
                            }

                            if (filterObj.name == 'updated_to') {
                                $("#updated_from").datepicker("option", "maxDate", $("#updated_to").datepicker("getDate"));
                            }
                        },
                        onDelete: function(filter){

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
						if(data.mess_type == 'error')
							systemMessages(data.message, 'message-' + data.mess_type);

						fnCallback(data, textStatus, jqXHR);
					}
				});
            },
            "fnDrawCallback": function(oSettings) {
            }
        });
    });

    var confirm_payment = function (element) {
        var id_order = $(element).data('order');
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . 'sample_orders/ajax_operations/confirm_payment';?>',
            data: { sample_order_id : id_order},
            dataType: 'json',
            success: function(data){
                if (data.mess_type == 'success') {
					dtMyOrders.fnDraw(false);
                }

                systemMessages(data.message, 'message-' + data.mess_type );
            }
        });
    }
</script>

<?php views()->display('admin/sample_orders/filter_view'); ?>

<div class="mt-10 wr-filter-list clearfix"></div>

<div class="row">
    <div class="ship col-xs-12" >
        <h3 class="titlehdr">Sample orders</h3>
        <table cellspacing="0" cellpadding="0" id="dtMyOrders" class="data table-bordered table-striped w-100pr">
            <thead>
                <tr>
                    <th class="dt_id_order">Nr.</th>
                    <th class="dt_users">Seller/Buyer</th>
                    <th class="dt_shiper">Freight Forwarder</th>
                    <th class="dt_price">Price</th>
                    <th class="dt_create_date">Create date</th>
                    <th class="dt_update_date">Update date</th>
                    <th class="dt_problems">Problems</th>
                    <th class="dt_status">Status</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
