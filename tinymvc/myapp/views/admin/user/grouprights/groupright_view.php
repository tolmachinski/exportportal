<script type="text/javascript">
	var createRelation = function(obj){
		var $this = $(obj);
		var group = $this.data('group');
		var right = $this.data('right');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>userrights/ajax_userrights_operation/create_relation',
			data: { group : group, right : right},
			beforeSend: function(){  },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					$this.data('callback','removeRelation').attr('title','Change to enable').removeClass('ep-icon_remove txt-red').addClass('ep-icon_ok txt-green');
				}
			}
		});
	}

	var removeRelation = function(obj){
		var $this = $(obj);
		var group = $this.data('group');
		var right = $this.data('right');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>userrights/ajax_userrights_operation/remove_relation',
			data: { group : group, right : right},
			beforeSend: function(){  },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					$this.data('callback','createRelation').attr('title','Change to disable').removeClass('ep-icon_ok txt-green').addClass('ep-icon_remove txt-red');
				}
			}
		});
	}
</script>

<div class="row">
	<div class="col-xs-12 mt-20">
        <?php foreach($bymodules as $module){?>
            <?php if (empty($module['rights'])) continue; ?>

			<h3 class="titlehdr">Operations with <?php echo $module['name_module']?></h3>

			<table cellspacing="0" cellpadding="0" class="data table-bordered table-striped w-100pr">
				<thead>
					<tr>
						<th>Rights/Groups</th>
						<?php foreach($groups as $group){?>
							<th><?php echo $group['gr_name']?></th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach($module['rights'] as $right){ ?>
					<tr>
						<td class="tac w-300"><span title="<?php echo $right['r_descr']?>"><?php echo $right['r_name']?></span></td>

						<?php foreach($groups as $group){?>
						<td class="tac">
							<?php if(in_array($right['idright'], $relations[$group['idgroup']])){ ?>

								<a class="ep-icon ep-icon_ok txt-green call-function" data-callback="removeRelation" data-group="<?php echo $group['idgroup']?>" data-right="<?php echo $right['idright']?>" title="Change to disable"></a>
							<?php } else {?>
								<a class="ep-icon ep-icon_remove txt-red call-function" data-callback="createRelation" data-group="<?php echo $group['idgroup']?>" data-right="<?php echo $right['idright']?>" title="Change to enable"></a>
							<?php } ?>
						</td>
						<?php } ?>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		<?php } ?>
	</div>
</div>
