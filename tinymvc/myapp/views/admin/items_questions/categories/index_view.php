<script>
var dtCategoriesQuestionsList;

$(document).ready(function() {

	dtCategoriesQuestionsList = $('#dtCategoriesQuestionsList').dataTable({
	    "bProcessing": true,
	    "bServerSide": true,
	    "sAjaxSource": "<?php echo __SITE_URL; ?>items_questions/ajax_categories_list_admin_dt",
	    "aoColumnDefs": [
			{"sClass": "w-60 tac", "aTargets": ['id_dt'], "mData": "id_dt"},
			{"sClass": "tac", "aTargets": ['category_dt'], "mData": "category_dt"},
			{"sClass": "w-80 tac", "aTargets": ['actions_dt'], "mData": "actions_dt", "bSortable": false}
	    ],
	    "sPaginationType": "full_numbers",
	    "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
	    "fnServerData": function(sSource, aoData, fnCallback) {
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
});

var removeCategory = function(obj){
	var $this = $(obj);
	var category = $this.data('category');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>items_questions/ajax_question_operation/remove_category',
		data: { category : category},
		beforeSend: function(){  },
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				dtCategoriesQuestionsList.fnDraw();
			}
		}
	});
}
</script>
<div class="row">
    <div class="col-xs-12">
	<div class="titlehdr h-30">
	    <span>Items categories questions list</span>
	    <a class="ep-icon ep-icon_plus-circle txt-green pull-right fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL;?>items_questions/popup_forms/create_question_category" data-table="dtCategoriesQuestionsList" data-title="Add category"></a>
	</div>

    <table id="dtCategoriesQuestionsList" class="data table-bordered table-striped w-100pr">
	    <thead>
		<tr>
		    <th class="id_dt">#</th>
		    <th class="category_dt">Category</th>
		    <th class="actions_dt">Actions</th>
		</tr>
	    </thead>
	    <tbody></tbody>
	</table>
    </div>
</div>
