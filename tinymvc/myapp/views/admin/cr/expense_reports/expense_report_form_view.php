<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<form  class="validateModal relative-b" method="post">
	<div class="wr-form-content w-700">
        <div class="row">
            <div class="col-xs-12">
                <label class="modal-b__label">Title</label>
                <input value="<?php echo $ereport['ereport_title'];?>" type="text" name="title" class="form-control validate[required,maxSize[255]]"/>
            </div>
            <div class="col-xs-12">
                <label class="modal-b__label">Description</label>
                <textarea class="form-control validate[required] text-block textcounter-ereport_description" data-max="1000" name="description" placeholder="Write description here"><?php echo $ereport['ereport_description'];?></textarea>
            </div>
            <div class="col-xs-12 col-sm-6">
                <label class="modal-b__label">Refund amount</label>
                <input type="text" class="form-control validate[required,custom[number],min[0]]" name="refund_amount" placeholder="0.00" value='<?php echo $ereport['ereport_refund_amount'];?>'>
            </div>
            <div class="col-xs-12 col-sm-6">
                <label class="modal-b__label">Status</label>
                <select name="status" class="form-control validate[required]">
                    <option value="init" <?php echo selected($ereport['ereport_status'], 'init');?>>Init</option>
                    <option value="in_progress" <?php echo selected($ereport['ereport_status'], 'in_progress');?>>In progress</option>
                    <option value="processed" <?php echo selected($ereport['ereport_status'], 'processed');?>>Processed</option>
                    <option value="declined" <?php echo selected($ereport['ereport_status'], 'declined');?>>Declined</option>
                </select>
            </div>
            <div class="col-xs-12">
                <label class="modal-b__label">Images</label>
                <span class="btn btn-success fileinput-button">
                    <i class="ep-icon ep-icon_plus"></i>
                    <span>Select files...</span>
                    <!-- The file input field used as target for the file upload widget -->
                    <input id="edit_fileupload" type="file" name="files[]" multiple>
                </span>
                <span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL?>public/img/loader.gif" alt="loader"> Uploading...</span>
                <div class="info-alert-b mt-10">
                    <i class="ep-icon ep-icon_info"></i>
                    <div> &bull; The maximum file size has to be <?php echo $fileupload_limits['image_size_readable']; ?>.</div>
                    <div> &bull; Min width: 250px, Min height: 250px.</div>
                    <div> &bull; You cannot upload more than <?php echo $fileupload_limits['amount']; ?> photo(s).</div>
                    <div> &bull; File available formats (jpg,jpeg,png,gif,bmp).</div>
                </div>

                <!-- The container for the uploaded files -->
                <div class="fileupload-queue files mt-10">
                    <?php if(!empty($ereport['ereport_photos'])){?>
                        <?php $photos = json_decode($ereport['ereport_photos'], true);?>
                        <?php if(!empty($photos)){?>
                            <?php foreach($photos as $file_key => $photo){ ?>
                                <div class="uploadify-queue-item item-middle">
                                    <div class="img-b">
                                        <img src="<?php echo __IMG_URL . "public/expense_reports/{$ereport['id_ereport']}/{$photo['name']}";?>">
                                    </div>

                                    <div class="cancel">
                                        <a class="confirm-dialog" data-action="<?php echo __SITE_URL . "cr_expense_reports/ajax_operations/delete_file/{$ereport['id_ereport']}";?>" data-file="<?php echo $file_key;?>" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a>
                                    </div>
                                </div>
                            <?php }?>
                        <?php }?>
                    <?php }?>
                </div>
            </div>
        </div>
    </div>

	<div class="wr-form-btns clearfix">
        <input type="hidden" name="id" value="<?php echo $ereport['id_ereport'];?>"/>
        <button class="btn btn-primary pull-right" type="submit">Save</button>
	</div>
</form>

<script type="text/javascript">
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            dataType: 'json',
            url: __site_url + 'cr_expense_reports/ajax_operations/edit',
			data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
            },
			success: function(data){
				systemMessages( data.message, data.mess_type );
                hideLoader($form);

				if(data.mess_type == 'success'){
					closeFancyBox();
					if(data_table != undefined){
						data_table.fnDraw();
                    }
				}
			}
        });
	}

    $(function() {
        $('.textcounter-ereport_description').textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		});

        var uploadFileLimit = intval('<?php echo $fileupload_limits['amount']; ?>');
        var imageTypes = new RegExp('(<?php echo $fileupload_limits['mimetypes']; ?>)', 'i');
        var imageFormats = new RegExp('(.|\/)(<?php echo $fileupload_limits['formats']; ?>)', 'i');
        $('#edit_fileupload').fileupload({
			url: __site_url + 'cr_expense_reports/ajax_operations/upload_files/<?php echo $upload_folder;?>',
			dataType: 'json',
			maxFileSize: intval('<?php echo $fileupload_limits['image_size']; ?>'),
            acceptFileTypes: imageFormats,
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
					$.each(data.result.files, function (index, file) {
						var itemID = uniqid();
                        $('.fileupload-queue').append(templateFileUpload('image','item-middle',itemID));
                        $('#fileupload-item-'+itemID+' .img-b').append('<img src="' + file.path + '"> <input type="hidden" name="images[]" value="'+file.path+'">');
                        $('#fileupload-item-'+itemID+' .cancel').append('<a class="confirm-dialog" data-action="'+__site_url+'cr_expense_reports/ajax_operations/delete_file/<?php echo $upload_folder;?>" data-file="'+file.name+'" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a>');
                        $.fancybox.update();
					});
				} else{
					systemMessages( data.result.message, data.result.mess_type );
				}

				$('.fileinput-loader-btn').fadeOut();
			},
			processalways: function(e,data){
				if (data.files.error){
					systemMessages( data.files[0].error, 'error' );
				}
			}
		}).prop('disabled', !$.support.fileInput)
			.parent().addClass($.support.fileInput ? undefined : 'disabled');
    });
</script>
