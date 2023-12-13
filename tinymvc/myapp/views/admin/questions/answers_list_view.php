<script>
	var dtAnswersList;
	var $checked_answers = [];

	var moderate_answer = function(opener){
		var $this = $(opener);
		$checked_answers = [];
		$checked_answers.push($this.data('answer'));

		moderate_answer_callback();
	}

	var moderate_answers = function(opener){
		$checked_answers = [];
		$(".check-answer:checked").each(function() {
			$checked_answers.push($(this).data('answer'));
		});

		moderate_answer_callback();
	}

	function moderate_answer_callback(){
		if ($checked_answers.length == 0) {
			systemMessages('Error: There are no answer(s) to be moderated.', 'message-error');
			return false;
		}

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>community_questions/ajax_answers_operation/moderate',
			dataType: "JSON",
			data: {answer: $checked_answers.join(',')},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type != 'error') {
					dtAnswersList.fnDraw();
				}
			}
		});
	}

	var delete_answer = function(opener){
		var $this = $(opener);
		$checked_answers = [];
		$checked_answers.push($this.data('answer'));

		delete_answer_callback();
	}

	var delete_answers = function(opener){
		$checked_answers = [];
		$(".check-answer:checked").each(function() {
			$checked_answers.push($(this).data('answer'));
		});

		delete_answer_callback();
	}

	function delete_answer_callback(){
		if ($checked_answers.length == 0) {
			systemMessages('Error: There are no answer(s) to be deleted.', 'message-error');
			return false;
		}

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>community_questions/ajax_answers_operation/delete_multiple',
			dataType: "JSON",
			data: {answer: $checked_answers.join(',')},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type != 'error') {
					dtAnswersList.fnDraw();
				}
			}
		});
	}

	$(document).ready(function() {
		var myFilters;
		dtAnswersList = $('#dtAnswersList').dataTable({
			"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "<?php echo __SITE_URL; ?>community_questions/ajax_administration_dt/answers",
			"sServerMethod": "POST",
			"sorting": [[5, "desc"]],
			"aoColumnDefs": [
				{"sClass": "w-50 tac vam", "aTargets": ['dt_id'], "mData": "dt_id", 'bSortable': false },
				{"sClass": "w-150", "aTargets": ['dt_author'], "mData": "dt_author" },
				{"sClass": "w-250 vam", "aTargets": ['dt_title'], "mData": "dt_title" },
				{"sClass": "vam","aTargets": ['dt_text'], "mData": "dt_text", 'bSortable': false },
				{"sClass": "w-150 vam", "aTargets": ['dt_question'], "mData": "dt_question" },
				{"sClass": "w-70 tac vam", "aTargets": ['dt_date'], "mData": "dt_date" },
				{"sClass": "w-50 tac vam", "aTargets": ['dt_comments'], "mData": "dt_comments" },
				{"sClass": "w-50 tac vam", "aTargets": ['dt_likes'], "mData": "dt_likes" },
				{"sClass": "w-50 tac vam", "aTargets": ['dt_dislike'], "mData": "dt_dislike" },
				{"sClass": "w-100 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", 'bSortable': false}
			],
			"fnServerData": function(sSource, aoData, fnCallback) {
				if (!myFilters) {
					myFilters = $('.dt_filter').dtFilters('.dt_filter',{
						'container': '.wr-filter-list',
						callBack: function() {
							dtAnswersList.fnDraw();
						},
						onSet: function(callerObj, filterObj){
							if(filterObj.name == 'start_date'){
								$("#finish_date").datepicker("option","minDate", $("#start_date").datepicker("getDate"));
							}
							if(filterObj.name == 'finish_date'){
								$("#start_date").datepicker("option","maxDate", $("#finish_date").datepicker("getDate"));
							}
						},
                        onDelete: function (callerObj, filterObj) {
                            if(filterObj.name == 'start_date'){
								$("#finish_date").datepicker("option","minDate", null);
							}
							if(filterObj.name == 'finish_date'){
								$("#start_date").datepicker("option","maxDate", null);
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
			"fnDrawCallback": function(oSettings) {    }
		});

		$('div.dataTables_filter input').addClass('search-question');

		$('.check-all-answers').on('click', function() {
			if ($(this).prop("checked")) {
				$('.check-answer').prop("checked", true);
				$('.btns-actions-all').show();
			} else {
				$('.check-answer').prop("checked", false);
				$('.btns-actions-all').hide();
			}
		});

		$('body').on('click', '.check-answer', function() {
			if ($(this).prop("checked")) {
				$('.btns-actions-all').show();
			} else {
				var hideBlock = true;
				$('.check-answer').each(function() {
					if ($(this).prop("checked")) {
						hideBlock = false;
						return false;
					}
				})
				if (hideBlock)
					$('.btns-actions-all').hide();
			}
		});

		idStartItemNew = <?php echo $last_answers_id;?>;
		startCheckAdminNewItems('community_questions/ajax_answers_operation/check_new', idStartItemNew);
	});
</script>
<div class="row">
    <div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>Answers list</span>

			<div class="pull-right btns-actions-all display-n">
				<a class="ep-icon ep-icon_remove txt-red pull-right confirm-dialog" data-message="Are you ure want delete this answers?" data-callback="delete_answers" title="Delete answers"></a>
				<a class="ep-icon ep-icon_sheild-ok txt-green mr-5 pull-right confirm-dialog" data-message="Are you ure want moderate this answer(s)?" data-callback="moderate_answers" title="Moderate answers"></a>
			</div>
		</div>

		<?php views()->display('admin/questions/questions_filter_bar'); ?>

		<div class="wr-filter-list mt-10 clearfix"></div>

		<table id="dtAnswersList" class="data table-bordered table-striped w-100pr">
			<thead>
				<tr>
					<th class="dt_id"><input type="checkbox" class="check-all-answers pull-left">#</th>
					<th class="author dt_author">Author</th>
					<th class="dt_title">Title</th>
					<th class="dt_text">Text</th>
					<th class="dt_question">Question</th>
					<th class="dt_date">Answered</th>
					<th class="dt_comments"><span title="Comments">C</span></th>
					<th class="dt_likes"><a class="ep-icon ep-icon_star-plus txt-orange" title="Likes"></a></th>
					<th class="dt_dislike"><a class="ep-icon ep-icon_star-minus txt-red" title="Dislikes"></a></th>
					<th class="dt_actions">Actions</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
    </div>
</div>

