<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>
<form method="post" class="relative-b validateModal">
	<input type="hidden" value="<?php echo $fileupload['directory']; ?>" name="upload_folder" />
	<div class="wr-form-content w-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr m-auto vam-table mt-15">
        <tbody>
            <tr>
                <td class="w-100">Order</td>
                <td>
                    <?php echo orderNumber($dispute['id_order']);?>
                </td>
            </tr>
			<tr>
                <td class="w-100">Data</td>
                <td>
                    <?php echo formatDate($dispute['date_time']);?>
                </td>
            </tr>
			<tr>
                <td class="w-100">Refund</td>
                <td class="vam">
					<label class="lh-30 pr-10 tac"><input type="radio" name="refund_money" class="radio" value="0" <?php echo (($dispute['money_back_request'] != 0) ? '' : 'checked="checked"')?>> No</label>
					<label class="lh-30 tac"><input type="radio" name="refund_money" class="radio" value="1" <?php echo (($dispute['money_back_request'] != 0) ? 'checked="checked"' : '')?>> Yes</label>
					<input class="validate[max[<?php echo $dispute['max_price']?>], custom[positive_number]] money w-100 ml-10" type="text" name="money_count" value="<?php echo (isset($dispute['money_back_request']) ? $dispute['money_back_request'] : $dispute['max_price'])?>" <?php echo (($dispute['money_back_request'] != 0) ? '' : 'style="display: none;"')?>>
				</td>
            </tr>
			<tr>
                <td class="w-100">Notice</td>
                <td>
					<textarea class="w-100pr h-100 validate[required,maxSize[500]] textcounter-dispute_notice" data-max="500" name="notice"></textarea>
                </td>
            </tr>
			<tr>
				<td>Add Photo</td>
				<td>
					<span class="btn btn-success fileinput-button">
						<i class="ep-icon ep-icon_plus"></i>
						<span>Select files...</span>
						<!-- The file input field used as target for the file upload widget -->
						<input id="add_fileupload" type="file" name="files[]" multiple accept="<?php echo arrayGet($fileupload, 'limits.type.accept'); ?>">
					</span>
					<span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...</span>
					<div class="info-alert-b mt-10">
						<i class="ep-icon ep-icon_info"></i>
						<div> &bull; The maximum file size has to be 2MB.</div>
						<div> &bull; Min width: 250px, Min height: 250px.</div>
						<div> &bull; You cannot upload more than <?php echo arrayGet($fileupload, 'limits.amount.allowed'); ?> of <?php echo arrayGet($fileupload, 'limits.amount.total'); ?> photo(s).</div>
						<div> &bull; File available formats (jpg,jpeg,png,gif,bmp).</div>
					</div>

					<!-- The container for the uploaded files -->
					<div class="fileupload-queue files mt-10"></div>
				</td>
			</tr>
			<tr>
				<td>Add Video</td>
				<td>
					<input type="text" name="video_link"/>
				</td>
			</tr>
        </tbody>
    </table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="dispute" value="<?php echo $dispute['id']; ?>"/>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript">
	$(function(){
		$('.textcounter-dispute_notice').textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		});

		$('.radio').click(function(){
			if($(this).attr("value") == 1)
				$(".money").show();
			else
				$(".money").hide();
		});

		var count = 0;
		$('#add_fileupload').fileupload({
			url: __site_url + 'dispute/ajax_operation/upload_photo/<?php echo arrayGet($fileupload, 'directory'); ?>',
			dataType: 'json',
			maxFileSize: "<?php echo arrayGet($fileupload, 'limits.filesize.size', 0); ?>",
			beforeSend: function () {
				$('.fileinput-loader-btn').fadeIn();
			},
			done: function (e, data) {
				if(data.result.mess_type == 'success'){
					var file = data.result.files;
                    var itemID = +(new Date());
                    $('.fileupload-queue').append(templateFileUpload('img','item-middle',itemID));
                    $('#fileupload-item-'+itemID+' .img-b').append('<img src="'+file.fullPath+'">');
                    $('#fileupload-item-'+itemID+' .img-b').append('<input type="hidden" name="images['+ count++ +']" value="'+file.path+'">');
                    $('#fileupload-item-'+itemID+' .cancel').append('<a data-action="dispute/ajax_operation/delete_temp_photo/<?php echo arrayGet($fileupload, 'directory'); ?>" data-file="'+file.name+'" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a>');
                    //$('#fileupload-item-'+itemID+' .cancel').append('<a data-file="'+file.name+'" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a>');
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

	function modalFormCallBack(form, data_table){
		if($('.radio:checked').val() == 0)
			$('.money').val('0');

		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>dispute/ajax_operation/edit',
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
