<script>
	var dtCommentsList;
	var $checked_comments = [];

	var moderate_comment = function(opener){
		var $this = $(opener);
		$checked_comments = [];
		$checked_comments.push($this.data('comment'));

		moderate_comments_callback();
	}

	var moderate_comments = function(){
		$checked_comments = [];
		$(".check-comment:checked").each(function() {
			$checked_comments.push($(this).data('comment'));
		});

		moderate_comments_callback();
	}

	function moderate_comments_callback(){
		if ($checked_comments.length == 0) {
			systemMessages('Error: There are no comment(s) to be moderated.', 'message-error');
			return false;
		}

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>community_questions/ajax_comments_operation/moderate',
			dataType: "JSON",
			data: {comment: $checked_comments.join(',')},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type != 'error') {
					dtCommentsList.fnDraw();
				}
			}
		});
	}

	var delete_comment = function(opener){
		var $this = $(opener);
		$checked_comments = [];
		$checked_comments.push($this.data('comment'));

		delete_comments_callback();
	}

	var delete_comments = function(opener){
		$checked_comments = [];
		$(".check-comment:checked").each(function() {
			$checked_comments.push($(this).data('comment'));
		});

		delete_comments_callback();
	}

	function delete_comments_callback(){
		if ($checked_comments.length == 0) {
			systemMessages('Error: There are no comment(s) to be deleted.', 'message-error');
			return false;
		}

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>community_questions/ajax_comments_operation/delete_multiple',
			dataType: "JSON",
			data: {comment: $checked_comments.join(',')},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type != 'error') {
					dtCommentsList.fnDraw();
				}
			}
		});
	}

$(document).ready(function() {
	var myFilters;
	var block = "";
	dtCommentsList = $('#dtCommentsList').dataTable({
	    "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
	    "bProcessing": true,
	    "bServerSide": true,
	    "sAjaxSource": "<?php echo __SITE_URL; ?>community_questions/ajax_administration_dt/comments",
	    "sServerMethod": "POST",
	    "sorting": [[4, "desc"]],
	    "aoColumnDefs": [
			{"sClass": "w-60 tac vam",  "aTargets": ['dt_id'], "mData": "dt_id", 'bSortable': false},
			{"sClass": "w-200", "aTargets": ['dt_author'], "mData": "dt_author", 'bSortable': false},
			{"sClass": "",  "aTargets": ['dt_text'], "mData": "dt_text", 'bSortable': false},
			{"sClass": "w-300", "aTargets": ['dt_answer'], "mData": "dt_answer",},
			{"sClass": "w-150 tac vam", "aTargets": ['dt_date'], "mData": "dt_date",},
			{"sClass": "w-100 tac vam",  "aTargets": ['dt_actions'], "mData": "dt_actions", 'bSortable': false},
	    ],
	    "fnServerData": function(sSource, aoData, fnCallback) {
			if (!myFilters) {
				myFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					callBack: function() {
						dtCommentsList.fnDraw();
					},
					onSet: function(callerObj, filterObj) {
						if (filterObj.name == 'start_date') {
							$("#finish_date").datepicker("option", "minDate", $("#start_date").datepicker("getDate"));
						}
						if (filterObj.name == 'finish_date') {
							$("#start_date").datepicker("option", "minDate", $("#finish_date").datepicker("getDate"));
						}
					},
					onDelete: function(callerObj, filterObj) {
						if (filterObj.name == 'start_date') {
							$("#finish_date").datepicker("option", "minDate", null);
						}
						if (filterObj.name == 'finish_date') {
							$("#start_date").datepicker("option", "minDate", null);
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
	    "sPaginationType": "full_numbers",
	    "fnDrawCallback": function(oSettings) {}
	});
	$('div.dataTables_filter input').addClass('search-comment');

	$('.check-all-comments').on('click', function(e) {
	    if ($(this).prop("checked")) {
			$('.check-comment').prop("checked", true);
			$('.btns-actions-all').show();
	    }else {
			$('.check-comment').prop("checked", false);
			$('.btns-actions-all').hide();
	    }
	});

	$('body').on('click', '.check-comment', function() {
	    if ($(this).prop("checked")) {
			$('.btns-actions-all').show();
	    } else {
			var hideBlock = true;
			$('.check-comment').each(function() {
				if ($(this).prop("checked")) {
					hideBlock = false;
					return false;
				}
			})
			if (hideBlock)
				$('.btns-actions-all').hide();
	    }
	});

	idStartItemNew = <?php echo $last_comments_id;?>;
	startCheckAdminNewItems('community_questions/ajax_comments_operation/check_new', idStartItemNew);
});
</script>

<div class="row">
    <div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>Comments list</span>
			<div class="pull-right btns-actions-all display-n">
				<a class="ep-icon ep-icon_remove txt-red pull-right confirm-dialog" data-callback="delete_comments" data-message="Are you sure want to delete this comments?" title="Delete comments"></a>
				<a class="ep-icon ep-icon_sheild-ok txt-green mr-5 pull-right confirm-dialog" data-callback="moderate_comments" data-message="Are you sure want to moderate this comments?" title="Moderate comments"></a>
			</div>
		</div>
		<?php tmvc::instance()->controller->view->display('admin/questions/questions_filter_bar'); ?>

		<div class="wr-filter-list mt-10 clearfix"></div>

        <table id="dtCommentsList" class="data table-striped table-bordered w-100pr" >
            <thead>
                <tr>
                    <th class="dt_id"><input type="checkbox" class="check-all-comments pull-left">#</th>
                    <th class="dt_answer">Answer</th>
                    <th class="author dt_author">Comment Author</th>
                    <th class="dt_text">Comment Text</th>
					<th class="dt_date">Added</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

