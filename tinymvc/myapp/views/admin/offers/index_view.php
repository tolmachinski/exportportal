<script type="text/javascript">
var offersFilters;
var dtOffers;

function fnFormatDetails(nTr) {
	var aData = dtOffers.fnGetData(nTr);

	var sOut = '<div class="dt-details"><table class="dt-details__table">\
			<tr>\
				<td>\
					<div class="txt-bold fs-14 pb-10"><i class="ep-icon ep-icon_clock"></i> Offer timeline</div>'
					+ aData['dt_log'] +
				'</td>\
			</tr></table></div>';
	return sOut;
}

$(document).ready(function(){

	$('.menu-level3 a').on('click', function(e){
		e.preventDefault();
		var $parentLi = $(this).parent('li');
		if(!$parentLi.hasClass('active')){
			$parentLi.addClass('active').siblings('li').removeClass('active');
			dtOffers.fnDraw();
		}
	});

	dtOffers = $('#dtOffers').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>offers/ajax_offers_administration",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-40 tac", "aTargets": ['dt_id_offer'], "mData": "dt_id_offer" },
			{ "sClass": "w-100", "aTargets": ['dt_status'], "mData": "dt_status" },
			{ "sClass": "w-20pr", "aTargets": ['dt_item'], "mData": "dt_item" },
			{ "sClass": "", "aTargets": ['dt_buyer'], "mData": "dt_buyer" },
			{ "sClass": "", "aTargets": ['dt_seller'], "mData": "dt_seller" },
			{ "sClass": "tac w-30", "aTargets": ['dt_quantity'], "mData": "dt_quantity", "bSortable": false },
			{ "sClass": "tac w-80", "aTargets": ['dt_price'], "mData": "dt_price", "bSortable": false },
			{ "sClass": "w-120 tac", "aTargets": ['dt_date_crate'], "mData": "dt_date_crate" },
			{ "sClass": "w-120 tac", "aTargets": ['dt_date_expired'], "mData": "dt_date_expired"},
		],
		"fnServerParams": function ( aoData ) {

		},
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			if(!offersFilters){
				offersFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':true,
					callBack: function(){ dtOffers.fnDraw(); },
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

			aoData = aoData.concat(offersFilters.getDTFilter());
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
			$('.rating-bootstrap').rating();
		}
	});

	$('body').on('click', 'a[rel=log_details]', function() {
		var $thisBtn = $(this);
	    var nTr = $thisBtn.parents('tr')[0];
	    if (dtOffers.fnIsOpen(nTr))
			dtOffers.fnClose(nTr); // This row is already open - close it
	    else
			dtOffers.fnOpen(nTr, fnFormatDetails(nTr), 'details');// Open this row

		$thisBtn.toggleClass('ep-icon_plus ep-icon_minus');
	});

	idStartItemNew = <?php echo $last_offers_id;?>;
	startCheckAdminNewItems('offers/ajax_offers_operation/check_new', idStartItemNew);
});
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr">Offers</div>
		<ul class="menu-level3 mb-10 clearfix">
			<li class="active"><a class="dt_filter" data-name="status" data-title="Status" data-value="" data-value-text="All" href="#">All</a></li>
			<li><a class="dt_filter" data-name="status" data-title="Status" data-value="new" data-value-text="New" href="#">New (<?php echo intval($statuses['new']['counter']); ?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-title="Status" data-value="wait_buyer" data-value-text="Waiting for the Buyer" href="#">Waiting for the Buyer (<?php echo intval($statuses['wait_buyer']['counter']);?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-title="Status" data-value="wait_seller" data-value-text="Waiting for the Seller" href="#">Waiting for the Seller (<?php echo intval($statuses['wait_seller']['counter']);?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-title="Status" data-value="accepted" data-value-text="Accepted" href="#">Accepted (<?php echo intval($statuses['accepted']['counter']);?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-title="Status" data-value="initiated" data-value-text="Order initiated" href="#">Order initiated (<?php echo intval($statuses['initiated']['counter']);?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-title="Status" data-value="declined" data-value-text="Declined" href="#">Declined (<?php echo intval($statuses['declined']['counter']);?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-title="Status" data-value="expired" data-value-text="Expired" href="#">Expired (<?php echo intval($statuses['expired']['counter']);?>)</a></li>
		</ul>

		<?php tmvc::instance()->controller->view->display('admin/offers/filter_panel_view'); ?>
		<div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtOffers" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					 <th class="dt_id_offer">#</th>
					 <th class="dt_status">Status</th>
					 <th class="dt_item">Item</th>
					 <th class="dt_buyer">Buyer Name</th>
					 <th class="dt_seller">Seller Name</th>
					 <th class="dt_quantity">Quantity</th>
					 <th class="dt_price">Price</th>
					 <th class="dt_date_crate">Created</th>
					 <th class="dt_date_expired">Expired</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>
