<?php
use function GuzzleHttp\json_decode;
tmvc::instance()->controller->view->display('new/file_upload_scripts'); ?>
<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal">
		<div class="modal-flex__content mh-600">
			<div class="container-fluid-modal">
				<div class="row">
					<div class="col-12 col-md-6">
						<label class="input-label input-label--required">Title</label>
                        <input class="validate[required, maxSize[255]]" value="<?php if(!empty($ereport)){ echo $ereport['ereport_title'];}?>" type="text" name="title" placeholder="Title"/>
					</div>

					<div class="col-12 col-md-6">
						<label class="input-label input-label--required">Refund amount, (USD)</label>
                        <input class="validate[required,custom[number],min[0]]" value="<?php if(!empty($ereport)){ echo $ereport['ereport_refund_amount'];}?>" type="text" name="refund_amount" placeholder="0.00"/>
					</div>

					<div class="col-12">
						<label class="input-label input-label--required">Description</label>
						<textarea name="description" placeholder="Write your description here" class="validate[required,maxSize[1000]] textcounter-ereport_description" data-max="1000"><?php if(!empty($ereport)){echo $ereport['ereport_description'];}?></textarea>
					</div>

					<div class="col-12">
						<label class="input-label">Add photo</label>

						<span class="btn btn-dark mnw-125 fileinput-button">
							<span>Select files...</span>
							<!-- The file input field used as target for the file upload widget -->
							<input id="add_fileupload" type="file" name="files[]" multiple>
						</span>
						<span class="fileinput-loader-btn" style="display:none;"><img class="image" src="<?php echo __IMG_URL;?>public/img/loader.svg" alt="loader"> Uploading...</span>

						<div class="info-alert-b mt-10">
							<i class="ep-icon ep-icon_info-stroke"></i>
							<div> &bull; The maximum file size has to be <?php echo $fileupload_limits['image_size_readable']; ?>.</div>
							<div> &bull; Min width: 250px, Min height: 250px.</div>
							<div> &bull; You cannot upload more than <?php echo $fileupload_limits['amount']; ?> photo(s).</div>
							<div> &bull; File available formats (jpg,jpeg,png,gif,bmp).</div>
						</div>

						<!-- The container for the uploaded files -->
						<div class="fileupload mt-10">
							<?php if(!empty($ereport)){?>
								<?php $ereport_files = json_decode($ereport['ereport_photos'], true);?>
								<?php if(!empty($ereport_files)){?>
									<?php foreach($ereport_files as $file_key => $ereport_file){?>
										<div class="fileupload-item ">
											<div class="fileupload-item__image">
												<span class="link">
													<img class="image" src="<?php echo __IMG_URL . "public/expense_reports/{$ereport['id_ereport']}/{$ereport_file['name']}";?>">
												</span>
											</div>
											<div class="fileupload-item__actions">
												<a class="btn btn-dark confirm-dialog" data-action="<?php echo __SITE_URL . "cr_expense_reports/ajax_operations/delete_file/{$ereport['id_ereport']}";?>" data-file="<?php echo $file_key;?>" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" href="#" title="Delete">Delete</a>
											</div>
										</div>
									<?php }?>
								<?php }?>
							<?php }?>
						</div>
					</div>
				</div>
            </div>

			<?php if(!empty($ereport)){?>
				<input type="hidden" name="id" value="<?php echo $ereport['id_ereport']; ?>"/>
			<?php }?>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Send</button>
            </div>
		</div>
	</form>
</div>
<script type="text/javascript">
	function modalFormCallBack(form, data_table){
        var $form = $(form);
        var $wrapper = $form.closest('.js-modal-flex');

		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . "cr_expense_reports/ajax_operations/" . ( !empty($ereport) ? "edit" : "add");?>',
			data: $form.serialize(),
            beforeSend: function () {
                showLoader($wrapper);
            },
            dataType: 'json',
			success: function(data){
				systemMessages( data.message, data.mess_type );

				if(data.mess_type == 'success'){
					closeFancyBox();

					if(data_table != undefined){
						data_table.fnDraw();
					}
				} else{
					hideLoader($wrapper);
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
        $('#add_fileupload').fileupload({
			url: __site_url + 'cr_expense_reports/ajax_operations/upload_files/<?php echo $upload_folder;?>',
			dataType: 'json',
			maxFileSize: intval('<?php echo $fileupload_limits['image_size']; ?>'),
            acceptFileTypes: imageFormats,
			beforeSend: function (event, data) {
                var upload_files = data.files.length;
                var total_files = $('.fileupload').find('.fileupload-item').length;

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

                        var image_params = {
                            type: 'imgnolink',
                            index: itemID,
                            image_link: file.path,
                            image: '<img class="image" src="' + file.path + '"> <input type="hidden" name="images['+ itemID +']" value="'+file.path+'">'
                        };

						$('.fileupload').append(templateFileUploadNew(image_params));
						$('#fileupload-item-'+itemID+' .fileupload-item__actions').append('<a class="btn btn-dark confirm-dialog" data-action="<?php echo __SITE_URL;?>cr_expense_reports/ajax_operations/delete_file/<?php echo $upload_folder;?>" data-file="'+file.name+'" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" href="#" title="Delete">Delete</a>');
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
