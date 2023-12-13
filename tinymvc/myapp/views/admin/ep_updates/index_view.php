<script type="text/javascript">
var epUpdatedFilters;
var dtEpUpdates;

function delete_ep_update_i18n(obj){
    var $this = $(obj);
    var id_ep_update = $this.data('ep-update-id');
    var ep_update_i18n_lang = $this.data('ep-update-i18n-lang');

    $.ajax({
        type: 'POST',
        url: '<?php echo __SITE_URL?>ep_updates/ajax_ep_updates_operations/delete_ep_update_i18n',
        data: {id_ep_update: id_ep_update, ep_update_i18n_lang: ep_update_i18n_lang},
        beforeSend: function(){ },
        dataType: 'json',
        success: function(resp){
            systemMessages( resp.message, 'message-' + resp.mess_type );
            if(resp.mess_type == 'success'){
                dtEpUpdates.fnDraw(false);
            }
        }
    });
}

$(document).ready(function(){
	remove_ep_update = function(obj){
		var $this = $(obj);//alert($this.data('column'));
		var ep_updates = $this.data('id');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>ep_updates/ajax_ep_updates_operations/remove_ep_update',
			data: {id: ep_updates},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );
				dtEpUpdates.fnDraw();

				if(data.mess_type == 'success'){ }
			}
		});
	}

	change_visible_ep_update = function(obj){
		var $this = $(obj);
		var ep_updates = $this.data("id");

		$.ajax({
			type: "POST",
			url: '<?php echo __SITE_URL?>ep_updates/ajax_ep_updates_operations/change_visible_ep_update',
			data: { id: ep_updates },
			dataType: 'JSON',
			success: function(resp){

				systemMessages( resp.message, 'message-' + resp.mess_type );
				dtEpUpdates.fnDraw();

				if(resp.mess_type == 'success'){
					$this.toggleClass('ep-icon_invisible ep-icon_visible');
				}

			}
		});
	}

	dtEpUpdates = $('#dtEpUpdates').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>ep_updates/ajax_ep_updates_administration/<?php echo $upload_folder;?>",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-40 tac", "aTargets": ['dt_id'], "mData": "dt_id" },
			{ "sClass": "vam", "aTargets": ['dt_title'], "mData": "dt_title" },
			{ "sClass": "w-60 tac", "aTargets": ['dt_content'], "mData": "dt_content", "bSortable": false  },
			{ "sClass": "w-150 tac", "aTargets": ['dt_description'], "mData": "dt_description", "bSortable": false },
			{ "sClass": "w-200 tac", "aTargets": ['dt_date_time'], "mData": "dt_date_time"},
            { "sClass": "w-150 tac", "aTargets": ['dt_tlangs_list'], "mData": "dt_tlangs_list", "bSortable": false  },
            { "sClass": "w-150 tac", "aTargets": ['dt_tlangs'], "mData": "dt_tlangs", "bSortable": false  },
			{ "sClass": "tac w-80", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"sorting": [[0, "desc"]],
		"fnServerData": function ( sSource, aoData, fnCallback ) {

			if(!epUpdatedFilters){
				epUpdatedFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':false,
					callBack: function(){ dtEpUpdates.fnDraw(); },
					onSet: function(callerObj, filterObj){

					},
					onDelete: function(filter){

					}
				});
			}

			aoData = aoData.concat(epUpdatedFilters.getDTFilter());
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
				$("#dtEpUpdates tbody *").highlight(keywordsSearch, "highlight");
		}
	});

	function fnFormatDetails(nTr) {
	    var aData = dtEpUpdates.fnGetData(nTr);

	    var sOut = '<div class="dt-details"><table class="dt-details__table">';
	    sOut += '<tr><td class="w-200">Description:</td><td>' + aData['dt_description'] + '</td></tr>'+
				'<tr><td>Content: </td><td>' + aData['dt_content'] + '</td></tr>';

	    sOut += '</table></div>';
	    return sOut;
	}

	$('body').on('click', 'a[rel=view_details]', function() {
		var $thisBtn = $(this);
		var nTr = $thisBtn.parents('tr')[0];

		if (dtEpUpdates.fnIsOpen(nTr))
			dtEpUpdates.fnClose(nTr);
		else
			dtEpUpdates.fnOpen(nTr, fnFormatDetails(nTr), 'details');

		$thisBtn.toggleClass('ep-icon_plus ep-icon_minus');
	});

});
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30"><span>EP updates</span> <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="ep_updates/popup_forms/add_ep_updates/<?php echo $upload_folder;?>" data-table="dtEpUpdates" data-title="Add EP updates"></a></div>

		<?php tmvc::instance()->controller->view->display('admin/ep_updates/filter_panel_view'); ?>
		<div class="wr-filter-list clearfix mt-10"></div>

		<table class="data table-striped table-bordered w-100pr" id="dtEpUpdates" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					<th class="dt_id">#</th>
					<th class="dt_title">Title</th>
					<th class="dt_date_time">Date</th>
					<th class="dt_tlangs_list">Translated in</th>
					<th class="dt_tlangs">Translate</th>
					<th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>
