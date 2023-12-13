<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700 h-420">
		<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr">
			<tr>
				<td class="w-140">Right name</td>
				<td><input class="validate[required] w-100pr" type="text" name="r_name" value="<?php echo $right['r_name']?>" ></td>
			</tr>
			<tr>
				<td>Right alias</td>
            	<td>
            		<input class="validate[required] w-100pr" type="text" name="r_alias" value="<?php echo $right['r_alias']?>">
            		<div class="fs-12">*example: right_alias(without space - will be used in code)</div>
            	</td>
			</tr>
			<tr>
				<td>Right description</td>
				<td>
					<textarea class="validate[required] w-100pr h-120" name="r_descr"><?php echo $right['r_descr']?></textarea>
				</td>
			</tr>
			<tr>
				<td>Ep Module</td>
				<td>
					<select name="r_module" class="validate[required] w-100pr">
					<?php foreach($bymodule as $mod){?>
						<option value="<?php echo $mod['id_module'] ?>" <?php echo selected($right['r_module'], $mod['id_module']);?>><?php echo $mod['name_module'];?></option>
					<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Can delete option</td>
				<td>
					<label><input name="rcan_delete" class="validate[required]" type="radio" value="1" <?php echo checked($right['rcan_delete'], '1')?>> Yes </label>
					<label><input name="rcan_delete" class="validate[required]" type="radio" value="0" <?php echo checked($right['rcan_delete'], '0')?>> No </label>
				</td>
			</tr>
			<tr>
				<td>Can share to staff</td>
				<td>
					<label><input  name="share_to_staff" class="validate[required]" type="radio" value="1" <?php echo checked($right['share_to_staff'], '1');?>> Yes </label>
					<label><input  name="share_to_staff" class="validate[required]" type="radio" value="0" <?php echo checked($right['share_to_staff'], '0');?>> No </label>
				</td>
			</tr>
			<tr>
				<td>Has field</td>
				<td><label><input  name="has_field" type="checkbox" class="add-field" value="1" <?php echo checked($right['has_field'], '1'); ?>> Yes</label></td>
			</tr>
			<tr class="additional-field <?php if(!isset($right) || $right['has_field'] == 0){?>display-n <?php }?>">
				<td>Field type</td>
				<td>
					<select class="w-100pr" name="type_field">
						<option value="simple" <?php echo selected($right['type'], 'simple');?>>Simple</option>
						<option value="social" <?php echo selected($right['type'], 'social');?>>Social</option>
					</select>
				</td>
			</tr>
			<tr class="additional-field <?php if(!isset($right) || $right['has_field'] == 0){?>display-n <?php }?>">
				<td>Field name</td>
				<td>
					<input class="validate[required] w-100pr" type="text" name="name_field" value="<?php echo $right['name_field']?>">
				</td>
			</tr>
			<tr class="additional-field <?php if(!isset($right) || $right['has_field'] == 0){?>display-n <?php }?>">
				<td>Field sample</td>
				<td>
					<input class=" w-100pr" type="text" name="sample_field" value="<?php echo $right['sample_field']?>">
				</td>
			</tr>
			<tr class="additional-field <?php if(!isset($right) || $right['has_field'] == 0){?>display-n <?php }?>">
				<td>Validation rule (same method name for back and front)</td>
				<td>
					<input class=" w-100pr" type="text" name="valid_rule"  value="<?php echo $right['valid_rule']?>">
				</td>
			</tr>
			<tr class="additional-field <?php if(!isset($right) || $right['has_field'] == 0){?>display-n <?php }?>">
				<td>Icon</td>
				<td>
					<input class="w-100pr" type="text" name="icon" value="<?php echo $right['icon']?>">

					<!--<span class="btn btn-success fileinput-button">
						<i class="ep-icon ep-icon_plus"></i>
						<span>Select files...</span>
						<input id="edit_fileupload" type="file" name="files[]">
					</span>
					<span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...</span>

					<div class="fileupload-queue files mt-10">
						<?php //if(isset($right) && !empty($right['icon'])){?>
							<div class="uploadify-queue-item item-small">
								<div class="img-b">
									<img src="<?php echo __IMG_URL.'public/img/rights_icon/'.$right['icon'];?>" alt="img"/>
								</div>
								<div class="cancel"><a data-action="userrights/ajax_delete_db_icon" data-file="<?php echo $right['id_right'];?>" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a></div>
							</div>
						<?php //}?>
					</div>-->
				</td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($right)){?>
			<input type="hidden" name="old_has_field" value="<?php echo $right['has_field']?>" >
			<input type="hidden" name="idright" value="<?php echo $right['idright']?>" >
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript">
$(document).ready(function(){

//	var url_photo = 'userrights/ajax_upload_icon/<?php echo $upload_folder;?>';
//	$('#edit_fileupload').fileupload({
//		url: url_photo,
//		dataType: 'json',
//		beforeSend: function () {
//			$('.fileinput-loader-btn').fadeIn();
//		},
//		done: function (e, data) {
//			if(data.result.mess_type == 'success'){
//				$.each(data.result.files, function (index, file) {
//					var itemID = +(new Date());
//					$('.fileupload-queue').append(templateFileUpload('img','item-middle',itemID));
//					$('#fileupload-item-'+itemID+' .img-b').append('<img src="'+file.path+'" alt="img">');
//					$('#fileupload-item-'+itemID+' .img-b').append('<input type="hidden" name="images[]" value="'+file.path+'">');
//					$('#fileupload-item-'+itemID+' .cancel').append('<a data-action="userrights/ajax_upload_icon_delete/<?php echo $upload_folder;?>" data-file="'+file.name+'" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a>');
//				});
//			} else{
//				systemMessages( data.result.message, 'message-' + data.result.mess_type );
//			}
//			$('.fileinput-loader-btn').fadeOut();
//		},
//		progressall: function (e, data) {
//		}
//	}).prop('disabled', !$.support.fileInput)
//	.parent().addClass($.support.fileInput ? undefined : 'disabled');

	$(".add-field").on('click', function(){
		if($(this).prop('checked'))
			$(this).closest("tbody").find(".additional-field").fadeIn();
		else
			$(this).closest("tbody").find(".additional-field").hide();
	});
});

	function modalFormCallBack(form){
		var $form = $(form);
		var $wrform = $form.closest('.wr-modal-b');
		var fdata = $form.serialize();

		<?php if(isset($right)){?>
			var url = "userrights/ajax_userrights_operation/update_right";
		<?php }else{ ?>
			var url = "userrights/ajax_userrights_operation/add_right";
		<?php }	?>

		$.ajax({
			type: 'POST',
			url: url,
			data: fdata,
			dataType: 'JSON',
			beforeSend: function(){
				showFormLoader($wrform, 'Sending right...');
				$form.find('button[type=submit]').addClass('disabled');
			},
			success: function(resp){
				hideFormLoader($wrform);
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					closeFancyBox();

					<?php if(isset($right)){?>
						callbackUpdateRight(resp);
					<?php }else{ ?>
						callbackAddRight(resp);
					<?php }	?>
				}else{
					$form.find('button[type=submit]').removeClass('disabled');
				}
			}
		});
	}
</script>
