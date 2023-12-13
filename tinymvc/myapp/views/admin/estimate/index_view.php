<script type="text/javascript">
var estimatesFilters;
var dtEstimates;

function fnFormatDetails(nTr) {
	var aData = dtEstimates.fnGetData(nTr);
	var sOut = '<div class="dt-details"><table class="dt-details__table">\
			<tr>\
				<td>'
					+ aData['dt_log'] +
				'</td>\
			</tr></table></div>';
	return sOut;
}
$(document).ready(function(){
	dtEstimates = $('#dtEstimates').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>estimate/ajax_estimate_administration",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-50 tac", "aTargets": ['dt_id_request_estimate'], "mData": "dt_id_request_estimate", "bSortable": false },
			{ "sClass": "w-50", "aTargets": ['dt_estimate'], "mData": "dt_estimate", "bSortable": false},
			{ "sClass": "mw-100 w-100", "aTargets": ['dt_status'], "mData": "dt_status"},
			{ "sClass": "", "aTargets": ['dt_item'], "mData": "dt_item", "bSortable": false },
			{ "sClass": "tac", "aTargets": ['dt_buyer'], "mData": "dt_buyer", "bSortable": false },
			{ "sClass": "tac", "aTargets": ['dt_seller'], "mData": "dt_seller", "bSortable": false },
			{ "sClass": "w-80 tac", "aTargets": ['dt_quantity'], "mData": "dt_quantity" },
			{ "sClass": "tac mw-100 w-100", "aTargets": ['dt_price'], "mData": "dt_price" },
			{ "sClass": "mw-120 w-120 tac", "aTargets": ['dt_date_created'], "mData": "dt_date_created" },
			{ "sClass": "mw-120 w-120 tac", "aTargets": ['dt_date_changed'], "mData": "dt_date_changed" },
		],
		"fnServerParams": function ( aoData ) {
			typeInquiry = 'all';
			$('.menu-level3 li').each(function(){
				if($(this).attr('class') == 'active')
					typeInquiry = $(this).children('a').attr('href');
			});

			aoData.push( { "name": "type_inquiry", "value": typeInquiry } );
		},
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			if(!estimatesFilters){
				estimatesFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':true,
					callBack: function(){
						dtEstimates.fnDraw();
					},
					onSet: function(callerObj, filterObj){

						if(filterObj.name == 'status'){
							$('.menu-level3').find('a[data-value="'+filterObj.value+'"]').parent('li').addClass('active').siblings('li').removeClass('active');
						}

					},
					onDelete: function(filter){

						if(filter.name == 'status'){
							var $li = $('a[data-name=' + filter.name + '][data-value="' + filter.default + '"]').parent('li');
							$li.addClass('active').siblings('li').removeClass('active');
						}
					}
				});
			}

			aoData = aoData.concat(estimatesFilters.getDTFilter());
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
				$("#dtEstimates tbody *").highlight(keywordsSearch, "highlight");

			$('.rating-bootstrap').rating();
		}
	});

	$('body').on('click', 'a[rel=log_details]', function() {
		var $thisBtn = $(this);
	    var nTr = $thisBtn.parents('tr')[0];
	    if (dtEstimates.fnIsOpen(nTr))
			dtEstimates.fnClose(nTr); // This row is already open - close it
	    else
			dtEstimates.fnOpen(nTr, fnFormatDetails(nTr), 'details');// Open this row

		$thisBtn.toggleClass('ep-icon_plus ep-icon_minus');
	});

	idStartItemNew = <?php echo $last_estimates_id;?>;
	startCheckAdminNewItems('estimate/ajax_estimate_operation/check_new', idStartItemNew);
});
</script>
<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr">Estimates</div>
		<ul class="menu-level3 mb-10 clearfix">
			<li class="active"><a class="dt_filter" data-name="status" data-title="Status" data-value="" data-value-text="All" href="#">All</a></li>
			<li><a class="dt_filter" data-name="status" data-value="new" data-title="Status" data-value-text="New" href="#">New (<?php echo intval($statuses['new']['counter']);?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-value="wait_buyer" data-title="Status" data-value-text="Wait buyer" href="#">Wait buyer (<?php echo intval($statuses['wait_buyer']['counter']);?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-value="wait_seller" data-title="Status" data-value-text="Wait seller" href="#">Wait seller (<?php echo intval($statuses['wait_seller']['counter']);?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-value="accepted" data-title="Status" data-value-text="Accepted" href="#">Accepted (<?php echo intval($statuses['accepted']['counter']);?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-value="initiated" data-title="Status" data-value-text="Order initiated" href="#">Order initiated (<?php echo intval($statuses['initiated']['counter']);?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-value="declined" data-title="Status" data-value-text="Declined" href="#">Declined (<?php echo intval($statuses['declined']['counter']);?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-value="expired" data-title="Status" data-value-text="Expired" href="#">Expired (<?php echo intval($statuses['expired']['counter']);?>)</a></li>
		</ul>

		<?php tmvc::instance()->controller->view->display('admin/estimate/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtEstimates" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					 <th class="dt_id_request_estimate">#</th>
					 <th class="dt_estimate">History</th>
					 <th class="dt_status">Status</th>
					 <th class="dt_item">Item</th>
					 <th class="dt_buyer">Buyer</th>
					 <th class="dt_seller">Seller</th>
					 <th class="dt_quantity">Quantity</th>
					 <th class="dt_price">Price</th>
					 <th class="dt_date_created">Created</th>
					 <th class="dt_date_changed">Changed</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>
