<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>Site routings</span>
			<div class="pull-right btns-actions-all">
                <a class="btn btn-success pull-right fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL;?>translations/popup_forms/add_routing_form" title="Add language" data-title="Add language">
					<i class="ep-icon ep-icon_plus fs-12 lh-12"></i>
					Add routing
				</a>
                <a class="btn btn-primary pull-right mr-5 call-function" data-callback="regenerate_route" href="#" title="Update lang routes">
					<i class="ep-icon ep-icon_updates fs-12 lh-12"></i>
				</a>
			</div>
		</div>

		<table id="dtRoutings" class="data table-bordered table-striped w-100pr">
			<thead>
				<tr>
					<th class="dt_id w-50">#</th>
					<th class="dt_route_controller w-100">Controller</th>
					<th class="dt_route_action w-100">Action</th>
					<th class="dt_route_replace">Routing replace</th>
					<th class="dt_route_position">Position</th>
					<th class="dt_actions w-100">Actions</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
<script>
    var dtRoutings;
	function translations_routes_callback(){
        dtRoutings.fnDraw(false);
	}

	var regenerate_route = function(btn){
		var $this = $(btn);
		$.ajax({
            url: '<?php echo __SITE_URL ?>translations/ajax_operations/regenerate_route',
            type: 'POST',
            dataType: 'json',
            data: {},
            beforeSend: function () {},
            success: function(resp){
                systemMessages( resp.message, 'message-' + resp.mess_type );
            }
        });
	}

	var change_route_weight = function(btn){
		var $this = $(btn);
        var id_route = $this.data('route');
        var direction = $this.data('direction');
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>translations/ajax_operations/change_route_weight',
            data: {id_route:id_route,direction:direction},
            dataType: 'JSON',
            success: function(resp){
                systemMessages( resp.message, 'message-' + resp.mess_type );
                if(resp.mess_type == 'success'){
                    translations_routes_callback();
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                systemMessages( 'Error.', 'error' );
                jqXHR.abort();
            }
        });
        return false;
	}

	$(document).ready(function(){
        dtRoutings = $('#dtRoutings').dataTable( {
            "sDom": 'rt<"bottom"i><"clear">',
			"pageLength": 250,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>translations/ajax_operations/routings_list_dt",
            "sServerMethod": "POST",
            "sorting": [],
            "aoColumnDefs": [
                {"sClass": "w-50 vam tac", "aTargets": ['dt_id'], "mData": "dt_id", "bSortable": false},
                {"sClass": "w-100 vam tac", "aTargets": ['dt_route_controller'], "mData": "dt_route_controller"},
                {"sClass": "w-100 vam tac", "aTargets": ['dt_route_action'], "mData": "dt_route_action", 'bSortable': false},
                {"sClass": "vat tac", "aTargets": ['dt_route_replace'], "mData": "dt_route_replace", 'bSortable': false},
                {"sClass": "w-100 vat tac", "aTargets": ['dt_route_position'], "mData": "dt_route_position"},
                {"sClass": "w-100 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", 'bSortable': false}
            ],
            "sPaginationType": "full_numbers",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
				$.ajax( {
					"dataType": 'JSON',
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
            "fnDrawCallback": function( oSettings ) {}
        });
    });
</script>
