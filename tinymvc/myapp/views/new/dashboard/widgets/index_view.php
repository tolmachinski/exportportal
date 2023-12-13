<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>

<script>
    window.remove_widget = function($widget) {
        $.ajax({
            'url': '/dashboard/remove_widget',
            'type': 'POST',
            'data': {
                id: $widget.data('id')
            },
            'dataType': 'JSON',
            'success': function (data) {
                if(data.mess_type === 'success') {
                    window.widgetsTable && window.widgetsTable.fnDraw(false);
                }
                systemMessages(data.message, data.mess_type);
            }
        });
    };

    $(document).on('click', '#copy-widget-code', function (e) {
        e.preventDefault();
        $('#widget-code-textarea').select();
        try {
            var successful = document.execCommand('copy');
            if (successful) {
                systemMessages('The widget code was copied to your clipboard', 'success');
            } else {
                systemMessages('We could not copy widget code to your clipboard, please do you manually', 'error');
            }
        } catch (err) {
            systemMessages('We could not copy widget code to your clipboard, please do you manually', 'error');
        }
    });

    $(document).ready(function(){
        window.widgetsTable = $('#dtItemsList').dataTable({
            "sDom": '<"top"i>rt<"bottom"lp><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>dashboard/ajax_my_widgets",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                {"sClass": "", "aTargets": ['dt_site'], "mData": "dt_site" , 'bSortable': true},
                {"sClass": "w-200", "aTargets": ['dt_width'], "mData": "dt_width" , 'bSortable': true},
                {"sClass": "w-120", "aTargets": ['dt_height'], "mData": "dt_height" , 'bSortable': true},
                {"sClass": "w-40 tac vam dt-actions", "aTargets": ['dt_actions'], "mData": "dt_actions", 'bSortable': false}
            ],
            "sorting" : [],
            "sPaginationType": "full_numbers",
            "fnServerData": function (sSource, aoData, fnCallback) {
                $.ajax({
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if(data.mess_type === 'error' || data.mess_type === 'info') {
                            systemMessages(data.message, data.mess_type);
                        }

                        fnCallback(data, textStatus, jqXHR);
                    }
                });
            },
            "fnDrawCallback": function( oSettings ) {
                hideDTbottom(this);
				mobileDataTable($('.main-data-table'));
            }
        });
    });
</script>

<div class="container-center dashboard-container">
	<div class="dashboard-line">
        <h1 class="dashboard-line__ttl">My Widgets</h1>

        <div class="dashboard-line__actions">
            <a class="btn btn-primary pl-20 pr-20 fancybox fancybox.ajax" data-title="Create widget" data-mw="470" href="<?php echo __SITE_URL; ?>dashboard/add_widget_popup">
				<i class="ep-icon ep-icon_plus-circle fs-20"></i>
				<span class="dn-m-min">Add widget</span>
			</a>
        </div>
    </div>

	<table class="main-data-table" id="dtItemsList">
        <thead>
            <tr>
                <th class="dt_site">Site</th>
				<th class="dt_width">Height</th>
				<th class="dt_height">Width</th>
				<th class="dt_actions"></th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>
