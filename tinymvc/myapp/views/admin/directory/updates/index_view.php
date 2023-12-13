<script>
	var dtUpdatesList;

$(document).ready(function(){
	var myFilters;

	dtUpdatesList = $('#dtUpdatesList').dataTable({
	    "bProcessing": true,
	    "bServerSide": true,
	    "sAjaxSource": "<?php echo __SITE_URL; ?>directory/ajax_updates_list_dt",
	    "aoColumnDefs": [
		{"sClass": "w-50 vam tac", "aTargets": ['id_dt'], "mData": "id", "bSortable": false},
		{"sClass": "w-140 vam tac", "aTargets": ['photo_dt'], "mData": "photo", "bSortable": false},
		{"sClass": "w-300 tac vam", "aTargets": ['company_dt'], "mData": "company"},
		{"sClass": "w-100 tac vat", "aTargets": ['user_dt'], "mData": "user"},
		{"sClass": "vat", "aTargets": ['description_dt'], "mData": "description"},
		{"sClass": "w-150 tac vam", "aTargets": ['added_dt'], "mData": "added"},
		{"sClass": "w-70 tac vam", "aTargets": ['actions_dt'], "mData": "actions", "bSortable": false}
	    ],
	    "sPaginationType": "full_numbers",
	    "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
	    "sorting": [[5, "desc"]],
	    "fnServerData": function(sSource, aoData, fnCallback) {
		if (!myFilters) {
		    myFilters = $('.dt_filter').dtFilters('.dt_filter',{
				'container': '.wr-filter-list',
				callBack: function() {
					dtUpdatesList.fnDraw();
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

		aoData = aoData.concat(myFilters.getDTFilter());
		$.ajax({
		    "dataType": 'json',
		    "type": "POST",
		    "url": sSource,
		    "data": aoData,
		    "success": function(data, textStatus, jqXHR) {
			if (data.mess_type == 'error')
			    systemMessages(data.message, 'message-' + data.mess_type);

			fnCallback(data, textStatus, jqXHR);

		    }
		});
	    },
        "fnDrawCallback": function(oSettings) {
			$('.rating-bootstrap').rating();
		}
	});

	$('.check-all-updates').on('click', function() {
	    if ($(this).prop("checked")) {
			$('.check-update').prop("checked", true);
			$('.btns-actions-all').show();
	    }else {
			$('.check-update').prop("checked", false);
			$('.btns-actions-all').hide();
	    }
	});

	$('body').on('click', '.check-update', function() {
	    if ($(this).prop("checked")) {
			$('.btns-actions-all').show();
	    } else {
			var hideBlock = true;
			$('.check-update').each(function() {
				if ($(this).prop("checked")) {
					hideBlock = false;
					return false;
				}
			})
			if (hideBlock)
				$('.btns-actions-all').hide();
	    }
	});

	idStartItemNew = <?php echo $last_updates_id;?>;
	startCheckAdminNewItems('directory/ajax_company_updates_operation/check_new', idStartItemNew);
});

	var delete_update_image = function(opener){
		var $this = $(opener);
		var dataUpdate = $this.data('update');
		$.ajax({
			type: "POST",
			url: "directory/ajax_company_updates_operation/delete_image",
			data: { update: dataUpdate },
			dataType: "JSON",
			success: function(resp){
				if(resp.mess_type == 'success'){
					$this.closest('td').fadeOut('slow', function(){$(this).remove();});
					dtUpdatesList.fnDraw();
				}
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		});
	}

	var remove_seller_update = function(obj){
		var $this = $(obj);
		var update = new Array();
		update.push($this.data("update"));

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>directory/ajax_company_updates_operation/delete',
			data: { update : update},
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					dtUpdatesList.fnDraw();
				}
			},
            error: function(data) {
                if (data.responseText) {
                    var message = $(data.responseText).find('li').text();
                    systemMessages( message, 'message-' + 'error' );
                }
            }
		});
	}

	var remove_seller_updates = function(obj){
		var $this = $(obj);
		var updates = new Array();

		$.each($('.check-update:checked'), function(){
			if($(this).is(":checked"))
				updates.push($(this).data("id-update"));
		});

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>directory/ajax_company_updates_operation/delete',
			data: { update : updates},
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					dtUpdatesList.fnDraw();
					$('.btns-actions-all').hide();
				}
			}
		});
	}
</script>
<div class="row">
    <div class="col-xs-12">
	<div class="titlehdr h-30">
	    <span>Updates list</span>
	    <div class="pull-right btns-actions-all display-n">
			<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_seller_updates" data-message="Are you sure what delete these updates?" title="Delete updates"></a>
	    </div>
	</div>

	<?php tmvc::instance()->controller->view->display('admin/directory/updates/filter_panel'); ?>

		<div class="wr-filter-list mt-10 clearfix"></div>

		<table id="dtUpdatesList" class="data table-striped table-bordered w-100pr">
			<thead>
			<tr>
				<th class="id_dt"><input type="checkbox" class="check-all-updates pull-left">#</th>
				<th class="photo_dt">Photo</th>
				<th class="company_dt">Company</th>
				<th class="user_dt">Seller info</th>
				<th class="description_dt">Update description</th>
				<th class="added_dt">Added date</th>
				<th class="actions_dt">Actions</th>
			</tr>
			</thead>
			<tbody class="tabMessage" id="pageall"></tbody>
		</table>
    </div>
</div>
