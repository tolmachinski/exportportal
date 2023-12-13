<script>
    var dtVacancies;

    $(function(){

        dtVacancies = $('#dtVacancies').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>hiring/ajax_operations/list",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "w-50 tac", "aTargets": ['dt_id'], "mData": "dt_id"},
                { "sClass": "w-200", "aTargets": ['dt_office'], "mData": "dt_office"},
                { "sClass": "w-250", "aTargets": ['dt_name'], "mData": "dt_name"},
                { "sClass": "", "aTargets": ['dt_link'], "mData": "dt_link"},
                { "sClass": "w-150 tac", "aTargets": ['dt_country'], "mData": "dt_country", "bSortable": false  },
                { "sClass": "w-100 tac", "aTargets": ['dt_visible'], "mData": "dt_visible", "bSortable": false  },
                { "sClass": "w-120 tac", "aTargets": ['dt_date'], "mData": "dt_date", "bSortable": false  },
                { "sClass": "tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
            ],
            "fnServerParams": function ( aoData ) {},
            "fnServerData": function ( sSource, aoData, fnCallback ) {
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

    function callbackManageVacancies(resp){
        dtVacancies.fnDraw(false);
    }

	var removeVacancy = function (obj) {
		var $this = $(obj);
		var vacancy = $this.data('vacancy');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>hiring/ajax_operations/delete',
			data: {vacancy: vacancy},
			beforeSend: function () {},
			dataType: 'json',
			success: function(resp) {
				systemMessages(resp.message, 'message-' + resp.mess_type);

				if (resp.mess_type === 'success') {
					callbackManageVacancies(resp);
				}
			}
		});
	}
</script>
<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
    		<span>Vacancies list</span>
            <?php if(have_right('manage_content')) { ?>
    		    <a class="btn btn-primary btn-sm pull-right fancybox.ajax fancyboxValidateModalDT" href="<?php echo __SITE_URL;?>hiring/popup_forms/add_vacancy" data-table="dtVacancies" data-title="Add vacancy">Add vacancy</a>
            <?php } ?>
    	</div>
        <table id="dtVacancies" cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr">
            <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_office">Office name</th>
                    <th class="dt_name">Post</th>
                    <th class="dt_link">Link</th>
                    <th class="dt_country">Country</th>
                    <th class="dt_visible">Visible</th>
                    <th class="dt_date">Date</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
