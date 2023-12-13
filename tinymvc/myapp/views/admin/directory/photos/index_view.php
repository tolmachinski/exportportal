<script>
	var myFilters;
	var dtPhotosList;

	var moderate_photo = function(opener){
		var $this = $(opener);
		var checked_photo = $this.data('id');
		$.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL ?>directory/ajax_company_photo_operation/moderate",
			dataType: "JSON",
			data: {id: checked_photo},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type != 'error')
					dtPhotosList.fnDraw();

			}
		});
	}

	var moderate_photos = function(){
		var checked_photo = '';
		$.each($('.check-photo:checked'), function(){
			checked_photo += $(this).data('id-photo') + ',';
		});
		checked_photo = checked_photo.substring(0, checked_photo.length - 1);

		$.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL ?>directory/ajax_company_photo_operation/moderate",
			dataType: "JSON",
			data: {id: checked_photo},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type != 'error')
					dtPhotosList.fnDraw();

			}
		});
	}

	var delete_photo = function(opener){
		var $this = $(opener);
		var checked_photo = $this.data('id');

		$.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL ?>directory/ajax_company_photo_operation/delete",
			dataType: "JSON",
			data: {id: checked_photo},
			success: function(data) {
			    systemMessages(data.message, 'message-' + data.mess_type);
			    if (data.mess_type != 'error')
					dtPhotosList.fnDraw();
			}
		});
	}

	var delete_photos = function(){
		var $this = $(opener);
		var checked_photo = '';
		$.each($('.check-photo:checked'), function(){
			checked_photo += $(this).data('id-photo') + ',';
		});
		checked_photo = checked_photo.substring(0, checked_photo.length - 1);
		$.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL ?>directory/ajax_company_photo_operation/delete",
			dataType: "JSON",
			data: {id: checked_photo},
			success: function(data) {
			    systemMessages(data.message, 'message-' + data.mess_type);
			    if (data.mess_type != 'error')
					dtPhotosList.fnDraw();
			}
		});
	}


$(document).ready(function(){
	function fnFormatDetails(nTr) {
	    var aData = dtPhotosList.fnGetData(nTr);

	    var sOut = '<div class="dt-details"><table class="dt-details__table">';
	    sOut += '<tr><td class="w-100">Title' +
		    '<td><p>' + aData['full_title'] +
		    '</p></td>' +
		    '</tr>';
	    sOut += '<tr><td class="w-100">Description' +
		    '<td><p>' + aData['full_description'] +
		    '</p></td>' +
		    '</tr>';
	    sOut += '</table></div>';
	    return sOut;
	}

	dtPhotosList = $('#dtPhotosList').dataTable({
	    "bProcessing": true,
	    "bServerSide": true,
	    "sAjaxSource": "<?php echo __SITE_URL; ?>directory/ajax_photo_list_dt",
	    "aoColumnDefs": [
		{"sClass": "w-50 vam tac", "aTargets": ['id_dt'], "mData": "id", "bSortable": false},
		{"sClass": "w-140 vam tac", "aTargets": ['photo_dt'], "mData": "photo", "bSortable": false},
		{"sClass": "w-300 tac vam", "aTargets": ['company_dt'], "mData": "company"},
		{"sClass": "w-100 tac vat", "aTargets": ['user_dt'], "mData": "user"},
		{"sClass": "w-200 vat", "aTargets": ['title_dt'], "mData": "title"},
		{"sClass": "vat", "aTargets": ['description_dt'], "mData": "description"},
		{"sClass": "w-80 tac vam", "aTargets": ['added_dt'], "mData": "added"},
		{"sClass": "tac vam w-60", "aTargets": ['comments_dt'], "mData": "comments"},
		{"sClass": "w-60 tac vam", "aTargets": ['actions_dt'], "mData": "actions", "bSortable": false}
	    ],
	    "sPaginationType": "full_numbers",
	    "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
	    "sorting": [[6, "desc"]],
	    "fnServerData": function(sSource, aoData, fnCallback) {
		if (!myFilters) {
		    myFilters = $('.dt_filter').dtFilters('.dt_filter',{
				'container': '.wr-filter-list',
				callBack: function() {
					dtPhotosList.fnDraw();
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

	$('body').on('click', 'a[rel=photo_details]', function() {
		var $thisBtn = $(this);
	    var nTr = $thisBtn.parents('tr')[0];
	    if (dtPhotosList.fnIsOpen(nTr))
			dtPhotosList.fnClose(nTr);
	    else
			dtPhotosList.fnOpen(nTr, fnFormatDetails(nTr), 'details');

		$thisBtn.toggleClass('ep-icon_plus ep-icon_minus');
	});

	$('.check-all-photos').on('click', function() {
	    if ($(this).prop("checked")) {
			$('.check-photo').prop("checked", true);
			$('.btns-actions-all').show();
	    }else {
			$('.check-photo').prop("checked", false);
			$('.btns-actions-all').hide();
	    }
	});

	$('body').on('click', '.check-photo', function() {
	    if ($(this).prop("checked")) {
			$('.btns-actions-all').show();
	    }else {
			var hideBlock = true;
			$('.check-photo').each(function() {
				if ($(this).prop("checked")) {
					hideBlock = false;
					return false;
				}
			})
			if (hideBlock)
				$('.btns-actions-all').hide();
	    }
	});

	idStartItemNew = <?php echo $last_photos_id;?>;
	startCheckAdminNewItems('directory/ajax_company_photo_operation/check_new', idStartItemNew);
 });
</script>
<div class="row">
    <div class="col-xs-12">
	<div class="titlehdr h-30">
	    <span>Photos list</span>
	    <div class="pull-right btns-actions-all display-n">
			<a class="ep-icon ep-icon_remove txt-red pull-right confirm-dialog" data-callback="delete_photos" data-message="Are you sure want to delete selected photos?" title="Delete photos"></a>
			<a class="ep-icon ep-icon_sheild-ok txt-green mr-5 pull-right confirm-dialog" data-callback="moderate_photos" title="Moderate photos" data-message="Are you sure want to moderate selected photos?"></a>
	    </div>
	</div>

	<?php tmvc::instance()->controller->view->display('admin/directory/photos/filter_panel'); ?>

		<div class="wr-filter-list mt-10 clearfix"></div>

		<table id="dtPhotosList" class="data table-striped table-bordered w-100pr">
			<thead>
			<tr>
				<th class="id_dt"><input type="checkbox" class="check-all-photos pull-left">#</th>
				<th class="photo_dt">Photo</th>
				<th class="company_dt">Company</th>
				<th class="user_dt">Seller info</th>
				<th class="title_dt">Title</th>
				<th class="description_dt">Description</th>
				<th class="added_dt">Added</th>
				<th class="comments_dt"><span title="Comments">C</span></th>
				<th class="actions_dt">Actions</th>
			</tr>
			</thead>
			<tbody class="tabMessage" id="pageall"></tbody>
		</table>
    </div>
</div>
