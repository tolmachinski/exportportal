<script type="text/javascript">
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: "mass_media/ajax_media_operation/<?php echo (isset($media) ? 'update' : 'create')?>_media",
		data: $form.serialize(),
		beforeSend: function () {
			showLoader($form);
		},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );

			if(data.mess_type == 'success'){
				closeFancyBox();
				if(data_table != undefined)
					data_table.fnDraw();
			}else{
				hideLoader($form);
			}
		}
	});
}

$(document).ready(function(){

	var url = 'mass_media/ajax_media_upload_photo/<?php echo $upload_folder;?>/<?php echo $media['id_media'];?>'

	$('#add_fileupload').fileupload({
		url: url,
		dataType: 'json',
		maxFileSize: <?php echo $fileupload_max_file_size?>,
		beforeSend: function () {
			$('.fileinput-loader-btn').fadeIn();
		},
		done: function (e, data) {
			if(data.result.mess_type == 'success'){
				$.each(data.result.files, function (index, file) {
					var itemID = +(new Date());
					$('.fileupload-queue').append(templateFileUpload('img','item-small',itemID));
					$('#fileupload-item-'+itemID+' .img-b').append('<img src="'+file.path+'" alt="img">');
					$('#fileupload-item-'+itemID+' .img-b').append('<input type="hidden" name="image" value="'+file.path+'">');
					$('#fileupload-item-'+itemID+' .cancel').append('<a data-action="mass_media/ajax_media_delete_photo/<?php echo $upload_folder;?>" data-file="'+file.name+'" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a>');
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
</script>

<form class="validateModal relative-b" method="post"  enctype="multipart/form-data">
	<div class="wr-form-content w-700 h-220">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tr>
				<td class="w-150">Name</td>
				<td><input type="text" name="title" class="w-100pr validate[required,maxSize[150]]" value="<?php if(isset($media)) echo $media['title_media']?>" /></td>
			</tr>
			<tr>
				<td>Type</td>
				<td>
					<select class="w-100pr validate[required]" name="type" >
						<option value="tv" <?php if(!isset($media)) echo "selected=\"selected\"" ?> <?php echo selected($media['type_media'],'tv')?>>TV</option>
						<option value="radio" <?php echo selected($media['type_media'],'radio')?>>Radio</option>
						<option value="newspaper" <?php echo selected($media['type_media'],'newspaper')?>>Newspaper</option>
						<option value="website" <?php echo selected($media['type_media'],'website')?>>Website</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Logo</td>
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
						<div> &bull; Min width: 250px, Min height: 140px.</div>
						<div> &bull; You cannot upload more than 1 photo(s).</div>
						<div> &bull; File available formats (jpg,jpeg,png,gif,bmp).</div>
					</div>

					<!-- The container for the uploaded files -->
					<div class="fileupload-queue files mt-10">
						<?php if(!empty($media['logo_media'])){?>
							<div class="uploadify-queue-item item-small">
								<div class="img-b">
									<img src="<?php echo $media['imageUrl'] ?>" />
								</div>
								<div class="cancel"><a data-action="mass_media/ajax_media_delete_db_photo" data-file="<?php echo $media['id_media'];?>" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a></div>
							</div>
						<?php }?>
					</div>
				</td>
			</tr>
			<tr>
				<td>Website</td>
				<td><input class="w-100pr validate[required, custom[url]]" type="text" name="website" value="<?php if(isset($media)) echo $media['website_media']?>" /></td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($media)){?>
			<input type="hidden" name="media"  value="<?php echo $media['id_media'];?>" />
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
