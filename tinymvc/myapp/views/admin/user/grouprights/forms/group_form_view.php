<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr">
			<tr>
				<td class="w-140">Group name</td>
				<td><input type="text" name="gr_name" class="validate[required, custom[onlyLetterSp]] w-100pr" value="<?php echo $group['gr_name']?>"></td>
			</tr>
			<tr>
				<td class="w-140">Type of group</td>
				<td>
					<select name="gr_type">
						<option value="">Select group type</option>
						<option value="Buyer" <?php echo selected($group['gr_type'], 'Buyer');?>>Buyer</option>
						<option value="Seller" <?php echo selected($group['gr_type'], 'Seller');?>>Seller</option>
						<option value="Company Staff" <?php echo selected($group['gr_type'], 'Company Staff');?>>Company Staff</option>
						<option value="Shipper" <?php echo selected($group['gr_type'], 'Shipper');?>>Freight Forwarder</option>
						<option value="CR Affiliate" <?php echo selected($group['gr_type'], 'CR Affiliate');?>>CR Affiliate</option>
						<option value="Admin" <?php echo selected($group['gr_type'], 'Admin');?>>Admin</option>
						<option value="EP Staff" <?php echo selected($group['gr_type'], 'EP Staff');?>>EP Staff</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="w-140">Group stamp</td>
				<td>
					<span class="btn btn-success fileinput-button">
						<i class="ep-icon ep-icon_plus"></i>
						<span>Select files...</span>
						<!-- The file input field used as target for the file upload widget -->
						<input id="edit_fileupload" type="file" name="files[]">
					</span>
					<span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...</span>
					<div class="info-alert-b mt-10">
						<i class="ep-icon ep-icon_info"></i>
						<div> &bull; The maximum file size has to be 2MB.</div>
						<div> &bull; You cannot upload more than 1 photo(s).</div>
						<div> &bull; File available formats (jpg,jpeg,png,gif,bmp).</div>
					</div>

					<!-- The container for the uploaded files -->
					<div class="fileupload-queue files mt-10">
						<?php if(isset($group) && !empty($group['stamp_pic'])){?>
							<div class="uploadify-queue-item item-middle">
								<div class="img-b">
									<img src="<?php echo __IMG_URL.'public/img/groups/'.$group['stamp_pic'];?>" alt="img"/>
								</div>
								<div class="cancel"><a data-action="userrights/ajax_delete_db_stamp" data-file="<?php echo $group['idgroup'];?>" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a></div>
							</div>
						<?php }?>
					</div>
				</td>
			</tr>
			<tr>
				<td class="w-140">Group priority</td>
				<td>
					<input name="gr_priority" type="text" class="validate[required, custom[onlyNumberSp]] w-100pr" min="0" max="999" maxlength="3" value="<?php echo $group['gr_priority']?>">
					<div>(<strong>Buyer</strong>: 1-5; <strong>Company staff</strong>: 6-20; <strong>Seller</strong>: 21-49; <strong>EP Staff</strong>: 50-80; <strong>Admin</strong>: 81-99; <strong>Freight Forwarder</strong>: 100-110)</div>
				</td>
			</tr>
			<tr>
				<td class="w-140">Can delete option</td>
				<td>
					<label class="pr-5"><input name="can_delete" type="radio" class="validate[required]" value="1" <?php echo checked($group['can_delete'], '1');?>> Yes </label>
                	<label><input name="can_delete" type="radio" class="validate[required]" value="0" <?php echo checked($group['can_delete'],'0');?>> No </label>
				</td>
			</tr>
		</table>
	</div>

	<div class="wr-form-btns clearfix">
		<?php if(isset($group)){?>
			<input type="hidden" name="idgroup" value="<?php echo $group['idgroup']?>">
		<?php }?>

		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript">
$(document).ready(function(){
	var url_photo = 'userrights/ajax_upload_stamp/<?php echo $upload_folder;?>';
	$('#edit_fileupload').fileupload({
		url: url_photo,
		dataType: 'json',
		maxFileSize: <?php echo $fileupload_max_file_size?>,
		beforeSend: function () {
			$('.fileinput-loader-btn').fadeIn();
		},
		done: function (e, data) {
			if(data.result.mess_type == 'success'){
				$.each(data.result.files, function (index, file) {
					var itemID = +(new Date());
					$('.fileupload-queue').append(templateFileUpload('img','item-middle',itemID));
					$('#fileupload-item-'+itemID+' .img-b').append('<img src="'+file.path+'" alt="img">');
					$('#fileupload-item-'+itemID+' .img-b').append('<input type="hidden" name="images[]" value="'+file.path+'">');
					$('#fileupload-item-'+itemID+' .cancel').append('<a data-action="userrights/ajax_upload_stamp_delete/<?php echo $upload_folder;?>" data-file="'+file.name+'" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a>');
				});
			} else{
				systemMessages( data.result.message, 'message-' + data.result.mess_type );
			}
			$('.fileinput-loader-btn').fadeOut();
		},
		processalways: function(e,data){
			if (data.files.error){
				systemMessages( data.files[0].error, 'message-error' );
			}
		}
	}).prop('disabled', !$.support.fileInput)
	.parent().addClass($.support.fileInput ? undefined : 'disabled');
});

	function modalFormCallBack(form){
		var $form = $(form);
		var fdata = $form.serialize();
		var $wrform = $form.closest('.wr-modal-b');

		<?php if(isset($group)){?>
			var url = "userrights/ajax_userrights_operation/update_group";
		<?php }else{ ?>
			var url = "userrights/ajax_userrights_operation/add_group";
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

					<?php if(isset($group)){?>
						callbackUpdateGroup(resp);
					<?php }else{ ?>
						callbackAddGroup(resp);
					<?php }	?>
				}else{
					$form.find('button[type=submit]').removeClass('disabled');
				}
			}
		});
	}
</script>
