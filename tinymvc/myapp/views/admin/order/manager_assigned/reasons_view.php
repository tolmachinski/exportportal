<script type="text/javascript">

    var dtMyOrdersReasons;
    var ordersFilters;
	var delete_reason = function(opener){
		var $this = $(opener);
		var reason = $this.data('reason');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>order/ajax_order_operations/delete_reason',
			data: { reason: reason},
			dataType: 'JSON',
			success: function(resp){
				if(resp.mess_type == 'success')
					dtMyOrdersReasons.fnDraw();
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		})
	}

	function dt_redraw_callback(){
        dtMyOrdersReasons.fnDraw();
	}


    $(document).ready(function(){
        dtMyOrdersReasons = $('#dtMyOrdersReasons').dataTable( {
            "sDom": '<"top">rt<"bottom"i><"clear">',
            "iDisplayLength": 10000,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>order/ajax_admin_reasons_dt",
            "sServerMethod": "POST",
            "sorting": [[ 0, "asc" ]],
            "aoColumnDefs": [
                {"sClass": "w-150 vam tac", "aTargets": ['dt_id_reason'], "mData": "dt_id_reason"},
                {"sClass": "vam tal", "aTargets": ['dt_reason'], "mData": "dt_reason", "bSortable": false},
                {"sClass": "vam tal", "aTargets": ['dt_message'], "mData": "dt_message", "bSortable": false},
                {"sClass": "w-80 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
            ],
            "sPaginationType": "full_numbers",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
				if(!ordersFilters) {
                    ordersFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        callBack: function() {
                            dtMyOrdersReasons.fnDraw();
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
</script>


<div class="row">
    <div class="ship col-xs-12" >
        <div class="titlehdr">
            <span>Cancel order reasons</span>
            <a class="pull-right fancyboxValidateModal fancybox.ajax" data-title="Add reason" href="<?php echo __SITE_URL;?>order/popups_order/add_reason/"><i class="ep-icon ep-icon_plus-circle txt-green"></i></a>
        </div>
        <div class="w-50pr pull-left">
            <label>By order status:</label>
            <select data-title="Order status" name="order_status" class="w-230 dt_filter">
                <option value="" >All</option>
                <?php foreach($orders_status as $status){?>
                  <option data-value-text="<?php echo $status['status'];?>" value="<?php echo $status['id'];?>"><?php echo $status['status'];?></option>
                <?php }?>
            </select>
        </div>
        <div class="w-50pr pull-right tar">
            <label>Search by</label>
            <input type="text" data-title="Search for" name="keywords" maxlength="50" class="w-230 keywords dt_filter" id="keywords" placeholder="Keywords">
        </div>
        <div class="mt-10 wr-filter-list clearfix"></div>

        <table cellspacing="0" cellpadding="0" id="dtMyOrdersReasons" class="data table-bordered table-striped w-100pr">
            <thead>
                <tr>
                    <th class="dt_id_reason">Nr.</th>
                    <th class="dt_reason">Reason</th>
                    <th class="dt_message">Message</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
