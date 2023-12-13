<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal inputs-40"
        data-callback="prototypeEditFormCallBack"
    >
		<input type="hidden" name="prototype" value="<?php echo $prototype['id_prototype'];?>"/>

		<div class="modal-flex__content">
			<label class="input-label input-label--required">Title</label>
			<input type="text" name="title" class="validate[required,minSize[4],maxSize[255]]" value="<?php echo $prototype['title']?>"/>

			<div class="form-group">
				<label class="input-label input-label--required">Image</label>
				<div class="info-alert-b">
					<i class="ep-icon ep-icon_info-stroke"></i>
					<div><?php echo translate('general_dashboard_modal_field_image_help_text_line_1', array('[[SIZE]]' => $fileupload['limits']['filesize_readable'])); ?></div>
					<div><?php echo translate('general_dashboard_modal_field_image_help_text_line_2', array('[[WIDTH]]' => $fileupload['limits']['width'], '[[HEIGHT]]' => $fileupload['limits']['height'])); ?></div>
					<div><?php echo translate('general_dashboard_modal_field_image_help_text_line_3', array('[[AMOUNT]]' => $fileupload['limits']['amount'])); ?></div>
					<div>• <?php echo translate('general_dashboard_modal_field_image_help_text_line_4', array('[[FORMATS]]' => str_replace('|', ',', $fileupload['limits']['formats']))); ?></div>
				</div>
			</div>

			<span class="btn btn-dark mnw-125 fileinput-button">
				<span>Select file</span>
				<!-- The file input field used as target for the file upload widget -->
				<input id="edit_fileupload" type="file" name="files" accept="<?php echo $fileupload['limits']['accept']; ?>">
			</span>
			<span class="fileinput-loader-btn" style="display:none;"><img class="image" src="<?php echo __IMG_URL;?>public/img/loader.svg" alt="loader"> Uploading...</span>

			<!-- The container for the uploaded files -->
			<div class="fileupload">
				<?php if($prototype['image']){?>
					<div class="fileupload-item">
						<div class="fileupload-item__image image-card3">
							<span class="link">
								<img
                                    class="image"
                                    src="<?php echo getDisplayImageLink(array('{ID}' => $prototype['id_prototype'], '{FILE_NAME}' => $prototype['image']), 'items.prototype', array( 'thumb_size' => 1 )); ?>"
                                    alt="<?php echo $prototype['title']?>"
                                />
							</span>
						</div>
						<div class="fileupload-item__actions">
							<a class="btn btn-dark confirm-dialog"
								data-action="<?php echo "{$fileupload['url']['delete']}/{$prototype["id_prototype"]}"; ?>"
								data-file=""
								data-callback="fileploadRemovePrototype"
								data-message="Are you sure you want to delete this image?"
								href="#"
								title="Delete">
								Delete
							</a>
						</div>
					</div>
				<?php }?>
			</div>

			<div class="container-fluid-modal">
				<div class="row">
					<div class="col-12 col-md-6">
						<label class="input-label input-label--required">Price in USD</label>
						<input class="validate[required,custom[positive_number],min[1]]" type="text" name="price" value="<?php echo $prototype['price']?>" placeholder="e.g. 100">
					</div>
					<div class="col-12 col-md-6">
						<label class="input-label input-label--required">Quantity, <?php echo $prototype['unit_name']?></label>
						<input class="validate[required,min[1],custom[positive_integer],maxSize[10]]" type="text" name="quantity" value="<?php echo $prototype['quantity']?>" placeholder="e.g. 1"/>
					</div>
					<div class="col-12 col-md-6">
						<label class="input-label input-label--required">Weight (Kg)</label>
						<input class="validate[required,custom[positive_number],maxSize[15]]" type="text" name="weight" value="<?php echo $prototype['prototype_weight']?>" placeholder="e.g. 10"/>
					</div>

					<div class="col-12 col-md-6">
						<label class="input-label input-label--required">Size,(cm, LxWxH)</label>
						<div class="input-group">
							<input class="flex--1 validate[required,custom[item_size]]" type="text" name="length" size="4" maxlength="7" value="<?php echo $prototype['prototype_length'];?>" placeholder="Length">
							<div class="input-group-text">x</div>
							<input class="flex--1 validate[required,custom[item_size]]" type="text" name="width" size="4" maxlength="7" value="<?php echo $prototype['prototype_width'];?>" placeholder="Width">
							<div class="input-group-text">x</div>
							<input class="flex--1 validate[required,custom[item_size]]" type="text" name="height" size="4" maxlength="7" value="<?php echo $prototype['prototype_height'];?>" placeholder="Height">
						</div>
					</div>
				</div>
			</div>

			<label class="input-label input-label--required">Description</label>
			<textarea class="validate[required]" name="description" id="html-description"><?php echo $prototype['description']?></textarea>

			<fieldset class="pb-10" id="options-field">
				<legend>Changes</legend>
				<div class="container-fluid-modal">
					<div class="row">
						<div class="col-6">
							<label class="input-label">Option name</label>
						</div>
						<div class="col-6">
							<label class="input-label">Option value</label>
						</div>

						<div class="col-12 prototype_user-options">
							<?php if(!empty($prototype['changes'])){?>
								<?php foreach($prototype['changes'] as $key => $change){?>
									<div class="row mt-10">
										<div class="col-6">
											<input class="form-control validate[maxSize[100]]" type="text" name="e_attr[<?php echo $key;?>][name]" value="<?php echo $change['name'];?>">
										</div>
										<div class="col-6">
											<div class="input-group w-100pr">
												<input class="form-control validate[maxSize[100]]" type="text" name="e_attr[<?php echo $key;?>][current_value]" value="<?php echo $change['current_value'];?>">
												<span class="input-group-btn">
													<button class="btn btn-default confirm-dialog"  data-message="Are you sure you want to delete this option?" data-callback="delete_option" type="button"><i class="ep-icon ep-icon_remove-stroke"></i></button>
												</span>
											</div>
										</div>
									</div>
								<?php } ?>
							<?php } else{?>
								<div class="row mt-10">
									<div class="col-6">
										<input class="form-control validate[maxSize[100]]" type="text" name="u_attr[name][]" >
									</div>
									<div class="col-6">
										<input class="form-control validate[maxSize[100]]" type="text" name="u_attr[value][]">
									</div>
								</div>
							<?php }?>
						</div>

						<div class="col-12 mt-10">
							<button class="btn btn-default call-function" data-callback="add_user_option"><i class="ep-icon ep-icon_plus fs-10"></i> Add option</button>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
		</div>
	</form>
