<script type="text/javascript" src="<?php echo __SITE_URL ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="text/javascript">
var dtFaq, groupsFilters;

$(document).ready(function(){

	dtFaq = $('#dtFaq').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>faq/ajax_faq_operation/administration_list_dt",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{ "sClass": "w-50 tac", "aTargets": ['dt_id'], "mData": "dt_id"},
			{ "sClass": "", "aTargets": ['dt_question'], "mData": "dt_question"},
			{ "sClass": "w-150 tac", "aTargets": ['dt_updated_at'], "mData": "dt_updated_at"},
			{ "sClass": "w-150 tac", "aTargets": ['dt_tlangs_list'], "mData": "dt_tlangs_list", "bSortable": false  },
			{ "sClass": "w-150 tac", "aTargets": ['dt_tags_list'], "mData": "dt_tags_list", "bSortable": false  },
			{ "sClass": "w-200 tac vam", "aTargets": ['dt_weight'], "mData": "dt_weight"  },
			{ "sClass": "tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
		],
		"fnServerParams": function ( aoData ) {},
		"fnServerData": function ( sSource, aoData, fnCallback ) {
            if (!groupsFilters) {
                groupsFilters = $('.dt_filter').dtFilters('.dt_filter',{
                    'container': '.wr-filter-list',
                    'debug': false,
                    callBack: function () { dtFaq.fnDraw(); },
                    onSet: function (callerObj, filterObj) {},
                    onDelete: function (filter) {}
                });
            }

            aoData = aoData.concat(groupsFilters.getDTFilter());
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
    dtFaq.fnDraw(false);
}

var save_weight = function(obj){
	var $this = $(obj);
	var id_faq = $this.data('id_faq');
    var weight = $this.prev().val();

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>faq/ajax_faq_operation/save_tag_weight',
		data: { id_faq : id_faq, weight : weight},
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				callbackManageFaq(resp);
			}
		}
	});
}

var delete_faq = function(obj){
	var $this = $(obj);
	var faq = $this.data('faq');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>faq/ajax_faq_operation/delete_faq',
		data: { faq : faq},
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
    		<span>FAQ list</span>
            <?php if(have_right('manage_content')) { ?>
    		    <a class="btn btn-primary btn-sm pull-right fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL;?>faq/popup_forms/create_faq" data-table="dtFaq" data-title="Add question">Add question</a>
            <?php } ?>
    	</div>

		<?php tmvc::instance()->controller->view->display('admin/faq/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

		<table id="dtFaq" class="data table-striped table-bordered w-100pr" cellspacing="0" cellpadding="0" >
			<thead>
				<tr>
					<th class="dt_id w-50">#</th>
					<th class="dt_question">Question</th>
					<th class="dt_updated_at">EN updated at</th>
					<th class="dt_tlangs_list">Translated in</th>
					<th class="dt_tags_list">Tags</th>
					<th class="dt_weight">Weight</th>
					<th class="dt_actions w-80">Actions</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
