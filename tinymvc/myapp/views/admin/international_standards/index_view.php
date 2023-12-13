<script src="<?php echo __FILES_URL;?>public/plug_admin/jquery-countdown-2-2-0/jquery.countdown.js"></script>
<script>
	var dtIStandards;
    var myFilters;
	$(document).ready(function(){
		dtIStandards = $('#dtIStandards').dataTable({
			"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "<?php echo __SITE_URL;?>international_standards/administration_dt",
			"aoColumnDefs": [
				{"sClass": "vam w-100 tac", "aTargets": ['dt_id'], "mData": "dt_id", "bSortable": false},
				{"sClass": "vam", "aTargets": ['dt_title'], "mData": "dt_title", "bSortable": false},
				{"sClass": "vam w-120 tac", "aTargets": ['dt_country'], "mData": "dt_country"},
				{"sClass": "w-80 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
			],
			"fnServerData": function(sSource, aoData, fnCallback) {
				if(!myFilters){
                    myFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        container: '.wr-filter-list',
                        callBack: function(){
                            dtIStandards.fnDraw();
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

	var delete_standard = function(obj){
		var $this = $(obj);
		var id_standard = $this.data('standard');
		$.ajax({
			url: '<?php echo __SITE_URL;?>international_standards/ajax_operations/delete_standard',
			type: 'POST',
			data:  {id_standard:id_standard},
			dataType: 'json',
			success: function(resp){
				systemMessages(resp.message, 'message-' + resp.mess_type );
                if(resp.mess_type == 'success'){
					dtIStandards.fnDraw(false);
				}
			}
		});
	}
</script>
<div class="container-fluid content-dashboard">
	<div class="row">
		<div class="col-xs-12">
			<div class="titlehdr h-30">
				<span>International Standards</span>
				<a class="ep-icon ep-icon_plus-circle txt-green fancyboxValidateModalDT fancybox.ajax pull-right" href="<?php echo __SITE_URL;?>international_standards/popup_forms/add_standard" data-table="dtIStandards" data-title="Add International Standard" title="Add International Standard"></a>
			</div>
			<?php tmvc::instance()->controller->view->display('admin/international_standards/filter_view'); ?>
			<div class="mt-10 wr-filter-list clearfix"></div>
			<table id="dtIStandards" class="data table-bordered table-striped w-100pr dataTable">
                <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_title">Title</th>
                    <th class="dt_country">Country</th>
                    <th class="dt_actions">Actions</th>
                </tr>
                </thead>
                <tbody class="tabMessage" id="pageall"></tbody>
            </table>
		</div>
	</div>
</div>
