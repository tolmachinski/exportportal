<form method="post" class="validateModal relative-b" enctype="multipart/form-data">
	<div class="wr-form-content w-700 h-330">
		<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr vam-table">
			<tr>
				<td>Name person</td>
				<td><input type="text" name="name" class="w-100pr validate[required,custom[validUserName],maxSize[100]]" value="<?php if(isset($person_info)) echo $person_info['name_person']?>" /></td>
			</tr>
			<tr>
				<td>Post</td>
				<td>
					<input type="text" name="post" class="w-100pr validate[required,maxSize[255]]" value="<?php if(isset($person_info)) echo $person_info['post_person']?>" />
				</td>
			</tr>
			<tr>
				<td>Phone</td>
				<td>
					<input type="text" name="tel" class="w-100pr validate[required,maxSize[15]]" value="<?php if(isset($person_info)) echo $person_info['tel_person']?>" />
				</td>
			</tr>
			<tr>
				<td>Email</td>
				<td>
					<input type="text" name="email" class="w-100pr validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]" value="<?php if(isset($person_info)) echo $person_info['email_person']?>" />
				</td>
			</tr>
			<tr>
				<td>Office name</td>
				<td>
					<select class="w-100pr validate[required]" name="office" >
					<?php if(isset($office)){ ?>
						<?php foreach($office as $office_item){?>
							<option value="<?php echo $office_item['id_office']?>" <?php if(isset($person_info)) echo selected($office_item['id_office'], $person_info['id_office'])?>><?php echo $office_item['name_office']?></option>
						<?php }?>
					<?php }?>
					</select>
				</td>
			</tr>
            <tr>
                <td>
                    Description
                </td>
                <td>
                    <textarea class="w-100pr h-100 validate[required]" name="description" placeholder="Document description"><?php if(isset($person_info)) echo $person_info['description']?></textarea>
                </td>
            </tr>
			<tr>
				<td class="w-130">Logo</td>
				<td>
					<span class="btn btn-success fileinput-button">
						<i class="ep-icon ep-icon_plus"></i>
						<span>Select files...</span>
						<!-- The file input field used as target for the file upload widget -->
						<input id="add_fileupload" type="file" name="files[]" multiple>
					</span>
					<span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...</span>
					<div class="info-alert-b mt-10">
						<i class="ep-icon ep-icon_info"></i>
						<div> &bull; The maximum file size has to be 2MB.</div>
						<div> &bull; Min width: 200px, Min height: 200px.</div>
						<div> &bull; You cannot upload more than 1 photo(s).</div>
					</div>

					<!-- The container for the uploaded files -->
					<div class="fileupload-queue files mt-10">
						<?php if(!empty($person_info['img_person'])){?>
							<div class="uploadify-queue-item item-large">
								<div class="img-b">
									<img src="<?php echo $person_info['imageUrl'] ?>" />
								</div>
								<div class="cancel"><a data-action="our_team/ajax_ourteam_delete_db_photo" data-file="<?php echo $person_info['id_person'];?>" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog ep-icon ep-icon_remove txt-gray" href="#" title="Delete"></a></div>
							</div>
						<?php }?>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($person_info)){?>
			<input type="hidden" name="person" value="<?php echo $person_info['id_person'];?>" />
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script>
$(document).ready(function(){
	var url = 'our_team/ajax_ourteam_upload_photo/<?php echo $upload_folder;?>/<?php echo $person_info['id_person'];?>'

	$('#add_fileupload').fileupload({
		url: url,
		dataType: 'json',
		maxFileSize: <?php echo $fileupload_max_file_size;?>,
		beforeSend: function (event, data) {
            var upload_files = data.files.length;
            var total_files = $('.fileupload-queue').find('.uploadify-queue-item').length;

            if(upload_files + total_files > 1){
                event.abort();
                systemMessages( 'You can not upload more than 1 image for one report.', 'error' );
            } else{
                $('.fileinput-loader-btn').fadeIn();
            }
		},
		done: function (e, data) {
			if(data.result.mess_type == 'success'){
				$.each(data.result.files, function (index, file) {
					var itemID = +(new Date());
					$('.fileupload-queue').append(templateFileUpload('img','item-large',itemID));
					$('#fileupload-item-'+itemID+' .img-b').append('<img src="'+file.path+'">');
					$('#fileupload-item-'+itemID+' .img-b').append('<input type="hidden" name="image" value="'+file.path+'">');
					$('#fileupload-item-'+itemID+' .cancel').append('<a data-action="our_team/ajax_ourteam_delete_photo/<?php echo $upload_folder;?>" data-file="'+file.name+'" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog ep-icon ep-icon_remove txt-gray" href="#" title="Delete"></a>');
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

	<?php if(isset($person_info)){?>
		var url = "our_team/ajax_our_team_operation/update_team";
	<?php }else{?>
		var url = "our_team/ajax_our_team_operation/create_team";
	<?php }?>

	$.ajax({
		type: 'POST',
		url: url,
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();

				dtOurteamList.fnDraw();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
