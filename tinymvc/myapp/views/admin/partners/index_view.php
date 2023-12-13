<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<script>
	var dtPartnersList;
    $(document).ready(function() {

        dtPartnersList = $('#dtPartnersList').dataTable({
            "sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL ?>partners/ajax_partners_administration/<?php echo $upload_folder;?>",
            "sServerMethod": "POST",
            "bFilter": false,
            "bPaginate": false,
            "bInfo": false,
            "aoColumnDefs": [
				{ "sClass": "w-50 tac", "aTargets": ['dt_id_partner'], "mData": "dt_id_partner", "bSortable": false },
				{ "sClass": "w-80 tac", "aTargets": ['dt_logo'], "mData": "dt_logo", "bSortable": false },
				{ "sClass": "tac", "aTargets": ['dt_name'], "mData": "dt_name", "bSortable": false },
				{ "sClass": "w-50 tac", "aTargets": ['dt_country'], "mData": "dt_country", "bSortable": false },
				{ "sClass": "w-50 tac", "aTargets": ['dt_visible'], "mData": "dt_visible", "bSortable": false },
				{ "sClass": "w-80 tac", "aTargets": ['dt_home'], "mData": "dt_home", "bSortable": false },
				{ "sClass": "w-80 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
            ],
            "fnServerParams": function(aoData) {
//                typeMess = 'info';
//                $('.menu-level3 li').each(function() {
//                    if ($(this).attr('class') == 'active')
//                        typeMess = $(this).children('a').attr('href');
//                });
//                aoData.push({"name": "type_mess", "value": typeMess});
            },
            "sPaginationType": "full_numbers",
            "fnDrawCallback": function(oSettings) { }
        });
    });

	var partnerRemove = function(obj){
		var $this = $(obj);
		var partner = $this.data('partner');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>partners/ajax_partners_operation/delete_partner',
			data: { partner : partner},
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					dtPartnersList.fnDraw();
				}
			}
		});
	}
</script>

<div class="row">
    <div class="pt-20 col-xs-12">
        <div class="titlehdr h-30">
        	<span>Partner list</span>
        	<a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="partners/partner_popups/add_partner/<?php echo $upload_folder;?>" data-table="dtPartnersList" data-title="Add partner"></a>
        </div>

        <table id="dtPartnersList" class="data table-striped table-bordered w-100pr" >
            <thead>
                <tr>
                    <th class="dt_id_partner">#</th>
                    <th class="dt_logo">Logo</th>
                    <th class="dt_name">Name</th>
                    <th class="dt_country">Country</th>
                    <th class="dt_visible">Visible</th>
                    <th class="dt_home">On Home</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
