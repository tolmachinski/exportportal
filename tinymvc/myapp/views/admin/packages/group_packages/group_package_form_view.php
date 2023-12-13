<form method="post" class="relative-b validateModal">
	<div class="wr-form-content w-900">
    <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr mt-15 mb-5 vam-table">
        <tbody>
        	<tr>
                <td class="w-100">Group from</td>
                <td>
					<select name="gr_from" class="w-100pr groups">
                        <option value="0">Default</option>
						<?php foreach($groups as $group){?>
						<option value="<?php echo $group['idgroup']?>" <?php echo selected($group['idgroup'], $package_info['gr_from'])?>><?php echo $group['gr_name']?></option>
						<?php }?>
					</select>
                </td>
            </tr>
			<tr>
                <td class="w-100">Group to</td>
                <td>
					<select name="gr_to" class="w-100pr groups">
                        <option value="0">Default</option>
                        <?php foreach($groups as $group){?>
						<option value="<?php echo $group['idgroup']?>" <?php echo selected($group['idgroup'], $package_info['gr_to'])?>><?php echo $group['gr_name']?></option>
                        <?php }?>
                    </select>
                </td>
            </tr>
			<tr>
                <td class="w-100">Downgrade to</td>
                <td>
					<select name="downgrade_gr_to" class="w-100pr groups">
                        <option value="">Select downgrade to group</option>
                        <?php foreach($groups as $group){?>
							<option value="<?php echo $group['idgroup'];?>" <?php echo selected($group['idgroup'], $package_info['downgrade_gr_to'])?>><?php echo $group['gr_name']?></option>
                        <?php }?>
                    </select>
                </td>
            </tr>
			<tr>
                <td class="w-100">Period</td>
                <td>
					<select name="period" class="w-100pr" <?php echo $is_used_package ? 'disabled' : '';?>>
                        <?php foreach($periods as $period){?>
                            <option value="<?php echo $period['id']?>" <?php echo selected($period['id'], $package_info['period'])?>><?php echo $period['full']?></option>
                        <?php }?>
                    </select>
                </td>
            </tr>
			<tr>
                <td class="w-100">Price</td>
                <td>
					<input type="text" name="price" class="validate[required] w-100pr" value="<?php echo $package_info['price']?>"/>
				</td>
            </tr>
			<tr>
                <td class="w-100">Description</td>
                <td>
					<textarea name="description" class="doc-text-block"><?php echo $package_info['description']?></textarea>
				</td>
            </tr>
			<tr>
                <td class="w-100">Default</td>
				<td><input type="checkbox" name="def" <?php echo checked(1, $package_info['def'])?>/></td>
            </tr>
        </tbody>
    </table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($package_info)){?>
		<input type="hidden" name="id" value="<?php echo $package_info['idpack']; ?>"/>
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js?<?php echo time();?>"></script>
<script type="text/javascript">
	$(function(){
		tinymce.init({
			selector:'.doc-text-block',
			menubar: false,
			statusbar : false,
			height : 250,
			plugins: ["image code autolink lists link textcolor preview fullscreen"],
			dialog_type : "modal",
			toolbar: "code fullscreen | fontsizeselect | bold italic underline forecolor backcolor link | numlist bullist | alignleft aligncenter alignright alignjustify",
			fontsize_formats: '8px 10px 12px 14px 16px 18px 20px 22px 24px 36px',
			resize: false
		});
	});

	function modalFormCallBack(form, data_table){
		tinyMCE.triggerSave();
		var $form = $(form);
		$.ajax({
            type: 'POST',
			url: '/group_packages/ajax_group_packages_operations/<?php if(isset($package_info)){?>edit<?php }else{?>add<?php }?>_package',
			data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
            },
            dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					closeFancyBox();
					renew_group_packages_table();
				}else{
					hideLoader($form);
				}
			}
        });
	}


</script>
