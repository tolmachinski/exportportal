<script>
	var dtItemsQuestionsList;

$(document).ready(function() {
	var myFilters;
	var block;

	function fnFormatDetails(nTr) {
	    var aData = dtItemsQuestionsList.fnGetData(nTr);

	    var sOut = '<div class="dt-details"><table class="dt-details__table">';
	    sOut += '<tr><td class="w-100">Question</td>' +
		    '<td><p class="mb-10"><strong>' + aData['full_title'] +
		    '</strong></p><p class="mb-10">' + aData['full_text'] +
		    '</p><p class="mb-10">' + aData['ques_date'] +
		    '</p><p>' +
		    '</p></td>' +
		    '</tr>';
	    sOut += '<tr><td>Reply</td>' +
		    '<td><p class="mb-10">' + aData['full_reply'] +
		    '</p><p>' + aData['reply_date'] +
		    '</p></td></tr>';
	    sOut += '</table></div>';
	    return sOut;
	}

	dtItemsQuestionsList = $('#dtItemsQuestionsList').dataTable({
	    "bProcessing": true,
	    "bServerSide": true,
	    "sAjaxSource": "<?php echo __SITE_URL; ?>items_questions/ajax_list_admin_dt",
	    "aoColumnDefs": [
		{"sClass": "w-50 tac", "aTargets": ['id_dt'], "mData": "id", "bSortable": false},
		{"sClass": "w-130 tac", "aTargets": ['item_dt'], "mData": "item"},
		{"sClass": "w-100 tac", "aTargets": ['author_dt'], "mData": "author"},
		{"sClass": "w-100 vam tac", "aTargets": ['category_dt'], "mData": "category"},
		{"sClass": "w-100 tac", "aTargets": ['seller_dt'], "mData": "seller"},
		{"sClass": "w-200 vam", "aTargets": ['title_dt'], "mData": "title"},
		{"sClass": "vam", "aTargets": ['text_dt'], "mData": "text"},
		{"sClass": "vam", "aTargets": ['answer_dt'], "mData": "answer"},
		{"sClass": "w-70 tac vam", "aTargets": ['created_dt'], "mData": "ques_date"},
		{"sClass": "w-50 tt-c", "aTargets": ['status_dt'], "mData": "status"},
		{"sClass": "w-40 tac vam", "aTargets": ['plus_dt'], "mData": "plus"},
		{"sClass": "w-40 tac vam", "aTargets": ['minus_dt'], "mData": "minus"},
		{"sClass": "w-60 tac", "aTargets": ['actions_dt'], "mData": "actions", "bSortable": false}
	    ],
	    "sPaginationType": "full_numbers",
	    "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
	    "sorting": [[8, "desc"]],
	    "fnServerData": function(sSource, aoData, fnCallback) {
		if(!myFilters){
		    myFilters = $('.dt_filter').dtFilters('.dt_filter',{
				'container': '.wr-filter-list',
				callBack: function() {
					dtItemsQuestionsList.fnDraw();
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
		"fnDrawCallback": function(oSettings) {}
	});

	$('body').on('click', 'a[rel=review_details]', function() {
		var $thisBtn = $(this);
	    var nTr = $thisBtn.parents('tr')[0];
	    if (dtItemsQuestionsList.fnIsOpen(nTr))
			dtItemsQuestionsList.fnClose(nTr);
	    else
			dtItemsQuestionsList.fnOpen(nTr, fnFormatDetails(nTr), 'details');

		$thisBtn.toggleClass('ep-icon_minus').toggleClass('ep-icon_plus');
	});

	$('.check-all-questions').on('click', function() {
	    if ($(this).prop("checked")) {
			$('.check-question').prop("checked", true);
			$('.btns-actions-all').show();
	    }else {
			$('.check-question').prop("checked", false);
			$('.btns-actions-all').hide();
	    }
	});

	$('body').on('click', '.check-question', function() {
	    if ($(this).prop("checked")) {
			$('.btns-actions-all').show();
	    }else {
			var hideBlock = true;
			$('.check-question').each(function() {
				if ($(this).prop("checked")) {
					hideBlock = false;
					return false;
				}
			});
			if (hideBlock)
				$('.btns-actions-all').hide();
	    }
	});

	idStartItemNew = <?php echo $last_items_questions_id;?>;
	startCheckAdminNewItems('items_questions/ajax_question_operation/check_new', idStartItemNew);
});

var moderate_question = function(opener){
	var btn = $(opener);
	var checked_question = btn.data('question');
	$.ajax({
		type: 'POST',
		url: "<?php echo __SITE_URL ?>items_questions/ajax_questions_administration_operation/moderate",
		dataType: "JSON",
		data: {question: checked_question},
		success: function(data) {
			systemMessages(data.message, 'message-' + data.mess_type);
			if (data.mess_type != 'error')
				dtItemsQuestionsList.fnDraw();

		}
	});
}


var moderate_questions = function(opener){
	var btn = $(opener);
	var checked_questions = [];
	$(".check-question:checked").each(function() {
		checked_questions.push($(this).data('id-question'));
	});

	$.ajax({
		type: 'POST',
		url: "<?php echo __SITE_URL ?>items_questions/ajax_questions_administration_operation/moderate_questions",
		dataType: "JSON",
		data: {checked_questions: checked_questions},
		success: function(data) {
			systemMessages(data.message, 'message-' + data.mess_type);
			if (data.mess_type != 'error')
				dtItemsQuestionsList.fnDraw();

		}
	});
}

var delete_question = function(opener){
	var btn = $(opener);
	var checked_question = btn.data('question');

	$.ajax({
		type: 'POST',
		url: "<?php echo __SITE_URL ?>items_questions/ajax_questions_administration_operation/delete",
		dataType: "JSON",
		data: {question: checked_question},
		success: function(data) {
			systemMessages(data.message, 'message-' + data.mess_type);
			if (data.mess_type != 'error')
				dtItemsQuestionsList.fnDraw();
		}
	});
}

var delete_questions = function(){
	$this = $(this);
	var checked_questions = [];
	$.each($(".check-question:checked"), function() {
		checked_questions.push($(this).data('id-question'));
	});

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL ?>items_questions/ajax_questions_administration_operation/delete_questions',
		dataType: "JSON",
		data: {checked_questions: checked_questions},
		success: function(data) {
			systemMessages(data.message, 'message-' + data.mess_type);
			if (data.mess_type != 'error') {
				dtItemsQuestionsList.fnDraw();
				$('.check-all-questions').prop("checked", false);
			}

		}
	});
}

</script>
<div class="row">
    <div class="col-xs-12">
	<div class="titlehdr h-30">
	    <span>Items questions list</span>
	    <div class="pull-right btns-actions-all display-n">
		<a class="ep-icon ep-icon_remove txt-red pull-right confirm-dialog" data-message="Are you sure want delete selected questions?" data-callback="delete_questions" title="Delete questions"></a>
		<a class="ep-icon ep-icon_sheild-ok txt-green pull-right confirm-dialog" data-callback="moderate_questions" data-message="Are you sure want moderate selected questions?" title="Moderate questions"></a>
	    </div>
	</div>

	<?php tmvc::instance()->controller->view->display('admin/items_questions/filter_panel_view'); ?>

	<div class="mt-10 wr-filter-list clearfix"></div>

    <table id="dtItemsQuestionsList" class="data table-bordered table-striped w-100pr">
	    <thead>
		<tr>
		    <th class="id_dt"><input type="checkbox" class="check-all-questions pull-left">#</th>
		    <th class="item_dt">Item</th>
		    <th class="author_dt">Author</th>
		    <th class="seller_dt">Seller</th>
		    <th class="category_dt">Category</th>
		    <th class="title_dt">Title</th>
		    <th class="text_dt">Text</th>
		    <th class="answer_dt">Reply</th>
		    <th class="created_dt tal">Created</th>
		    <th class="status_dt">Status</th>
		    <th class="plus_dt"><a class="ep-icon ep-icon_star-plus" title="Count plus"></a></th>
		    <th class="minus_dt"><a class="ep-icon ep-icon_star-minus" title="Count minus"></a></th>
		    <th class="actions_dt">Actions</th>
		</tr>
	    </thead>
	    <tbody class="tabMessage" id="pageall"></tbody>
	</table>
    </div>
</div>
