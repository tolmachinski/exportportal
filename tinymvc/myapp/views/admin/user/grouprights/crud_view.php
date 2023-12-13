<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<script type="text/javascript">
	function callbackUpdateRight(resp){
		var $trRight = $("tr#trright-"+resp.idright);

		if(resp.r_module == resp.old_module){

			$trRight.find('.name-b').html('<span title="'+ resp.r_descr + '">'+ resp.r_name + '</span>').end()
				.find('.alias-b').text(resp.r_alias).end()
				.find('.field-b').text(resp.has_field).end()
				.find('.actions-b').find('a.ep-icon_remove').remove();

			if(resp.rcan_delete == 1){
				var $delete_butt = '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="removeRight" data-message="Are you sure you want to delete this right?" data-right="'+ resp.idright + '" title="Delete right"></a>';
				$trRight.find('.actions-b').append($delete_butt);
			}
		} else{

			$trRight.remove();
			var $new_tbody = $("table#"+resp.r_module+"-right_list > tbody");

			$trRight = '<tr id="trright-'+ resp.idright + '" >\
					<td class="tac">'+ resp.idright + '</td>\
					<td class="tac name-b"><span title="'+ resp.r_descr + '">'+ resp.r_name + '</span></td>\
					<td class="tac alias-b">'+ resp.r_alias+'</td>\
					<td class="tac field-b">'+ resp.has_field+'</td>\
					<td class="actions-b tac">\
						<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" data-title="Edit right" href="userrights/popup_userrights/edit_right/'+ resp.idright + '" title="Edit right"></a>';

			if(resp.rcan_delete == 1)
				$trRight += '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="removeRight" data-message="Are you sure you want to delete this right?" data-right="'+ resp.idright + '" title="Delete right"></a>';

			$trRight +=  '</td>\
				</tr>';

			$new_tbody.append($trRight);
		}
	}

	function callbackAddRight(resp){
		var $tbody = $("table#"+resp.r_module+"-right_list > tbody");

		var tr = '<tr id="trright-'+ resp.idright + '">'+
				'<td class="tac w-50">'+ resp.idright + '</td>'+
				'<td class="tac name-b"><span title="'+ resp.r_descr + '">'+ resp.r_name + '</span></td>'+
				'<td class="tac w-200 alias-b">'+ resp.r_alias+'</td>'+
				'<td class="tac w-50 field-b">'+ resp.has_field+'</td>'+
				'<td class="actions-b w-80">'+
					'<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" data-title="Edit right" href="userrights/popup_userrights/edit_right/'+ resp.idright + '" title="Edit right"></a>';

		if(resp.rcan_delete == 1)
			tr += '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="removeRight" data-message="Are you sure you want to delete this right?" data-right="'+ resp.idright + '" title="Delete right"></a>';

		tr +=  '</td>'+
			'</tr>';

		$tbody.append(tr);
	}

	var removeRight = function(obj){
		var $this = $(obj);
		var right = $this.data('right');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>userrights/ajax_userrights_operation/remove_right',
			data: { right : right},
			beforeSend: function(){  },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					$this.closest('tr').fadeOut('normal', function(){
						$(this).remove();
					});
				}
			}
		});
	}

	function callbackAddGroup(resp){
		var $tbody = $("table#group_list > tbody");

		var tr = '<tr id="trgroup-'+ resp.idgroup + '">\
				<td class="tac w-50">'+ resp.idgroup + '</td>\
				<td class="tac name-b">'+ resp.gr_name + '</td>\
				<td class="tac type-b">'+ resp.gr_type + '</td>\
				<td class="tac priority-b">'+ resp.gr_priority + '</td>\
				<td class="tac actions-b w-80">\
					<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" data-title="Edit group" href="userrights/popup_userrights/edit_group/'+ resp.idgroup + '" title="Edit group"></a>';

				if(resp.can_delete == 1)
					tr += '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="removeGroup" data-message="Are you sure you want to delete this group?" data-group="'+ resp.idgroup + '" title="Delete group"></a>';

				tr += '</td>\
				</tr>';

		$tbody.append(tr);
	}

	function callbackUpdateGroup(resp){
		var tr = $("table#group_list tr#trgroup-"+resp.idgroup);
		tr.find('.name-b').text(resp.gr_name).end()
			.find('.type-b').text(resp.gr_type).end()
			.find('.priority-b').text(resp.gr_priority);

		var $actions = tr.find('.actions-b');

		if(resp.can_delete == 0)
			$actions.find('a.ep-icon_remove').remove();
		else{
			if(!$actions.find('a.ep-icon_remove').length)
				$actions.append('<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="removeGroup" data-message="Are you sure you want to delete this group?" data-group="'+ resp.idgroup + '" title="Delete group"></a>');
		}
	}

	var removeGroup = function(obj){
		var $this = $(obj);
		var group = $this.data('group');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>userrights/ajax_userrights_operation/remove_group',
			data: { group : group},
			beforeSend: function(){  },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					$this.closest('tr').fadeOut('normal', function(){
						$(this).remove();
					});
				}
			}
		});
	}
