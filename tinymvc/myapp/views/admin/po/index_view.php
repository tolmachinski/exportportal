<script type="text/javascript">
var dtPo;
var poFilters;

function fnFormatDetails(nTr) {
	var aData = dtPo.fnGetData(nTr);

	var sOut = '<div class="dt-details"><table class="dt-details__table">\
			<tr>\
				<td class="w-50pr">\
					<div class="txt-bold fs-14 pb-10"><i class="ep-icon ep-icon_comments "></i> Comment</div>'
					+ aData['dt_comment'] +
				'</td>\
				<td class="w-50pr">\
					<div class="txt-bold fs-14 pb-10"><i class="ep-icon ep-icon_gears "></i> PO Changes</div>'
					+ aData['dt_po_changes'] +
				'</td>\
			</tr>\
			<tr>\
				<td>\
					<div class="txt-bold fs-14 pb-10"><i class="ep-icon ep-icon_clock"></i> PO Timeline</div>'
					+ aData['dt_log'] +
				'</td>\
				<td class="w-50pr">\
					<div class="txt-bold fs-14 pb-10"><i class="ep-icon ep-icon_gears "></i> Prototype changes</div>'
					+ aData['dt_changes'] +
				'</td>\
			</tr></table></div>';
	return sOut;
}

$(document).ready(function(){

	dtPo= $('#dtPo').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>po/ajax_po_administration",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-50 tac", "aTargets": ['dt_id_po'], "mData": "dt_id_po" },
			{ "sClass": "w-100", "aTargets": ['dt_status'], "mData": "dt_status"},
			{ "sClass": "w-20pr", "aTargets": ['dt_prototype'], "mData": "dt_prototype"},
			{ "sClass": "w-20pr", "aTargets": ['dt_item'], "mData": "dt_item", "bSortable": false },
			{ "sClass": "tac", "aTargets": ['dt_buyer'], "mData": "dt_buyer", "bSortable": false },
			{ "sClass": "tac", "aTargets": ['dt_seller'], "mData": "dt_seller", "bSortable": false },
			{ "sClass": "w-80 tac", "aTargets": ['dt_quantity'], "mData": "dt_quantity" },
			{ "sClass": "tac mw-100 w-100", "aTargets": ['dt_price'], "mData": "dt_price" },
			{ "sClass": "mw-120 w-120 tac", "aTargets": ['dt_date_created'], "mData": "dt_date_created" },
			{ "sClass": "mw-120 w-120 tac", "aTargets": ['dt_date_changed'], "mData": "dt_date_changed" },
		],
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			if(!poFilters){
				poFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':false,
					callBack: function(){
						dtPo.fnDraw();
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

			aoData = aoData.concat(poFilters.getDTFilter());
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
				$("#dtPo tbody *").highlight(keywordsSearch, "highlight");

			$('.rating-bootstrap').rating();
		}
	});

	$('body').on('click', 'a[rel=log_details]', function() {
		var $thisBtn = $(this);
	    var nTr = $thisBtn.parents('tr')[0];
	    if (dtPo.fnIsOpen(nTr))
			dtPo.fnClose(nTr); // This row is already open - close it
	    else
			dtPo.fnOpen(nTr, fnFormatDetails(nTr), 'details');// Open this row

		$thisBtn.toggleClass('ep-icon_plus ep-icon_minus');
	});

	idStartItemNew = <?php echo $last_po_id;?>;
	startCheckAdminNewItems('po/ajax_po_operation/check_new', idStartItemNew);
});
</script>
<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr">PO</div>
		<ul class="menu-level3 mb-10 clearfix">
			<li class="active"><a class="dt_filter" data-name="status" data-title="Status" data-value="" data-value-text="All" href="#">All</a></li>
			<li><a class="dt_filter" data-name="status" data-value="initiated" data-title="Status" data-value-text="Initiated" href="#">Initiated (<?php echo intval($statuses['initiated']['counter']);?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-value="po_processing" data-title="Status" data-value-text="In process" href="#">In process (<?php echo intval($statuses['po_processing']['counter']);?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-value="prototype_confirmed" data-title="Status" data-value-text="Prototype confirmed" href="#">Prototype confirmed (<?php echo intval($statuses['prototype_confirmed']['counter']);?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-value="order_initiated" data-title="Status" data-value-text="Order initiated" href="#">Order initiated (<?php echo intval($statuses['order_initiated']['counter']);?>)</a></li>
			<li><a class="dt_filter" data-name="status" data-value="declined" data-title="Status" data-value-text="Declined" href="#">Declined (<?php echo intval($statuses['declined']['counter']);?>)</a></li>
		</ul>

		<?php tmvc::instance()->controller->view->display('admin/po/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtPo" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					 <th class="dt_id_po">#</th>
					 <th class="dt_status">Status</th>
					 <th class="dt_prototype">Prototype</th>
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
