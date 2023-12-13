
<script type="text/javascript">
$(document).ready(function(){

});

	function callbackUpdateService(resp){
		$('#trservice-'+resp.id_service).find('.name-b').text(resp.s_title);
	}

	function callbackCreateService(resp){
		$('#table-services').find('tbody')
			.append('<tr id="trservice-'+resp.id_service+'">\
                    <td class="tac w-50">'+resp.id_service+'</td>\
                    <td class="tac name-b">'+resp.s_title+'</td>\
                    <td class="tac actions-b w-80">\
                        <a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" href="admin/popup_forms/edit_group_service/'+resp.id_service+'" data-title="Edit service" title="Edit service"></a>\
                        <a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="serviceRemove" data-message="Are you sure you want to delete this service?" data-service="'+resp.id_service+'" href="#" title="Delete service"></a>\
                    </td>\
                </tr>');
	}

	var serviceRemove = function(obj){
		var $this = $(obj);
		var service = $this.data('service');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>admin/ajax_admin_operation/delete_group_service',
			data: { service : service},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					$('#trservice-'+service+',#trservicegroup-'+service).fadeOut(function(){
						$(this).remove();
					});
				}
			}
		});
	}

	var disableServiceGroup = function(obj){
		var $this = $(obj);
		var service = $this.data('service');
		var group = $this.data('group');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>admin/ajax_admin_operation/delete_relation_service',
			data: { service : service, group : group},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					$this.data('callback','enableServiceGroup').attr('title','Change to enable')
						.removeClass('ep-icon_ok txt-green').addClass('ep-icon_remove txt-red');
				}
			}
		});
	}

	var enableServiceGroup = function(obj){
		var $this = $(obj);
		var service = $this.data('service');
		var group = $this.data('group');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>admin/ajax_admin_operation/create_relation_service',
			data: { service : service, group : group},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					$this.data('callback','disableServiceGroup').attr('title','Change to disable')
						.removeClass('ep-icon_remove txt-red').addClass('ep-icon_ok txt-green');
				}
			}
		});
	}
</script>

<div class="row">
    <div class="col-xs-12">
        <h3 class="titlehdr mt-10 mb-10">User's services <a class="pull-right btn btn-default btn-sm fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL?>admin/popup_forms/add_group_service" data-title="Add service" title="Add service">Add service</a></h3>

        <?php if(isset($services) && count($services)){?>
        <table id="table-services" cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Service</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($services as $service){ ?>
                <tr id="trservice-<?php echo $service['id_service']?>">
                    <td class="tac w-50"><?php echo $service['id_service']?></td>
                    <td class="tac name-b"><?php echo $service['s_title']?></td>
                    <td class="tac actions-b w-80">
                        <a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL?>admin/popup_forms/edit_group_service/<?php echo $service['id_service']?>" data-title="Edit service" title="Edit service"></a>
                        <a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="serviceRemove" data-message="Are you sure you want to delete this service?" data-service="<?php echo $service['id_service']?>" href="#" title="Delete service"></a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php } ?>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <h3 class="titlehdr">Services of user's group</h3>

        <table id="table-servicesgroup" cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr">
            <thead>
                <tr>
                    <th class="tac">Services/Groups</th>
                    <?php foreach($groups as $group){?>
                    <th class="tac"><?php echo $group['gr_name']?></th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($services as $service){ ?>
                <tr id="trservicegroup-<?php echo $service['id_service']?>">
                    <td class="tac"><?php echo $service['s_title']?></td>
                    <?php foreach($groups as $group){?>
                    <td class="tac">
                        <?php if(in_array($service['id_service'], $relations[$group['idgroup']])){ ?>
							<a class="ep-icon ep-icon_ok txt-green call-function" data-callback="disableServiceGroup" data-service="<?php echo $service['id_service']?>" data-group="<?php echo $group['idgroup']?>" title="Change to disable"></a>
                        <?php } else {?>
							<a class="ep-icon ep-icon_remove txt-red call-function" data-callback="enableServiceGroup" data-service="<?php echo $service['id_service']?>" data-group="<?php echo $group['idgroup']?>" title="Change to enable"></a>
                        <?php } ?>
                    </td>
                    <?php } ?>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>