</div>

<?php tmvc::instance()->controller->view->display('new/file_upload_scripts'); ?>

<script>
	$(document).ready(function(){
		tinymce.remove('textarea#html-description');
		tinymce.init({
			selector:'textarea#html-description',
			menubar: false,
			statusbar : false,
			height : 300,
			plugins: ["autolink lists link"],
            style_formats: [
                {title: 'H3', block: 'h3'},
                {title: 'H4', block: 'h4'},
                {title: 'H5', block: 'h5'},
                {title: 'H6', block: 'h6'},
            ],
			toolbar: "styleselect | bold italic underline | link | numlist bullist ",
			resize: false
		});

		var filesAmount = parseInt('<?php echo $fileupload['limits']['amount']; ?>', 10);
        var filesAllowed = parseInt('<?php echo $fileupload['limits']['amount'] - (!empty($prototype['image']) ? 1 : 0); ?>', 10);
		var urlPhoto = "<?php echo $fileupload['url']['upload']; ?>";
		var urlDeletePhoto = "<?php echo $fileupload['url']['delete']; ?>";
		$('#edit_fileupload').fileupload({
			url: urlPhoto,
			dataType: 'json',
			maxNumberOfFiles: filesAmount,
			maxFileSize: parseInt('<?php echo $fileupload['limits']['filesize']; ?>', 10),
			beforeSend: function (event, files, index, xhr, handler, callback) {
				var uploadButton = $('.fileinput-loader-btn');
				if(files.files && files.files.length > filesAllowed){
					if(filesAllowed > 0) {
						systemMessages(translate_js({ plug: 'fileUploader', text: 'error_exceeded_limit_text'}).replace('[AMOUNT]', filesAmount), 'warning');
					} else {
						systemMessages(translate_js({ plug: 'fileUploader', text: 'error_no_more_files'}), 'warning');
					}
					uploadButton.fadeOut();
					event.abort();

					return;
				}

				uploadButton.fadeIn();
			},
			done: function (e, data) {
				if(data.result.mess_type == 'success'){
					filesAllowed--;

					var image_params = {
						type: 'imgnolink',
						index: data.result.id,
						image_link: "<?php echo __SITE_URL?>" + data.result.path,
						image: '<img class="image" src="<?php echo __IMG_URL?>' + data.result.path + '">'
					};

					$('.fileupload').append(templateFileUploadNew(image_params));
					$('.fileupload-item .fileupload-item__actions').append(
						'<a class="btn btn-dark confirm-dialog" \
							data-action="' + urlDeletePhoto + "/" + data.result.id + '" \
							data-file="" \
							data-callback="fileploadRemovePrototype" \
							data-message="Are you sure you want to delete this image?" \
							href="#" title="Delete">Delete</a>'
					);
				} else{
					systemMessages(data.result.message, data.result.mess_type);
				}

				$('.fileinput-loader-btn').fadeOut();
			},
			processalways: function(e,data) {
				if (data.files.error){
					systemMessages( data.files[0].error, 'error' );
				}
			}
		}).prop('disabled', !$.support.fileInput)
		.parent().addClass($.support.fileInput ? undefined : 'disabled');

		window.fileploadRemovePrototype = function(button) {
			try {
				fileuploadRemove(button).then(function(response) {
					if ('success' === response.mess_type) {
						filesAllowed++;
					}
				});
			} catch (error) {
				if(__debug_mode) {
					console.error(error);
				}
			}
		}
	});

	var add_user_option = function(btn){
		if($('.prototype_user-options > .row').length >= 20){
			systemMessages( 'You can not add more options.', 'warning' );
			return false;
		}

		var template = '<div class="row mt-10">\
							<div class="col-6">\
								<input class="validate[required,maxSize[100]]" type="text" name="u_attr[name][]"/>\
							</div>\
							<div class="col-6">\
								<div class="input-group">\
									<input class="form-control validate[required,maxSize[100]]" type="text" name="u_attr[value][]"/>\
									<span class="input-group-append">\
										<button class="btn btn-default call-function" data-callback="delete_option" type="button"><i class="ep-icon ep-icon_remove-stroke"></i></button>\
									</span>\
								</div>\
							</div>\
						</div>';
		$('.prototype_user-options').append(template);
	}

	var delete_option = function(obj){
		var $this = $(obj);
		$this.closest('.row').remove();
		if(!$('.prototype_user-options > .row').length){
			var template = '<div class="row mt-10">\
								<div class="col-6">\
									<input class="validate[maxSize[100]]" type="text" name="u_attr[name][]"/>\
								</div>\
								<div class="col-6">\
									<input class="validate[maxSize[100]]" type="text" name="u_attr[value][]"/>\
								</div>\
							</div>';
			$('.prototype_user-options').append(template);
		}
	}

	function prototypeEditFormCallBack(form){
		var $form = $(form);
		var $wrform = $form.closest('.js-modal-flex');
		var fdata = $form.serialize();

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL;?>prototype/ajax_prototype_operation/edit_prototype',
			data: fdata,
			dataType: 'JSON',
			beforeSend: function(){
				showLoader($wrform);
				$form.find('button[type=submit]').addClass('disabled');
			},
			success: function(resp){
				hideLoader($wrform);
				systemMessages( resp.message, resp.mess_type );

				if(resp.mess_type == 'success'){
					callFunction('manage_prototype_callback', resp);
					closeFancyBox();
				}else{
					$form.find('button[type=submit]').removeClass('disabled');
				}
			}
		});
	}
</script>
