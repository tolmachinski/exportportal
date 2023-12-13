<form id="add-article-form" method="post" class="validateModal relative-b">
   <div class="wr-form-content w-700 h-440">
		<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr vam-table">
			<tbody>
				<tr>
					<td class="w-100">Visible</td>
					<td>
						<input type="checkbox" name="visible" checked="checked"/>
					</td>
				</tr>
				<tr>
					<td class="w-100">Country</td>
					<td>
						<select class="w-100pr validate[required]" name="country" >
							<?php foreach($port_country as $country){?>
								<option value="<?php echo $country['id']?>"><?php echo $country['country']?></option>
							<?php }?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="w-100">Type</td>
					<td>
						<label><input type="radio" name="type" value="1" checked="checked"/> Import</label>
						<label><input type="radio" name="type" value="0"/> Export</label>
					</td>
				</tr>
				<tr>
					<td>Meta Keywords</td>
					<td>
						<textarea class="w-100pr h-50 validate[required]" name="meta_key"></textarea>
					</td>
				</tr>
				<tr>
					<td>Meta Description</td>
					<td>
						<textarea class="w-100pr h-50 validate[required]" name="meta_desc"></textarea>
					</td>
				</tr>
				<tr>
					<td>Text</td>
					<td>
						<textarea class="w-100pr h-150 validate[required] article-text-block" name="text"></textarea>
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
							<div> &bull; Min width: 200px, Min height: 200px.</div>
							<div> &bull; You cannot upload more than 1 photo(s).</div>
							<div> &bull; File available formats (jpg,jpeg,png,gif,bmp).</div>
						</div>
						<!-- The container for the uploaded files -->
						<div class="fileupload-queue files mt-10"></div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script>
function fileuploadRemoveFromJsOnly($thisBtn){
    $thisBtn.closest('.uploadify-queue-item').remove();
}
$(document).ready(function(){
	var url = 'country_articles/ajax_upload_temp_photo/';
    var uploadFileLimit = intval('<?php echo $fileupload_total; ?>');

	$('#add_fileupload').fileupload({
		url: url,
		dataType: 'json',
        maxNumberOfFiles: 1,
		maxFileSize: <?php echo $fileupload_max_file_size?>,
		beforeSend: function (event, data) {
            var upload_files = data.files.length;
            var total_files = $('.fileupload-queue').find('.uploadify-queue-item').length;

            if(upload_files + total_files > uploadFileLimit){
                event.abort();
                systemMessages( 'You can not upload more than '+ uploadFileLimit +' files for one report.', 'error' );
            } else{
                $('.fileinput-loader-btn').fadeIn();
            }
        },
		done: function (e, data) {
			if(data.result.mess_type == 'success'){
				var file = data.result.image;
                var itemID = +(new Date());
                $('.fileupload-queue').append(templateFileUpload('img','',itemID));
                $('#fileupload-item-'+itemID+' .img-b').append('<img src="'+file.fullPath+'">');
                $('#fileupload-item-'+itemID+' .img-b').append('<input type="hidden" name="image" value="'+file.path+'">');
                $('#fileupload-item-'+itemID+' .cancel').append('<a data-file="'+file.name+'" data-callback="fileuploadRemoveFromJsOnly" data-message="Are you sure you want to delete this image?" class="confirm-dialog ep-icon ep-icon_remove txt-gray" href="#" title="Delete"></a>');

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
		selector:'.article-text-block',
		menubar: false,
		statusbar : false,
		height : 250,
		plugins: ["autolink lists link textcolor"],
		dialog_type : "modal",
		toolbar: "bold italic underline forecolor backcolor link | numlist bullist",
		resize: false
	});
});

function modalFormCallBack(form, data_table){
	tinymce.triggerSave();
	var $form = $(form);
	var fdata = $form.serialize();

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>country_articles/ajax_articles_operation/save_article',
		data: fdata,
		beforeSend: function(){ showLoader($form); },
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
