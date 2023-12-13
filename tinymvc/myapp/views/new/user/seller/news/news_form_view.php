<div class="js-wr-modal wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        data-callback="sellerNewsFormCallBack"
    >
		<div class="modal-flex__content">
            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_news_title_label_text'); ?></label>
                <input
                    class="validate[required, maxSize[200]]"
                    maxlength="200"
                    type="text"
                    name="title"
                    placeholder="<?php echo translate('seller_news_placeholder_title_text', null, true);?>"
                    value="<?php echo (isset($news['title_news']) ? $news['title_news'] : '');?>"
                    <?php echo addQaUniqueIdentifier('popup__seller-news__edit-comment-form_title-input'); ?>
                />
            </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_news_content_label_text'); ?></label>
                <textarea
                    id="js-edit-news-text-block"
                    class="validate[required]"
                    data-max="20000"
                    name="text"
                    placeholder="<?php echo translate('seller_news_content_placeholder_text', null, true);?>"
                    <?php echo addQaUniqueIdentifier('seller-news-my__form_content-textarea_popup'); ?>
                ><?php echo (isset($news['text_news']) ? $news['text_news'] : '');?></textarea>
            </div>

            <div class="form-group">
                <label class="input-label"><?php echo translate('seller_news_image_label_text'); ?></label>

                <span class="btn btn-dark mnw-125 fileinput-button">
                    <span><?php echo translate('seller_news_select_files_text'); ?></span>
                    <!-- The file input field used as target for the file upload widget -->
                    <input
                        id="js-add-edit-news-uploader"
                        type="file"
                        name="files"
                        accept="<?php echo $fileupload['limits']['accept']; ?>"
                        <?php echo addQaUniqueIdentifier('popup__seller-news__edit-comment-form_select-files-btn'); ?>
                    >
                </span>
                <span class="fileinput-loader-btn" style="display:none;"><img class="image" src="<?php echo __IMG_URL;?>public/img/loader.svg" alt="loader"> <?php echo translate('seller_news_uploading_text'); ?></span>
                <div class="info-alert-b mt-10">
                    <i class="ep-icon ep-icon_info-stroke"></i>
                    <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_1', array('[[SIZE]]' => $fileupload['limits']['filesize_readable'])); ?></div>
                    <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_2', array('[[WIDTH]]' => $fileupload['limits']['width'], '[[HEIGHT]]' => $fileupload['limits']['height'])); ?></div>
                    <div><?php echo translate('general_dashboard_modal_field_image_help_text_line_3', array('[[AMOUNT]]' => $fileupload['limits']['amount'])); ?></div>
                    <div>• <?php echo translate('general_dashboard_modal_field_image_help_text_line_4', array('[[FORMATS]]' => str_replace('|', ',', $fileupload['limits']['formats']))); ?></div>
                </div>

                <!-- The container for the uploaded files -->
                <div class="fileupload files mt-10" id="js-add-edit-news-image-wrapper">
                    <?php if(!empty($news['image_news'])) { ?>
                        <div class="fileupload-item item-middle">
                            <div class="fileupload-item__image">
                                <a class="link fancyboxGallery" rel="fancybox-thumb" href="<?php echo $imageLink?>">
                                    <img class="image" src="<?php echo $imageLink?>" <?php echo addQaUniqueIdentifier('popup__seller-news__edit-comment-form_image'); ?>/>
                                </a>
                            </div>
                            <div class="fileupload-item__actions">
                                <a class="btn btn-dark confirm-dialog"
                                    data-callback="fileploadRemoveNewsImageShallow"
                                    data-message="<?php echo translate('seller_news_delete_image_question', null, true);?>"
                                    title="<?php echo translate('seller_news_delete_word', null, true);?>"
                                    <?php echo addQaUniqueIdentifier('seller-news-my__form_delete-files-btn_popup'); ?>
                                >
                                    <?php echo translate('seller_news_delete_word'); ?>
                                </a>
                                <input type="hidden" name="old_image" value="1">
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="form-group mt-10">
                <ul class="list-form-checked-info">
                    <li class="list-form-checked-info__item">
                        <label class="custom-checkbox">
                            <input
                                name="post_wall"
                                type="checkbox"
                                <?php echo addQaUniqueIdentifier('seller-news-my__form_post-on-wall-checkbox_popup'); ?>
                            >
                            <span class="custom-checkbox__text"><?php echo translate('general_modal_field_post_on_wall_label_text'); ?></span>
                        </label>
                    </li>
                </ul>
            </div>

            <?php if(!empty($news)){?>
                <input type="hidden" name="id_news" value="<?php echo $news['id_news'];?>"/>
            <?php }?>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button
                    class="btn btn-primary"
                    type="submit"
                    <?php echo addQaUniqueIdentifier('seller-news-my__form_save-btn_popup'); ?>
                >
                    <?php echo translate('general_save_word'); ?>
                </button>
            </div>
		</div>
	</form>
</div>

<script>
	$(function() {
        var onSaveContent = function(url, isEdit, formElement) {
            var form = $(formElement);
            var wrapper = form.closest('.js-wr-modal');
            var formData = form.serializeArray();
            var submitButton = form.find('button[type=submit]');
            var sendRequest = function (url, data) {
                return $.post(url, data, null, 'json');
            };
            var onRequestStart = function() {
                showLoader(wrapper);
                submitButton.addClass('disabled');
            };
            var onRequestEnd = function() {
                hideLoader(wrapper);
                submitButton.removeClass('disabled');
            };
            var onRequestSuccess = function(data){
                systemMessages(data.message, data.mess_type);
                if(data.mess_type === 'success'){
                    if (isEdit) {
						callFunction('callbackEditSellerNews', data);
					} else {
						callFunction('callbackAddSellerNews', data);
					}
                    closeFancyBox();
                }
            };

            onRequestStart();
            sendRequest(url, formData)
				.done(onRequestSuccess)
				.fail(onRequestError)
				.always(onRequestEnd);
        };
        var onUploadStart = function (event, files, index, xhr, handler, callBack) {
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
        };
        var onUploadFinished = function(e, data){
            if (data.files.error){
                systemMessages(data.files[0].error, 'error');
            }
        };
        var onUploadDone = function (e, data) {
            if(data.result.mess_type == 'success'){
                addImage(data.result.files, 0);
            } else {
                systemMessages(data.result.message, data.result.mess_type);
            }

			uploadButton.fadeOut();
        };
		var onFileRemove = function(button) {
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
		};
		var onFileRemoveShallow = function(button) {
            button.closest('.fileupload-item').remove();
            $.fancybox.reposition();
            filesAllowed++;
		};
        var addImage = function(file, index) {
            filesAllowed--;

            var pictureId = index + '-' + new Date().getTime();
            var url = __img_url + '/' + file.fullPath;
            var imageInput = $('<input>').attr({
                name: 'image',
                type: 'hidden',
                value: file.path
            });
			var image = $('<img>').attr({ src: file.fullPath });
            var imageContent = $(templateFileUploadNew({
                type: 'imgnolink',
                index: pictureId,
                image: image.prop('outerHTML'),
                className: 'fileupload-image',
            }));
            var closeButton = $('<a>').text(translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_text'})).attr({
                title: translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_title'}),
                class: 'btn btn-dark confirm-dialog',
                'data-file': file.name,
                'data-action': fileRemoveUrl,
                'data-message': translate_js({ plug: 'general_i18n', text: 'form_button_delete_file_message'}),
                'data-callback': 'fileploadRemoveNewsImage',
            });

            imageContent.find('.fileupload-item__actions').append([imageInput, closeButton]);
            imageWrapper.append(imageContent);
        };

		var isEdit = Boolean(~~'<?php echo (int) !empty($news) ?>');
		var url = '<?php echo $action; ?>';
		var news = '<?php echo $news['id_news']; ?>' || null;
        var filesAmount = parseInt('<?php echo $fileupload['limits']['amount']; ?>', 10);
        var filesAllowed = parseInt('<?php echo $fileupload['limits']['amount'] - (!empty($news['image_news']) ? 1 : 0); ?>', 10);
        var fileTypes = new RegExp('(<?php echo $fileupload['limits']['mimetypes']; ?>)', 'i');
        var fileFormats = new RegExp('(.|\/)(<?php echo $fileupload['limits']['formats']; ?>)', 'i');
        var fileUploadMaxSize = "<?php echo $fileupload['limits']['filesize']; ?>";
        var fileUploadTimestamp = "<?php echo $fileupload['directory']; ?>";
        var fileUploadUrl = "<?php echo $fileupload['url']['upload']; ?>";
        var fileRemoveUrl = "<?php echo $fileupload['url']['delete']; ?>";
        var uploader = $('#js-add-edit-news-uploader');
        var uploadButton = $('.fileinput-loader-btn');
        var imageWrapper = $('#js-add-edit-news-image-wrapper');
		var contentSelector = '#js-edit-news-text-block';
        var uploaderOptions = {
            url: fileUploadUrl,
            dataType: 'json',
			formData: { news: news },
            maxNumberOfFiles: filesAmount,
            maxFileSize: fileUploadMaxSize,
            acceptFileTypes: fileFormats,
            loadImageFileTypes: fileTypes,
            processalways: onUploadFinished,
            beforeSend: onUploadStart,
            done: onUploadDone,
        };
		var editorOptions = {
			selector: contentSelector,
			statusbar : true,
			menubar: false,
			height : 200,
			dialog_type : "modal",
			plugins: ["lists charactercount"],
			toolbar: "bold italic underline | numlist bullist "
		};

        uploader.fileupload(uploaderOptions);
        uploader.prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');
		tinymce.remove(contentSelector);
		tinymce.init(editorOptions);

        window.fileploadRemoveNewsImage = onFileRemove;
        window.fileploadRemoveNewsImageShallow = onFileRemoveShallow;
        window.sellerNewsFormCallBack = onSaveContent.bind(null, url, isEdit);
    });
</script>
