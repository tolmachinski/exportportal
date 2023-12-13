<script>
	var delete_library_file = function(opener){
		var $this = $(opener);
		var checked_file = $this.data('id');
		$.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL ?>directory/ajax_company_library_operation/delete",
			dataType: "JSON",
			data: {id: checked_file},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type != 'error')
					dtDocList.fnDraw();
			}
		});
	}

	var delete_library_files = function(){
		var checked_files = '';
		$.each($('.check-document:checked'), function(){
			checked_files += $(this).data('id-document') + ',';
		});
		checked_files = checked_files.substring(0, checked_files.length - 1);
		$.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL ?>directory/ajax_company_library_operation/delete",
			dataType: "JSON",
			data: {id: checked_files},
			success: function(data) {
			    systemMessages(data.message, 'message-' + data.mess_type);
			    if (data.mess_type != 'error')
					dtDocList.fnDraw();
			}
		});
	}

	var dtDocList;
	$(document).ready(function(){
	var myFilters;


	dtDocList = $('#dtDocList').dataTable({
	    "bProcessing": true,
	    "bServerSide": true,
	    "sAjaxSource": "<?php echo __SITE_URL; ?>directory/ajax_library_list_dt",
	    "aoColumnDefs": [
			{"sClass": "w-50 vam tac", "aTargets": ['id_dt'], "mData": "id", "bSortable": false},
			{"sClass": "w-140 vam tac", "aTargets": ['type_dt'], "mData": "type"},
			{"sClass": "w-80 vam tac", "aTargets": ['access_dt'], "mData": "access"},
			{"sClass": "w-300 tac vam", "aTargets": ['company_dt'], "mData": "company"},
			{"sClass": "w-100 tac vat", "aTargets": ['user_dt'], "mData": "user"},
			{"sClass": "vat", "aTargets": ['title_dt'], "mData": "title"},
			{"sClass": "vat", "aTargets": ['description_dt'], "mData": "description"},
			{"sClass": "w-150 tac vam", "aTargets": ['added_dt'], "mData": "added"},
			{"sClass": "w-60 tac", "aTargets": ['actions_dt'], "mData": "actions", "bSortable": false}
	    ],
	    "sPaginationType": "full_numbers",
	    "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
	    "sorting": [[7, "desc"]],
	    "fnServerData": function(sSource, aoData, fnCallback) {
		if (!myFilters) {
		    myFilters = $('.dt_filter').dtFilters('.dt_filter',{
				'container': '.wr-filter-list',
				callBack: function() {
					dtDocList.fnDraw();
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

	$('.check-all-document').on('click', function() {
	    if ($(this).prop("checked")) {
			$('.check-document').prop("checked", true);
			$('.btns-actions-all').show();
	    } else {
			$('.check-document').prop("checked", false);
			$('.btns-actions-all').hide();
	    }
	});

	$('body').on('click', '.check-document', function() {
	    if ($(this).prop("checked")) {
			$('.btns-actions-all').show();
	    }else {
			var hideBlock = true;
			$('.check-document').each(function() {
				if ($(this).prop("checked")) {
					hideBlock = false;
					return false;
				}
			})
			if (hideBlock)
				$('.btns-actions-all').hide();
	    }
	});

	idStartItemNew = <?php echo $last_libraries_id;?>;
	startCheckAdminNewItems('directory/ajax_company_library_operation/check_new', idStartItemNew);
})
</script>

<div class="row">
    <div class="col-xs-12">
	<div class="titlehdr h-30">
	    <span>Documents list</span>
	    <div class="pull-right btns-actions-all display-n">
			<a class="ep-icon ep-icon_remove txt-red pull-right confirm-dialog" data-callback="delete_library_files" data-message="Are you sure want delete selected documents?" title="Delete document"></a>
	    </div>
	</div>

	<?php tmvc::instance()->controller->view->display('admin/directory/library/filter_panel'); ?>

		<div class="wr-filter-list mt-10 clearfix"></div>

        <table id="dtDocList" class="data table-bordered table-striped w-100pr">
			<thead>
			<tr>
				<th class="id_dt"><input type="checkbox" class="check-all-document pull-left">#</th>
				<th class="type_dt">Type</th>
				<th class="access_dt">Access</th>
				<th class="company_dt">Company</th>
				<th class="user_dt">Seller info</th>
				<th class="title_dt">Title</th>
				<th class="description_dt">Document description</th>
				<th class="added_dt">Added date</th>
				<th class="actions_dt">Actions</th>
			</tr>
			</thead>
			<tbody class="tabMessage" id="pageall"></tbody>
		</table>
    </div>
</div>
