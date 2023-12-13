<div class="row">
	<div class="col-xs-12">
		<?php views('admin/matchmaking/sellers_list_filter_bar_view');?>
		<div class="titlehdr h-30">
			<span>Sellers list</span>
            <a class="ep-icon ep-icon_file-text pull-right ml-10 fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL . 'matchmaking/popup_forms/export/' . $userId;?>" data-title="Export sellers" title="Export sellers"></a>
            <iframe src="" id="exportSellers" class="display-n"></iframe>
        </div>

		<div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-bordered table-striped w-100pr" id="dtSellersList">
			<thead>
				<tr>
					<th class="dt_name">Full name</th>
					<th class="dt_company">Company name</th>
					<th class="dt_email">Email</th>
					<th class="dt_phone">Phone number</th>
					<th class="dt_country">Country</th>
					<th class="dt_b2b_requests">Has B2B requests</th>
					<th class="dt_count_items">Count items in industries of interest</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>
<script>
    var requirementFilters;
    var dtSellersList;

    $(document).ready(function(){
        dtSellersList = $('#dtSellersList').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . "matchmaking/ajax_operations/dt_sellers_list";?>",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "tal vam w-30", "aTargets": ["dt_name"], "mData": "dt_name", "bSortable": false },
                { "sClass": "tal vam w-30", "aTargets": ["dt_company"], "mData": "dt_company", "bSortable": false },
                { "sClass": "tal vam w-30", "aTargets": ["dt_email"], "mData": "dt_email", "bSortable": false },
                { "sClass": "tal vam w-30", "aTargets": ["dt_phone"], "mData": "dt_phone", "bSortable": false },
                { "sClass": "tal vam w-30", "aTargets": ['dt_country'], "mData": "dt_country", "bSortable": false },
                { "sClass": "tac vam w-50", "aTargets": ['dt_b2b_requests'], "mData": "dt_b2b_requests", "bSortable": false },
                { "sClass": "tac vam w-70", "aTargets": ['dt_count_items'], "mData": "dt_count_items", "bSortable": false },
            ],
            "sorting": [[0, "desc"]],
            "fnServerData": function ( sSource, aoData, fnCallback ) {
                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){
                            dtSellersList.fnDraw();
                        },
                        onSet: function(callerObj, filterObj){
						},
                        onDelete: function(callerObj, filterObj){
                        },
						onReset: function(){
						}
                    });
                }

                aoData = aoData.concat(requirementFilters.getDTFilter(), {name: 'userId', value: '<?php echo $userId;?>'});
                $.ajax( {
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if (data.mess_type == 'error' || data.mess_type == 'info'){
                            systemMessages(data.message, 'message-' + data.mess_type);
                        }

                        fnCallback(data, textStatus, jqXHR);
                    }
                });
            },
            "sPaginationType": "full_numbers",
            "lengthMenu": [[50, 100, 250], [50, 100, 250]],
            "targets": 'no-sort',
            "bSort": false,
            "order": [],
            "fnDrawCallback": function( oSettings ) {

            }
        });
    });
</script>
