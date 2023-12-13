<script type="text/javascript">
var dtFaqTags, groupsFilters;

$(document).ready(function(){

	dtFaqTags = $('#dtFaqTags').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>faq/ajax_faq_operation/tags_administration_list_dt",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "", "aTargets": ['dt_tag_name'], "mData": "dt_tag_name", "bSortable": false},
			{ "sClass": "tac", "aTargets": ['dt_top_priority'], "mData": "dt_top_priority", "bSortable": true},
			{ "sClass": "tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"fnServerParams": function ( aoData ) {},
		"fnServerData": function ( sSource, aoData, fnCallback ) {
/*             if (!groupsFilters) {
                groupsFilters = $('.dt_filter').dtFilters('.dt_filter',{
                    'container': '.wr-filter-list',
                    'debug': false,
                    callBack: function () { dtFaqTags.fnDraw(); },
                    onSet: function (callerObj, filterObj) {},
                    onDelete: function (filter) {}
                });
            } */

            // aoData = aoData.concat(groupsFilters.getDTFilter());
			$.ajax( {
				"dataType": 'JSON',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if(data.mess_type == 'error' || data.mess_type == 'info') {
						systemMessages(data.message, 'message-' + data.mess_type);
                        }

					fnCallback(data, textStatus, jqXHR);
				}
			});
        },
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {}
	});
});

function callbackManageFaq(resp){
    dtFaqTags.fnDraw(false);
}

var delete_faq_tag = function(obj){
	var $this = $(obj);
	var id_tag = $this.data('id_tag');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>faq/ajax_faq_operation/delete_faq_tag',
		data: { id_tag : id_tag},
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				callbackManageFaq(resp);
			}
		}
	});
}
</script>

<div class="row">
	<div class="col-xs-12">
    	<div class="titlehdr h-30">
			<span>FAQ tags</span>
			<a class="btn btn-primary btn-sm pull-right fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL; ?>faq/popup_forms/create_faq_tag" data-table="dtFaq" data-title="Add question">Add tag</a>
    	</div>

		<table id="dtFaqTags" class="data table-striped table-bordered w-100pr" cellspacing="0" cellpadding="0" >
			<thead>
				<tr>
					<th class="dt_tag_name">Tag</th>
					<th class="dt_top_priority">Top priority</th>
					<th class="dt_actions w-80">Actions</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
