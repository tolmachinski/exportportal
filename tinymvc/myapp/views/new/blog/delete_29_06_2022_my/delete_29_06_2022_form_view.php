<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" id="blog-post--form">
        <input type="hidden" name="post" value="<?php echo !empty($blog_info['id']) ? $blog_info['id'] : null; ?>"/>
        <input type="hidden" name="upload_folder" value="<?php echo $upload_folder;?>"/>

		<div class="modal-flex__content pr-15">
            <label class="input-label input-label--required"><?php echo translate("blog_dashboard_modal_field_country_label_text"); ?></label>
            <?php $id_country = empty($blog_info) ? 0 : $blog_info['id_country']; ?>
            <select class="validate[required,integer]" name="country">
                <option value="" disabled><?php echo translate("blog_dashboard_modal_field_country_placeholder"); ?></option>
                <option value="0" <?php echo selected($country['id_country'], 0); ?>>
                    <?php echo translate("blog_dashboard_modal_country_option_any_text"); ?>
                </option>

                <?php echo getCountrySelectOptions($blog_countries, $id_country, array('include_default_option' => false));?>
            </select>

            <label class="input-label input-label--required"><?php echo translate("blog_dashboard_modal_field_language_label_text"); ?></label>
            <?php if(empty($blog_info['lang'])) { ?>
                <select class="validate[required]" name="blog_lang" id="blog-post-language">
                    <option value=""><?php echo translate("blog_dashboard_modal_field_language_placeholder"); ?></option>
                    <?php foreach($tlanguages as $tlanguage) { ?>
                        <option value="<?php echo $tlanguage['lang_iso2'];?>" <?php echo selected($tlanguage['lang_iso2'], __SITE_LANG); ?>>
                            <?php echo $tlanguage['lang_name'];?>
                        </option>
                    <?php } ?>
                </select>
            <?php } else { ?>
                <div class="form-content">
                    <?php echo $tlanguage['lang_name']; ?>
                </div>
            <?php } ?>

            <div class="relative-b">
                <label class="input-label input-label--required"><?php echo translate("blog_dashboard_modal_field_category_label_text"); ?></label>
                <select class="validate[required]" name="category" id="blog-post-category">
                    <option value=""><?php echo translate("blog_dashboard_modal_field_category_placeholder"); ?></option>
                    <?php foreach($blog_categories as $category){?>
                        <option value="<?php echo $category['id_category'];?>" <?php echo selected($category['id_category'], $blog_info['id_category']); ?>>
                            <?php echo $category['name'];?>
                        </option>
                    <?php }?>
                </select>
            </div>

            <div class="relative-b">
                <label class="input-label input-label--required"><?php echo translate("blog_dashboard_modal_field_title_label_text"); ?></label>
                <input type="text" name="title" class="validate[required,maxSize[250]]" value="<?php echo $blog_info['title']?>" placeholder="<?php echo translate("blog_dashboard_modal_field_title_placeholder"); ?>"/>
            </div>

            <div class="relative-b">
                <label class="input-label input-label--required"><?php echo translate("blog_dashboard_modal_field_description_label_text"); ?></label>
                <textarea class="validate[required,maxSize[500]]" id="blog-post--formfield--description" name="short_description" data-max="500" placeholder="<?php echo translate("blog_dashboard_modal_field_description_placeholder"); ?>"><?php echo $blog_info['short_description']?></textarea>
            </div>

            <label class="input-label input-label--required"><?php echo translate("blog_dashboard_modal_field_content_label_text"); ?></label>
            <textarea class="validate[required,maxSize[20000]]" id="blog-post--formfield--content" name="content" data-max="20000" placeholder="<?php echo translate("blog_dashboard_modal_field_content_placeholder"); ?>"><?php echo $blog_info['content']?></textarea>

            <div class="relative-b">
                <label class="input-label input-label--required"><?php echo translate("blog_dashboard_modal_field_tags_label_text"); ?></label>
                <?php views()->display('new/tags_rule_view');?>
                <input class="w-100pr" name="tags" id="blog-post--formfield--tags" value="<?php echo implode(';', $blog_tags);?>">
            </div>

            <!-- <select class="form-control validate[required]" name="tags[]" id="blog-post--formfield--tags" multiple placeholder="<?php //echo translate("blog_dashboard_modal_field_tags_placeholder"); ?>">
                <?php //foreach($blog_tags as $tag){ ?>
                    <option selected="selected"><?php //echo $tag; ?></option>
                <?php //} ?>
            </select> -->

            <label class="input-label"><?php echo translate("blog_dashboard_modal_field_photo_label_text"); ?></label>
            <div class="juploader-b">
                <span class="btn btn-dark mnw-125 fileinput-button">
                    <span><?php echo translate("blog_dashboard_modal_field_photo_upload_button_text"); ?></span>
                    <input id="blog-post---formfield--files" type="file" name="files[]" accept="<?php echo arrayGet(getMimePropertiesFromFormats(config('blogs_photos_main_accept')), 'accept');?>">
                </span>
                <span class="fileinput-loader-btn fileinput-loader-img" style="display:none;">
                    <img class="image" src="<?php echo __IMG_URL;?>public/img/loader.svg" alt="loader"> <?php echo translate("blog_dashboard_modal_field_photo_upload_placeholder"); ?>
                </span>
                <div class="info-alert-b mt-15">
                    <i class="ep-icon ep-icon_info-stroke"></i>
                    <div> &bull; <?php echo translate("blog_dashboard_modal_field_photo_help_text_line_1", array('{{FILE_SIZE}}' => config('fileupload_max_file_size_placeholder')));?></div>
                    <div> &bull; <?php echo translate("blog_dashboard_modal_field_photo_help_text_line_2", array('[WIDTH]' => config('blogs_photos_main_min_width'), '[HEIGHT]' => config('blogs_photos_main_min_height'))); ?></div>
                    <div> &bull; <?php echo translate("blog_dashboard_modal_field_photo_help_text_line_3"); ?></div>
                    <div> &bull; <?php echo translate("blog_dashboard_modal_field_photo_help_text_line_4", array('[ACCEPT]' => config('blogs_photos_main_accept'))); ?></div>
                </div>

                <div class="fileupload mt-15 clearfix">
                    <?php if(!empty($blog_info['photo'])) { ?>
                        <div class="fileupload-item">
                            <div class="fileupload-item__image">
                                <a class="link fancyboxGallery" rel="fancybox-thumb" href="<?php echo $blog_info['photo_url']; ?>">
                                    <img class="image" src="<?php echo $blog_info['photo_url']; ?>" />
                                </a>
                            </div>
                            <!-- <div class="fileupload-item__actions">
                                <a class="btn btn-dark confirm-dialog"
                                    data-file="<?php echo $blog_info['id']; ?>"
                                    data-action="<?php echo __SITE_URL . "blogs/ajax_blog_delete_db_photo"; ?>"
                                    data-callback="fileuploadRemove"
                                    data-additional-callback="updateImageStats"
                                    data-message="<?php echo translate("blog_dashboard_modal_field_photo_delete_button_message"); ?>"
                                    title="<?php echo translate("blog_dashboard_modal_field_photo_delete_button_title"); ?>">
                                    <?php echo translate("blog_dashboard_modal_field_photo_delete_button_text"); ?>
                                </a>
                            </div> -->
                        </div>
                    <?php } ?>
                </div>
            </div>

            <label class="vam lh-35">
                <input class="checkbox-visible" type="checkbox" name="visible" <?php echo !empty($blog_info) && (bool) $blog_info['visible'] ? "checked": ''; ?> value="1">
                <?php echo translate("blog_dashboard_modal_field_publish_label_text"); ?>
            </label>
		</div>

		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit"><?php echo translate("blog_dashboard_modal_form_submit_button_title"); ?></button>
            </div>
		</div>
	</form>
