<script type="text/javascript">
var b2bRequests, b2bFilter;
$(document).ready(function(){
    var unblockResource = function (caller) {
        var button = $(caller);
        var url = button.data('url') || null;
        var onRequestSuccess = function(resposne) {
            systemMessages(resposne.message, resposne.mess_type);
            if(resposne.mess_type === 'success') {
                b2bRequests.fnDraw();
            }
        }

        if(null !== url) {
            $.post(url, null, null, 'json').done(onRequestSuccess).fail(onRequestError);
        }
    };

	var onResourceBlock = function() {
		b2bRequests.fnDraw(false);
	};

	change_visible = function(obj){
		var $this = $(obj);
		var id = $this.data('id');
		var change_to = $this.data('change-to');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>b2b/ajax_b2b_operation/admin_change_request_status',
			data: {
				id: id,
				change_to: change_to
			},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					b2bRequests.fnDraw(false);
				}
			}
		});
	}

	change_blocked = function(obj){
		var $this = $(obj);
		var id = $this.data('id');
		var change_to = $this.data('change-to');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>b2b/ajax_b2b_operation/admin_change_request_blocked',
			data: {
				id: id,
				change_to: change_to
			},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					b2bRequests.fnDraw(false);
				}
			}
		});
	}

	b2bRequests = $('#b2bRequests').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>b2b/ajax_requests_administration",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-60 tac", "aTargets": ['dt_id'], "mData": "dt_id" },
			{ "sClass": "tac", "aTargets": ['dt_title'], "mData": "dt_title" },
			{ "sClass": "tac w-250", "aTargets": ['dt_created_at'], "mData": "dt_created_at" },
			{ "sClass": "w-350", "aTargets": ['dt_company'], "mData": "dt_company" },
			{ "sClass": "w-350", "aTargets": ['dt_locations'], "mData": "dt_locations", "bSortable": false },
			{ "sClass": "tac w-160", "aTargets": ['dt_zip'], "mData": "dt_zip", "bSortable": false  },
			{ "sClass": "tac w-120", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"sorting": [[0, "desc"]],
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {

		},
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			if(!b2bFilter){
				b2bFilter = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					callBack: function(){ b2bRequests.fnDraw(); },
				});
			}

			aoData = aoData.concat(b2bFilter.getDTFilter());
			$.ajax( {
				"dataType": 'JSON',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if(data.mess_type == 'error')
						systemMessages(data.message, 'message-' + data.mess_type);
					if(data.mess_type == 'info')
						systemMessages(data.message, 'message-' + data.mess_type);

					fnCallback(data, textStatus, jqXHR);

				}
			});
		},
	});

	$('body').on('click', 'a[rel=view_details]', function() {
		var $thisBtn = $(this);
		var nTr = $thisBtn.parents('tr')[0];

		if (b2bRequests.fnIsOpen(nTr))
			b2bRequests.fnClose(nTr);
		else
			b2bRequests.fnOpen(nTr, fnFormatDetails(nTr), 'details');

		$thisBtn.toggleClass('ep-icon_plus ep-icon_minus');
	});

	mix(window, {
		unblockResource: unblockResource,
		onResourceBlock: onResourceBlock,
	});
});

function fnFormatDetails(nTr) {
	var aData = b2bRequests.fnGetData(nTr);

	var sOut = '<div class="dt-details"><table class="dt-details__table">';
	sOut += '<tr><td class="w-200">Views count:</td><td>' + aData['dt_views'] + '</td></tr>';
	sOut += '<tr><td class="w-200">Radius:</td><td>' + aData['dt_radius'] + '</td></tr>';
	sOut += '<tr><td class="w-200">Message:</td><td>' + aData['dt_message'] + '</td></tr>';
	sOut += '<tr><td class="w-200">Tags:</td><td>' + aData['dt_tags'] + '</td></tr>';

	sOut += '</table></div>';
	return sOut;
}
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30"><span>B2B Requests</span></div>

		<?php tmvc::instance()->controller->view->display('admin/b2b/request/filter_panel_view'); ?>

		<div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="b2bRequests" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					<th class="dt_id">#</th>
					<th class="dt_company">Company</th>
					<th class="dt_title">Title</th>
					<th class="dt_created_at">Date create</th>
					<th class="dt_locations">Search in location</th>
					<th class="dt_zip">Zip</th>
					<th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>
