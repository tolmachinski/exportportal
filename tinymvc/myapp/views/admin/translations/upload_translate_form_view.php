<div class="wr-modal-b">
   	<form class="modal-b__form validateModal">
		<div class="modal-b__content pb-0 w-750">
			<div class="row">
				<div class="col-xs-12 mt-15">
					<div class="info-alert-b">
						<i class="ep-icon ep-icon_info"></i>
						<div> &bull; The maximum file size has to be 2MB.</div>
						<div> &bull; You can not upload more than 1 file.</div>
						<div> &bull; File available formats (xls, xlsx).</div>
					</div>
				</div>
				<div class="col-xs-12 mt-15">
					<span class="btn btn-success fileinput-button">
						<i class="ep-icon ep-icon_plus"></i>
						<span>Upload file...</span>
						<!-- The file input field used as target for the file upload widget -->
						<input id="translations_fileupload" type="file" name="translations_file">
					</span>
					<span class="fileinput-loader-btn fileinput-loader-img" style="display:none;">
						<img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...
					</span>
				</div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <button class="btn btn-default pull-right call-function" data-callback="closeFancyBox" type="button">Close</button>
        </div>
   </form>
</div>
<script>
	$(document).ready(function(){
		$('#translations_fileupload').fileupload({
			url: '<?php echo __SITE_URL;?>translations/ajax_upload_file/<?php echo $upload_folder;?>',
			dataType: 'json',
			maxFileSize: '<?php echo $fileupload_max_file_size?>',
			beforeSend: function (xhr, settings) {
				$('.fileinput-loader-btn').fadeIn();
			},
			done: function (e, data) {
				systemMessages( data.result.message, 'message-' + data.result.mess_type );
				$('.fileinput-loader-img').fadeOut();
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