</div>

<?php if(empty($blog_info['lang'])) { ?>
<script type="application/javascript">
    $(function() {
        var languages = $('#blog-post-language');
        var categories = $('#blog-post-category');
        var onChange = function(event) {
            var self = $(this);
            var lang = self.val() || null;
            var url = __site_url + 'blogs/ajax_blogs_operation/get_blog_categories';
            var onRequestSuccess = function (response) {
                if(response.mess_type !== 'success') {
                    systemMessages(response.message, response.mess_type);

                    return;
                }

                categories.children().not(':first').remove();
                if(response.categories.length > 0) {
                    var options = [];
                    response.categories.forEach(function(category) {
                        options.push($('<option>').val(category.id_category).html(category.name));
                    });

                    categories.append(options);
                    categories.prop('disabled', false);
                } else {
                    categories.append($('<option>').val("").prop('selected', true).text("Categories are not found"));
                }
            };

            if(null !== lang) {
                categories.prop('disabled', true);
                $.post(url, { blog_lang: lang }, null, 'json').done(onRequestSuccess).fail(onRequestError);
            } else {
                categories.children().not(':first').prop('selected', false);
                categories.children().first().prop('selected', true);
            }
        }

        languages.on('change', onChange);
    });
</script>
<?php } ?>

<script type="application/javascript">


    $(function() {
        $.fn.setValHookType = function (type) {
            this.each(function () {
                this.type = type;
            });

            return this;
        };

        var sendRequest = function (url, data) {
            return $.post(url, data, null, 'json');
        };
        var sendContent = function (formElement){
            var form = $(formElement);
            var wrapper = form.closest('.js-modal-flex');
            var submitButton  =form.find('button[type=submit]');
            var formData = form.serializeArray();
            var isEditMode = Boolean(~~'<?php echo (int) !empty($blog_info); ?>');
            var url = __site_url + (isEditMode ? 'blogs/ajax_blogs_operation/edit_blog' : 'blogs/ajax_blogs_operation/add_blog');
            var beforeSend = function() {
                showLoader(wrapper);
                submitButton.addClass('disabled');
            };
            var onRequestEnd = function() {
                hideLoader(wrapper);
                submitButton.removeClass('disabled');
            };
            var onRequestSuccess = function(data){
                hideLoader(wrapper);
                systemMessages(data.message, data.mess_type);
                if(data.mess_type === 'success'){
                    closeFancyBox();
                    callFunction(isEditMode ? 'onEditBlog' : 'onAddBlog', data);
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };
        var saveContent = function () {
            tinymce.EditorManager.triggerSave();
        };
        var addImage = function(index, file) {
            uploadFileLimit--;

            var queue = $('.fileupload');
            var url  =__img_url + '/' + file.path;
            var itemId = new Date().getTime() + '-' + index;
            var image_params = {
                type: 'imgnolink',
                index: itemId,
                image_link: url,
                image: $('<img>').attr({ src: url}).prop('outerHTML')
            };
            var imagePreview = $(templateFileUploadNew(image_params));
            var imageInput = $('<input>').attr({ name: 'images[]', type: 'hidden', value: file.path});
            var closeButton = $('<a>').text("Delete").attr({
                title: 'Delete',
                class: 'btn btn-dark confirm-dialog',
                'data-file': file.name,
                // 'data-action': imageRemoveUrl,
                'data-message': 'Are you sure you want to delete this image?',
                'data-callback': 'fileuploadRemove',
                'data-additional-callback': 'updateImageStats',
            });

            imagePreview.find('.fileupload-item__actions').append([imageInput, closeButton]);
            queue.append(imagePreview);
        };
        var inlineFileUpload = function (callback, value, meta) {
            if (meta.filetype !== 'image') {
                return;
            }

            var body = $(body);
            var url = this.settings.file_picker_upload_url || location.origin;
            var input = body.find('input#blog-inline-image-upload');
            var onLoad = function (file) {
                return function(){
                    var id = 'blobid' + (new Date()).getTime();
                    var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                    var base64 = this.result.split(',')[1];
                    var blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);

                    var formData = new FormData();
                    formData.append("userfile", blobInfo.blob());
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: "JSON",
                        data: formData,
                        beforeSend: function(){
                            showLoader('.mce-floatpanel.mce-in', 'Uploading...');
                        },
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            hideLoader('.mce-floatpanel.mce-in');
                            if(response.mess_type == 'success'){
                                var url = response.message.charAt(0) === '/' ? response.message.substring(1) : response.message;

                                callback(__img_url + url, { title: response.message.split('/').slice(-1) });
                            } else {
                                systemMessages(response.message, response.mess_type);
                            }
                        }
                    });
                };
            }

            if(input.length === 0) {
                input = $('<input>');
                input.css({display: 'none'});
                input.attr('id', 'blog-inline-image-upload');
                input.attr('type', 'file');
                input.attr('accept', '<?php echo arrayGet(getMimePropertiesFromFormats(config('blogs_photos_in_text_accept')), 'accept');?>');
                body.append(input);
            }

            input.on('change', function() {
                var file = this.files[0];
                var reader = new FileReader();
                reader.onload = onLoad(file);
                reader.readAsDataURL(file);
            });
            input.click();
        };
        var uploadFinished = function (e, data) {
            if(data.result.mess_type == 'success'){
                $.each(data.result.files || [], addImage);
            } else {
                systemMessages(data.result.message, data.result.mess_type);
            }

			$('.fileinput-loader-btn').fadeOut();
        };
        var beforeUpload = function (event, files, index, xhr, handler, callBack) {
            if(files.files.length > uploadFileLimit){
                if(uploadFileLimit > 0) {
                    systemMessages(translate_js({ plug: 'fileUploader', text: 'error_exceeded_limit_text'}).replace('[AMOUNT]', uploadFileLimit), 'warning');
                } else {
                    systemMessages(translate_js({ plug: 'fileUploader', text: 'error_no_more_files'}), 'warning');
                }

                event.abort();
            } else {
                $('.fileinput-loader-btn').fadeIn();
            }
        };
        var processUploadErrors = function(e,data){
            if (data.files.error){
                systemMessages(data.files[0].error, 'error');
            }
        };
        var initializeEditor = function(editor) {
            initNewTinymce(editor, {valHook: 'editor'});

            // var container = $(editor.editorContainer);
            // var containerId = container.attr('id');
            // var showPrompt = function (e) {
            //     var selector = "." + containerId + 'formError';
            //     var errorBox = container.siblings(selector);
            //     if(errorBox.length) {
            //         errorBox.show();
            //         errorBox.css('opacity', 1);
            //     }
            // };
            // var hidePrompt =  function (e) {
            //     var selector = "." + containerId + 'formError';
            //     var errorBox = container.siblings(selector);
            //     if(errorBox.length) {
            //         errorBox.hide();
            //         errorBox.css('opacity', 0);
            //     }
            // };
            // var reValidate = function () {
            //     container.validationEngine('validate');
            // };

            // container.addClass('validate[required,maxSize[20000]]').setValHookType('editor').on('blur', hidePrompt);
            // editor.on('blur', hidePrompt);
            // editor.on('dirty', reValidate);
            // editor.on('click change', function() {
            //     if(this.getContent() === '' && container.siblings("." + containerId + 'formError').length) {
            //         reValidate();
            //         showPrompt();
            //     }
            // });
        };
        var updateImageStats = function() {
            uploadFileLimit++;
            callFunction('onDeleteImage');
        };

        var form = $('#blog-post--form');
        var visibilityFlag = $('.checkbox-visible');
        var postDescription = $('#blog-post--formfield--description');
        var postContent = $('#blog-post--formfield--content');
        var fileUploader = $('#blog-post---formfield--files');
        var uploadFileLimit = 1 - parseInt('<?php echo (int) !empty($blog_info['photo']); ?>', 10);
        var imageUploadMaxSize = "<?php echo config('fileupload_max_file_size');?>";
        var imageUploadTimestamp = "<?php echo $upload_folder;?>";
        var imageUploadUrl = __site_url + 'blogs/ajax_blog_upload_photo/' + imageUploadTimestamp;
        // var imageRemoveUrl = __site_url + 'blogs/ajax_blog_delete_files/' + imageUploadTimestamp;
        var imagePickerUploadUrl = __site_url + 'blogs/upload_photo/' + imageUploadTimestamp;

        var uploadOptions = {
            url: imageUploadUrl,
            dataType: 'json',
            done: uploadFinished,
            maxFileSize: imageUploadMaxSize,
            acceptFileTypes: /(\.|\/)(jpe?g|png)$/i,
            loadImageFileTypes: /^image\/(p?jpeg|png|x-windows-bmp)$/,
            maxNumberOfFiles: 1,
            processalways:processUploadErrors,
            beforeSend: beforeUpload,
        };

        // var tagsOptions = {
        //     width: '100%',
        //     tags: true,
        //     multiple: true,
        //     placeholder: tagList.attr('placeholder') || null,
        //     tokenSeparators: [',']
        // };
        var checkboxOptions = {
			checkboxClass: 'icheckbox icheckbox--20 icheckbox--blue mb-3 mr-10',
			increaseArea: '20%'
        };
        var counterOptions = {
			countDown: true
        };
        var editorOptions = {
            schema: 'html5',
            target: postContent.get(0),
            theme: 'modern',
			timestamp: imageUploadTimestamp,
			language: __site_lang,
			menubar: false,
			statusbar: true,
			height: 500,
			min_height: 250,
            max_height: 1000,
            media_poster: false,
			media_alt_source: false,
            relative_urls: false,
            convert_urls: false,
            remove_script_host: false,
            resize: false,
            dialog_type : "modal",
			plugins: [ "media autolink lists link image contextmenu charactercount paste fullscreen" ],
            toolbar: "undo redo | styleselect | bold italic underline link | numlist bullist | media | image | fullscreen",
            contextmenu: "undo redo | bold italic underline | link media image",
            file_picker_types: 'image',
            file_picker_upload_url: imagePickerUploadUrl,
            file_picker_callback: inlineFileUpload,
            paste_filter_drop: true,
            paste_enable_default_filters: true,
            paste_word_valid_elements: 'img,h3,h4,h5,h6,p,span,strong,em,b,i,u,a,ol,ul,li,br',
            paste_webkit_styles: 'none',
            paste_webkit_styles: 'text-decoration',
            paste_data_images: false,
            paste_retain_style_properties: 'text-decoration',
            // init_instance_callback: initializeEditor,
            style_formats: [
                { title: 'H3', block: 'h3' },
                { title: 'H4', block: 'h4' },
                { title: 'H5', block: 'h5' },
                { title: 'H6', block: 'h6' },
            ],
        };

        form.on('jqv.form.validating', saveContent);

        var $requestTagsSelect = $("#blog-post--formfield--tags");

        var $requestTags = $requestTagsSelect.tagsInput({
            'defaultText':'<?php echo translate("blog_dashboard_modal_field_tags_placeholder"); ?>',
            'width':'100%',
            'height':'auto',
            'minChars' : 3,
            'maxChars' : 30,
            'delimiter': [';']
        });

        $requestTags.next('.tagsinput').addClass('validate[required]')
            .setValHookType('tagsinput');

        $.valHooks.tagsinput = {
            get: function (el) {
                return $requestTagsSelect.val() || [];
            },
            set: function (el, val) {
                $requestTagsSelect.val(val);
            }
        };

        // var $requestTagsSelect = $("#blog-post--formfield--tags");
        // var $requestTags = $requestTagsSelect.tagsinput({
        //     trimValue: true,
        //     maxChars: 30,
        //     minChars: 3,
        //     cancelConfirmKeysOnEmpty: false
        // });

        // $requestTags[0].$container.addClass('validate[required]')
        //     .setValHookType('tagsinput');

        // $.valHooks.tagsinput = {
        //     get: function (el) {
        //         return $requestTagsSelect.val() || [];
        //     },
        //     set: function (el, val) {
        //         $requestTagsSelect.val(val);
        //     }
        // };

        // var tagList = $('#blog-post--formfield--tags');
        // tagList.select2(tagsOptions)
        // .data('select2')
        // .$container.attr('id', 'blog-post--formfield--tags-container')
        // .addClass('validate[required]')
        // .setValHookType('select2');

        // $.valHooks.select2 = {
        //     get: function (el) {
        //         return tagList.val() || [];
        //     },
        //     set: function (el, val) {
        //         tagList.val(val);
        //     }
        // };


        visibilityFlag.icheck(checkboxOptions);
        postDescription.textcounter(counterOptions);
        fileUploader.fileupload(uploadOptions).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');
        tinymce.remove('#' + postContent.attr('id'));
        tinymce.init(editorOptions);

        // $.valHooks.editor = {
        //     get: function (el) {
        //         return tinymce.get(postContent.attr('id')).getContent({format : 'text'}) || "";
        //     }
        // };

        window.updateImageStats = updateImageStats;
        window.modalFormCallBack = sendContent;
    });
</script>
