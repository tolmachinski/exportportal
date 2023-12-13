<form method="post" class="relative-b validateModal">
	<div class="wr-form-content w-550">
    <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr mt-15 mb-5 vam-table">
        <tbody>
			<tr>
                <td class="w-100">Group for</td>
                <td>
					<select name="group_for" class="group_for-modal">
                        <option value="0">Default</option>
						<?php foreach($groups as $group){?>
						<option value="<?php echo $group['idgroup']?>" <?php echo selected($group['idgroup'], $package_info['group_for'])?>><?php echo $group['gr_name']?></option>
						<?php }?>
					</select>
                </td>
            </tr>
			<tr>
                <td class="w-100">Right</td>
                <td>
					<select name="right" class="right-modal">
                        <option value="0">Default</option>
						<?php foreach($bymodules as $module){ ?>
                            <optgroup label="<?php echo $module['name_module'];?>">
                            <?php foreach($module['rights'] as $right){?>
                                <option title="<?php echo $right['r_descr'];?>" value="<?php echo $right['idright'];?>" <?php echo selected($right['idright'], $package_info['id_right']);?>><?php echo $right['r_name'];?></option>
                            <?php } ?>
                            </optgroup>
                        <?php } ?>
                    </select>
                </td>
            </tr>
			<tr>
                <td class="w-100">Period</td>
                <td>
					<select name="period">
                        <?php foreach($periods as $period){?>
                            <option value="<?php echo $period['id']?>" <?php echo selected($period['id'], $package_info['id_period'])?>><?php echo $period['full']?></option>
                        <?php }?>
                    </select>
                </td>
            </tr>
			<tr>
                <td class="w-100">Price</td>
                <td>
					<input type="text" name="price" class="validate[required]" value="<?php echo $package_info['price']?>"/>
				</td>
            </tr>
        </tbody>
    </table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($package_info)){?>
			<input type="hidden" name="id" value="<?php echo $package_info['idrpack']; ?>"/>
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript">
	$('select.group_for-modal').on('change', function(){
		get_group_rights($(this).find('option:selected').val());
	});
	
	function get_group_rights(id_group){
		var $right_select = $('select.right-modal');
		$.ajax({
			url: '/rights_packages/ajax_rights_packages_operations/get_rights_for_group/'+id_group,
			dataType: 'JSON',
			beforeSend: function(){
				$right_select.prop('disabled', true);
			},
			success: function(resp){
				$right_select.prop('disabled', false);
			}
		});
	}
	
	get_group_rights($('select.group_for-modal option:selected').val());
	
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
			url: '/rights_packages/ajax_rights_packages_operations/<?php if(isset($package_info)){?>edit<?php }else{?>add<?php }?>_right_package',
			data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
            },
            dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					closeFancyBox();
					renew_right_packages_table();
				}else{
					hideLoader($form);
				}
			}
        });
	}


</script>
