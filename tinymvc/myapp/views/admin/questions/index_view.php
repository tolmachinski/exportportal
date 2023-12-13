<script type="text/javascript">
var dtQuestionCategory;

$(document).ready(function(){

	dtQuestionCategory = $('#dtQuestionCategory').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>community_questions/ajax_administration_dt/question_category",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-50 tac", "aTargets": ['dt_id'], "mData": "dt_id"},
			{ "sClass": "", "aTargets": ['dt_title_cat'], "mData": "dt_title_cat"},
			{ "sClass": "w-70 tac", "aTargets": ['dt_visible'], "mData": "dt_visible"},
			{ "sClass": "w-150 tac", "aTargets": ['dt_tlangs'], "mData": "dt_tlangs", "bSortable": false},
			{ "sClass": "w-200 tac", "aTargets": ['dt_tlangs_list'], "mData": "dt_tlangs_list", "bSortable": false},
			{ "sClass": "tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"fnServerParams": function ( aoData ) {

		},
		"fnServerData": function ( sSource, aoData, fnCallback ) {
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
			
		}
	});
});

var removeCategory = function(obj){
	var $this = $(obj);
	var category = $this.data('category');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>community_questions/ajax_question_categories_operation/remove_category',
		data: { category : category},
		beforeSend: function(){  },
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				dtQuestionCategory.fnDraw();
			}
		}
	});
}

var remove_category_question_i18n = function(obj){
	var $this = $(obj);
	var category = $this.data('category');
	var lang_category = $this.data('lang');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>community_questions/ajax_question_categories_operation/remove_category_i18n',
		data: { category : category, lang_category : lang_category},
		beforeSend: function(){  },
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				dtQuestionCategory.fnDraw();
			}
		}
	});
}
</script>

<div class="row">
	<div class="col-xs-12">
    	<div class="titlehdr h-30">
    		<span>Question categories</span>
    		<a class="btn btn-primary btn-sm pull-right fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL;?>community_questions/popup_forms/create_question_category" data-table="dtQuestionCategory" data-title="Add category">Add category</a>
    	</div>
		
		<table id="dtQuestionCategory" class="data table-striped table-bordered w-100pr" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					 <th class="dt_id w-50">#</th>
					 <th class="dt_title_cat">Title category</th>
					 <th class="dt_visible">Visible</th>
					 <th class="dt_tlangs_list">Translated in</th>
					 <th class="dt_tlangs">Translate</th>
					 <th class="dt_actions w-80">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>
