<script src="<?php echo asset('public/plug_admin/jquery-countdown-2-2-0/jquery.countdown.js', 'legacy'); ?>"></script>
<script>
	var dtAccreditationDocs;
    var myFilters;
	$(document).ready(function(){
		dtAccreditationDocs = $('#dtAccreditationDocs').dataTable({
			"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "<?php echo __SITE_URL; ?>verification_document_types/ajax_operation/list?mode=legacy",
			"aoColumnDefs": [
				{"sClass": "vam w-50 tac", "aTargets": ['dt_id'], "mData": "dt_id"},
				{"sClass": "vam w-120 tac vam", "aTargets": ['dt_category'], "mData": "dt_category", "bSortable": false },
				{"sClass": "vam", "aTargets": ['dt_title'], "mData": "dt_title"},
				{"sClass": "vam w-120 tac vam", "aTargets": ['dt_update'], "mData": "dt_update", "bSortable": true },
				{"sClass": "vam w-200 tac vam", "aTargets": ['dt_translations'], "mData": "dt_translations", "bSortable": false },
				{"sClass": "w-80 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
			],
			"fnServerData": function(sSource, aoData, fnCallback) {
				if(!myFilters){
                    myFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        container: '.wr-filter-list',
                        callBack: function(){
                            dtAccreditationDocs.fnDraw();
                        },
                        beforeSet: function(callerObj){},
                        onSet: function(callerObj, filterObj){},
                        onDelete: function(filterObj){}
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
			"sorting" : [[0,'desc']],
			"sPaginationType": "full_numbers",
			"fnDrawCallback": function(oSettings) {}
		});
	});

	var delete_doc = function(obj){
		var $this = $(obj);
		var id_document = $this.data('doc');
		$.ajax({
			url: '<?php echo __SITE_URL; ?>verification_document_types/ajax_operation/delete/' + id_document,
			type: 'POST',
			data:  {id_document:id_document},
			dataType: 'json',
			success: function(resp){
				systemMessages(resp.message, 'message-' + resp.mess_type );
                if(resp.mess_type == 'success'){
					dtAccreditationDocs.fnDraw(false);
				}
			}
		});
	}
</script>
<div class="container-fluid content-dashboard">
	<div class="row">
		<div class="col-xs-12">
			<div class="titlehdr h-30">
				<span>Document types</span>
                <?php if (have_right('moderate_content')) { ?>
				    <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="<?php echo $addUrl; ?>" data-table="dtAccreditationDocs" data-title="Add verification document type"></a>
                <?php } ?>
			</div>

			<?php views()->display('admin/verification_document_types/filter_panel_view'); ?>
			<div class="wr-filter-list mt-10 clearfix"></div>

			<table id="dtAccreditationDocs" class="data table-bordered table-striped w-100pr dataTable">
                <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_category">Category</th>
                    <th class="dt_title">Document title</th>
                    <th class="dt_update">EN updated at</th>
                    <th class="dt_translations">Translated to</th>
                    <th class="dt_actions">Actions</th>
                </tr>
                </thead>
                <tbody class="tabMessage" id="pageall"></tbody>
            </table>
		</div>
	</div>
</div>
