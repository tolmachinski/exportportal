<script>
	var dtOrdersQuotes, myFilters;
	$(document).ready(function(){
		if(($('.main-data-table').length > 0) && ($(window).width() < 768)){
			$('.main-data-table').addClass('main-data-table--mobile');
		}

		// QUOTE REQUEST responses COMMENT BTN
		$('#dtOrdersQuotes').on('click', 'a[rel=quote_details]', function() {
			var $aTd = $(this);
			var nTr = $aTd.parents('tr')[0];

			if (dtOrdersQuotes.fnIsOpen(nTr))
				dtOrdersQuotes.fnClose(nTr);
			else
				dtOrdersQuotes.fnOpen(nTr, fnFormatDetails(nTr), 'details');

			$aTd.toggleClass('ep-icon_plus ep-icon_minus');
			$.fancybox.reposition();
		});

		if (myFilters) {
			myFilters.reInit();
		}
		dtOrdersQuotes = $('#dtOrdersQuotes').dataTable({
			"sDom": '<"top"i>rt<"bottom"lp><"clear">',
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "<?php echo __SITE_URL;?>shippers/ajax_shipping_requests_list_dt/<?php echo $id_order;?>",
			"aoColumnDefs": [
				{"sClass": "w-50 tac vam", "aTargets": ['dt_id'], "mData": "dt_id"},
				{"sClass": "w-80 tac vam dn-xl", "aTargets": ['dt_shipper_logo'], "mData": "dt_shipper_logo", "bSortable": false},
				{"sClass": "vam", "aTargets": ['dt_shipper_name'], "mData": "dt_shipper_name"},
				{"sClass": "w-150 tac vam", "aTargets": ['dt_shipping_price'], "mData": "dt_shipping_price"},
				{"sClass": "w-65 tac vam", "aTargets": ['dt_delivery_time'], "mData": "dt_delivery_time"},
				{"sClass": "w-200 tac vam", "aTargets": ['dt_countdown'], "mData": "dt_countdown"},
				{"sClass": "w-100 tac vam dn-xl", "aTargets": ['dt_create_date'], "mData": "dt_create_date"},
				{"sClass": "w-100 tac vam", "aTargets": ['dt_status'], "mData": "dt_status"},
				{"sClass": "w-65 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
			],
			"language": {
				"paginate": {
					"first": "<i class='ep-icon ep-icon_arrow-left'></i>",
					"previous": "<i class='ep-icon ep-icon_arrows-left'></i>",
					"next": "<i class='ep-icon ep-icon_arrows-right'></i>",
					"last": "<i class='ep-icon ep-icon_arrow-right'></i>"
				}
			},
			"fnServerData": function(sSource, aoData, fnCallback) {
				if (!myFilters) {
					myFilters = $('.dt_filter').dtFilters('.dt_filter',{
						'container': '.filter-container',
						callBack: function() {
							dtOrdersQuotes.fnDraw();
						},
						onSet: function(callerObj, filterObj) {
						},
						onDelete: function(filterObj){
						}
					});
				}

				aoData = aoData.concat(myFilters.getDTFilter());

				$.ajax({
					"dataType": 'json',
					"type": "POST",
					"url": sSource,
					"data": aoData,
					"success": function(data, textStatus, jqXHR) {
					if (data.mess_type == 'error')
						systemMessages(data.message, data.mess_type);

					fnCallback(data, textStatus, jqXHR);

					}
				});
			},
			"sorting" : [[7,'asc']],
			"sPaginationType": "full_numbers",
			"fnDrawCallback": function(oSettings) {
				hideDTbottom(this);
				mobileDataTable($('.main-data-table'));

				$.each($('.order-status'), function(){
					var $this = $(this);
					var expire = $this.data('expire');

					$this.countdown(expire, function(event) {
						var format_clock = '<div class="txt-green">%D days %H hours %M min</div>';
						if(expire < 7200000){
							format_clock = '<div class="txt-red">%D days %H hours %M min</div>';
						}
						$(this).html(event.strftime(format_clock));
					}).on('finish.countdown', function(event) {
						$(this).html('<div class="txt-red">The time for this status has expired!</div>');
					});
				});
				$.fancybox.reposition();
			}
		});
	});

	change_status = function(obj){
		var $this = $(obj);
		var id_request = $this.data('request');

		$.ajax({
			type: 'POST',
			url: '<?php echo getUrlForGroup('shippers/ajax_shippers_operation/decline_quote_request');?>',
			data: { id_request : id_request},
			beforeSend: function(){
				showLoader('.dataTables_wrapper');
			},
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, data.mess_type );

				if(data.mess_type == 'success'){
					dtOrdersQuotes.fnDraw();
				}

				hideLoader('.dataTables_wrapper');
			}
		});
	}

	function fnFormatDetails(nTr){
		var aData = dtOrdersQuotes.fnGetData(nTr);
		var sOut = '<div class="dt-details"><table class="dt-details__table">';
			sOut += '<tr><td class="w-130">Seller comment:</td><td>' + aData['dt_comment_user'] +'</td></tr>';
			sOut += '<tr><td class="w-130">Freight Forwarder comment:</td><td>' + aData['dt_comment_shipper'] +'</td></tr>';
			sOut += '</table> </div>';
		return sOut;
	}
</script>
<div class="js-modal-flex wr-modal-flex inputs-40">
	<div class="modal-flex__content mh-700">
		<div class="mt-10 filter-container clearfix"></div>
		<div class="dataTables_wrapper">
			<table id="dtOrdersQuotes" class="main-data-table">
				<thead>
				<tr>
					<th class="dt_id">#</th>
					<th class="dt_shipper_logo">Logo</th>
					<th class="dt_shipper_name">Company name</th>
					<th class="dt_shipping_price">Price</th>
					<th class="dt_delivery_time">Time</th>
					<th class="dt_create_date">Request created</th>
					<th class="dt_countdown vam tac">Request countdown</th>
					<th class="dt_status">Status</th>
					<th class="dt_actions"></th>
				</tr>
				</thead>
				<tbody class="tabMessage"></tbody>
			</table>
		</div>
	</div>
</div>