</script>

<div class="row">
	<div class="col-xs-12">
		<a class="btn btn-default" href="admin/groupright">Groups and rights</a>
		<a class="btn btn-default" href="users/administration">Users</a>
		<a class="btn btn-default" href="group_packages/administration">Account upgrade packages</a>
		<a class="btn btn-default fancybox.ajax fancyboxValidateModal" data-title="Create group" href="<?php echo __SITE_URL;?>userrights/popup_userrights/add_group">Create group</a>
		<a class="btn btn-default fancybox.ajax fancyboxValidateModal" data-title="Create right" href="<?php echo __SITE_URL;?>userrights/popup_userrights/add_right">Create right</a>
	</div>
</div>

<div class="row">
    <div class="col-xs-6">

        <h3 class="titlehdr mt-10 mb-10">User's groups</h3>
        <table cellspacing="0" cellpadding="0" class="data table-bordered table-striped w-100pr" id="group_list">
            <thead>
                <tr>
                    <th >#</th>
                    <th>Group name</th>
                    <th>Group type</th>
                    <th>Group priority</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($groups as $group){?>
                <tr id="trgroup-<?php echo $group['idgroup']?>">
                    <td class="tac w-50"><?php echo $group['idgroup']?></td>
                    <td class="tac name-b"><?php echo $group['gr_name']?></td>
                    <td class="tac type-b"><?php echo $group['gr_type']?></td>
                    <td class="tac priority-b"><?php echo $group['gr_priority']?></td>
                    <td class="tac actions-b w-80">
                        <a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" data-title="Edit group" href="<?php echo __SITE_URL;?>userrights/popup_userrights/edit_group/<?php echo $group['idgroup']?>" title="Edit group"></a>

                        <?php if($group['can_delete']){?>
                        	<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="removeGroup" data-message="Are you sure you want to delete this group?" data-group="<?php echo $group['idgroup']?>" title="Delete group"></a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="col-xs-6">
        <?php foreach($bymodules as $module){ ?>
        <h3 class="titlehdr mt-10 mb-10" id="<?php echo $module['id_module']?>-right_ttl">Rights in module <?php echo $module['name_module']?></h3>
        <table cellspacing="0" cellpadding="0" class="data table-bordered table-striped w-100pr" id="<?php echo $module['id_module']?>-right_list">
            <thead>
                <tr>
                    <th >#</th>
                    <th>Right name</th>
                    <th>Right alias</th>
                    <th>Field</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
			<?php if (!empty($module['rights'])) { ?>
				<?php foreach($module['rights'] as $right){?>
					<tr id="trright-<?php echo $right['idright']?>">
						<td class="tac w-50"><?php echo $right['idright']?></td>
						<td class="tac name-b"><span title="<?php echo $right['r_descr']?>"><?php echo $right['r_name']?></span></td>
						<td class="tac w-200 alias-b"><?php echo $right['r_alias']?></td>
						<td class="tac w-50 filed-b"><?php echo ($right['has_field'])?'+':'-';?></td>
						<td class="tac w-80 actions-b">
							<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" data-title="Edit right" href="<?php echo __SITE_URL;?>userrights/popup_userrights/edit_right/<?php echo $right['idright']?>" title="Edit right"></a>

							<?php if ($right['rcan_delete']){?>
								<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="removeRight" data-message="Are you sure you want to delete this right?" data-right="<?php echo $right['idright']?>" title="Delete right"></a>
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
			<?php } ?>
			</tbody>
		</table>
		<?php } ?>
	</div>
</div>
