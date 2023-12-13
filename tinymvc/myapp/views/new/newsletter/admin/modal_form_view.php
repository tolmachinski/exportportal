<form method="post" class="validateModal relative-b">
   <div class="wr-form-content w-700 h-440">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td class="w-100">Visible</td>
					<td>
						<input type="checkbox" name="visible" <?php echo checked(1, $ep_news['visible'])?>/>
					</td>
				</tr>
				<tr>
					<td>Title</td>
					<td>
						<input type="text" name="title" class="validate[required] w-100pr" value="<?php echo ((isset($ep_news['title']) ? $ep_news['title'] : ''))?>" />
					</td>
				</tr>
				<tr>
					<td>Content</td>
					<td>
						<textarea class="w-100pr h-50 validate[required] tinymce" name="content"><?php echo ((isset($ep_news['content']) ? $ep_news['content'] : ''))?></textarea>
					</td>
				</tr>
				<tr>
					<td>Description</td>
					<td>
						<textarea class="w-100pr h-150 validate[required] blog-text-block" name="description" ><?php echo ((isset($ep_news['description']) ? $ep_news['description'] : ''))?></textarea>
					</td>
				</tr>
				<tr>
					<td>Photo</td>
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
							<div> &bull; Min width: 235px, Min height: 100px.</div>
							<div> &bull; You cannot upload more than 1 photo(s).</div>
							<div> &bull; File available formats (jpg,jpeg,png,gif,bmp).</div>
						</div>

						<!-- The container for the uploaded files -->
						<div class="fileupload-queue files mt-10">
						<?php if(isset($ep_news['main_image']) && !empty($ep_news['main_image'])){?>
							<div class="uploadify-queue-item item-large">
								<div class="img-b">
									<img src="<?php echo getDisplayImageLink(['{FILE_NAME}' => $ep_news['main_image']], 'ep_news.main')?>" />
								</div>
								<div class="cancel"><a data-action="ep_news/ajax_ep_news_delete_db_photo" data-file="<?php echo $ep_news['id'];?>" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog ep-icon ep-icon_remove txt-gray" href="#" title="Delete"></a></div>
							</div>
						<?php }?>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($ep_news['id'])){?><input type="hidden" name="id" value="<?php echo $ep_news['id'];?>"/><?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script>
$(document).ready(function(){
	var url = 'ep_news/ajax_ep_news_upload_photo/<?php echo $upload_folder;?><?php if(isset($ep_news)) echo '/'.$ep_news['id'];?>';

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
					$('.fileupload-queue').append(templateFileUpload('img','item-large',itemID));
					$('#fileupload-item-'+itemID+' .img-b').append('<img src="'+file.path+'">');
					$('#fileupload-item-'+itemID+' .img-b').append('<input type="hidden" name="image" value="'+file.path+'">');
					$('#fileupload-item-'+itemID+' .cancel').append('<a data-action="ep_news/ajax_ep_news_delete_files/<?php echo $upload_folder;?>" data-file="'+file.name+'" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog ep-icon ep-icon_remove txt-gray" href="#" title="Delete"></a>');
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

    tinymce.init({
        selector:'.tinymce',
        menubar: false,
        statusbar : false,
        tooltip : false,
        height : 250,
        plugins: ["autolink lists link"],
        dialog_type : "modal",
        style_formats: [
            {title: 'H3', block: 'h3'},
            {title: 'H4', block: 'h4'},
            {title: 'H5', block: 'h5'},
            {title: 'H6', block: 'h6'},
        ],
        toolbar: "styleselect | bold italic underline link | numlist bullist",
		resize: false
    });
});
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL ?>ep_news/ajax_ep_news_operations/<?php echo ((isset($ep_news) ? "edit" : "add"))?>_ep_news',
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
</script>
