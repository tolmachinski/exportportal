<script>
	var dtQuestionsList;
	var $checked_questions = [];

	var delete_question = function(opener){
		var $this = $(opener);
		$checked_questions = [];
		$checked_questions.push($this.data('question'));

		delete_questions_callback();
	}

	var delete_questions = function(){
		$checked_questions = [];
		$(".check-question:checked").each(function() {
			$checked_questions.push($(this).data('id-question'));
		});

		delete_questions_callback();
	}

	function delete_questions_callback(){
		if ($checked_questions.length == 0) {
			systemMessages('Error: There are no question(s) to be deleted.', 'message-error');
			return false;
		}

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>community_questions/ajax_questions_operation/delete_multiple',
			dataType: "JSON",
			data: {question: $checked_questions.join(',')},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type != 'error') {
					dtQuestionsList.fnDraw();
				}

			}
		});
	}

	var moderate_question = function(opener){
		var $this = $(opener);
		$checked_questions = [];
		$checked_questions.push($this.data('question'));

		moderate_questions_callback();
	}

	var moderate_questions = function(){
		$checked_questions = [];
		$(".check-question:checked").each(function() {
			$checked_questions.push($(this).data('id-question'));
		});

		moderate_questions_callback();
	}

	function moderate_questions_callback(){
		if ($checked_questions.length == 0) {
			systemMessages('Error: There are no question(s) to be moderated.', 'message-error');
			return false;
		}

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>community_questions/ajax_questions_operation/moderate',
			dataType: "JSON",
			data: {question: $checked_questions.join(',')},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type != 'error') {
					dtQuestionsList.fnDraw();
				}

			}
		});
	}

    $(document).ready(function() {
		var myFilters;
		dtQuestionsList = $('#dtQuestionsList').dataTable({
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL;?>community_questions/ajax_administration_dt/questions",
            "sServerMethod": "POST",
            "sorting": [[ 6, "desc" ]],
            "aoColumnDefs": [
                {"sClass": "w-50 tac vam", "aTargets": ['dt_id'], "mData": "dt_id", 'bSortable': false},
                {"sClass": "w-150", "aTargets": ['dt_author'], "mData": "dt_author" },
                {"sClass": "w-300", "aTargets": ['dt_title'], "mData": "dt_title" },
                {"sClass": "","aTargets": ['dt_text'], "mData": "dt_text", 'bSortable': false },
                {"sClass": "w-200", "aTargets": ['dt_category'], "mData": "dt_category" },
                {"sClass": "w-50 tac vam", "aTargets": ['dt_county'], "mData": "dt_county" },
                {"sClass": "w-70 tac vam", "aTargets": ['dt_date'], "mData": "dt_date" },
                {"sClass": "w-30 tac vam", "aTargets": ['dt_answers'], "mData": "dt_answers" },
                {"sClass": "w-30 tac vam", "aTargets": ['dt_comments'], "mData": "dt_comments" },
                {"sClass": "w-100 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", 'bSortable': false}
            ],
			"fnServerData": function ( sSource, aoData, fnCallback ) {
                if(!myFilters){
					myFilters = $('.dt_filter').dtFilters('.dt_filter',{
						'container': '.wr-filter-list',
						callBack: function(){
							dtQuestionsList.fnDraw();
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

				$.ajax( {
					"dataType": 'json',
					"type": "POST",
					"url": sSource,
					"data": aoData,
					"success": function (data, textStatus, jqXHR) {
						if(data.mess_type == 'error')
							systemMessages(data.message, 'message-' + data.mess_type);

						fnCallback(data, textStatus, jqXHR);

					}
				});
			},

            "sPaginationType": "full_numbers",
            "fnDrawCallback": function(oSettings) { }
		});

        $('div.dataTables_filter input').addClass('search-question');

		$('.check-all-questions').on('click', function(e){
			if($(this).prop("checked")){
				$('.btns-actions-all').show();
				$('.check-question').prop("checked", true);
			}else {
				$('.check-question').prop("checked", false);
				$('.btns-actions-all').hide();
			}
		});

		$('body').on('click', '.check-question', function(){
			if($(this).prop("checked")){
				$('.btns-actions-all').show();
			}else {
				var hideBlock = true;
				$('.check-question').each(function(){
					if($(this).prop("checked")){
						hideBlock = false;
						return false;
					}
				});
				if(hideBlock)
					$('.btns-actions-all').hide();
			}
		});

		idStartItemNew = <?php echo $last_questions_id;?>;
		startCheckAdminNewItems('community_questions/ajax_questions_operation/check_new', idStartItemNew);
	});
</script>

<div class="row">
    <div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>Questions list</span>
			<div class="pull-right btns-actions-all display-n">
				<a class="ep-icon ep-icon_remove txt-red pull-right confirm-dialog" data-message="Are you sure want delete selected questions?" data-callback="delete_questions" title="Delete questions"></a>
				<a class="ep-icon ep-icon_sheild-ok txt-green mr-5 pull-right confirm-dialog" data-message="Are you sure want moderate selected questions?" data-callback="moderate_questions" id="moderate-questions" title="Moderate questions"></a>
			</div>
		</div>
		<?php views()->display('admin/questions/questions_filter_bar'); ?>

		<div class="wr-filter-list mt-10 clearfix"></div>

        <table id="dtQuestionsList" class="data table-bordered table-striped w-100pr" >
            <thead>
                <tr>
                    <th class="dt_id"><input type="checkbox" class="check-all-questions pull-left">#</th>
                    <th class="author dt_author">Author</th>
                    <th class="dt_title">Title</th>
                    <th class="dt_text">Text</th>
                    <th class="dt_category">Category</th>
                    <th class="country dt_county"><a class="ep-icon ep-icon_globe fs-22" title="Country"></a></th>
                    <th class="dt_date">Asked</th>
                    <th class="tal dt_answers"><span title="Answers">A</span></th>
                    <th class="tal dt_comments"><span title="Comments">C</span></th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

