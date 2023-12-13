<form id="article-form"
    name="article-form"
    action="<?php echo $url['save']; ?>"
    data-process="<?php echo $url['process_draft']; ?>"
    class="content-form content-form--modal validate-modal mt-35 mb-35 m-auto">

    <input id="article-form--input--folder-name"
        type="hidden"
        name="upload_folder"
        value="<?php echo $upload_folder;?>"/>

    <input id="article-form--input--token"
        type="hidden"
        name="token"
        value="<?php echo $token; ?>">

    <input id="article-form--input--email"
        type="hidden"
        name="email"
        value="<?php echo $email; ?>">

    <input id="rticle-form--input--lang"
        type="hidden"
        name="lang"
        value="en">

    <!-- <div class="w-100pr">
        <label for="article-form--input--lang">
            <i class="text-red">*</i> Blog language:
        </label>
        <div class="mt-5" style="position: relative">
            <select id="article-form--input--lang"
                name="lang"
                data-validation-engine="validate[required]"
                data-prompt-position="topLeft:0,-10">
                <option></option>
                <?php // foreach($tlanguages as $tlanguage){ ?>
                    <option value="<?php echo $tlanguage['lang_iso2']; ?>">
                        <?php // echo $tlanguage['lang_name']; ?>
                    </option>
                <?php // } ?>
                <option value="0">Other</option>
            </select>
        </div>
    </div> -->

    <?php if(!empty($required_theme)) { ?>
        <div class="w-100pr mt-20">
            <h4><strong class="fs-18">Please write a small article on the subject: "<?php echo $required_theme; ?>". The article should be written in English.</strong></h4>
        </div>
    <?php } ?>

    <!-- <div class="w-100pr">
        <label for="article-form--input--country">
            <i class="text-red">*</i> Country:
        </label>
        <div class="mt-5" style="position: relative">
            <select id="article-form--input--country"
                name="country"
                data-validation-engine="validate[required]"
                data-prompt-position="topLeft:0,-10">
                <option></option>
                <option value="0" selected="selected">Any</option>
                <?php // foreach($blog_countries as $country){ ?>
                    <option value="<?php // echo $country['id']; ?>">
                        <?php // echo $country['country']; ?>
                    </option>
                <?php //} ?>
            </select>
        </div>
    </div> -->

    <div class="w-100pr mt-20">
        <label for="article-form--input--title">
            <i class="text-red">*</i> Title:
        </label>
        <div style="position: relative">
            <input id="article-form--input--title"
                type="text"
                name="title"
                class="input mt-5"
                placeholder="Enter the title"
                data-validation-engine="validate[required,maxSize[255]]"
                data-prompt-position="topLeft:0,-5">
        </div>
    </div>

    <div class="w-100pr mt-20">
        <label for>
            <i class="text-red">*</i> Short decription:
        </label>
        <div class="file-upload__message mt-10 mb-10">
            <div class="alert alert-info">
                <i class="ep-icon ep-icon_info-stroke"></i>
                <div> Short Description is a piece of text that helps the visitors to understand what the blog is about.</div>
            </div>
        </div>
        <div style="position: relative">
            <textarea id="article-form--input--description"
                class="mt-5"
                name="description"
                placeholder="Enter the short description"
                data-validation-engine="validate[required,maxSize[500]]"
                data-prompt-position="topLeft:0,-5"></textarea>
        </div>
    </div>

    <div class="w-100pr mt-20">
        <div class="file-upload__group" style="position: relative">
            <label for="article-form--input--content">
                Photo:
            </label>
            <div class="file-upload__message mt-10">
                <div class="alert alert-info">
                    <i class="ep-icon ep-icon_info-stroke"></i>
                    <div>If you have an image that corresponds with your article please upload here</div>
                    <div> &bull; Only JPG, JPEG, PNG and BMP file formats are accepted.</div>
                    <div> &bull; The maximum file size has to be 10MB.</div>
                    <div> &bull; Min width: 1150px, Min height: 500px.</div>
                    <div> &bull; You cannot upload more than 1 photo.</div>
                </div>
            </div>
            <button class="file-upload__button button fileinput-button ml-0 mt-15" id="article-form--button--files" data-target="#article-form--input--files">
                <i class="ep-icon ep-icon_plus"></i> Upload a photo
            </button>
            <input
                type="file"
                name="files[]"
                id="article-form--input--files"
                class="file-upload__tag"
                accept=".jpg,.jpeg,.png,.bmp">

            <!-- The container for the uploaded files -->
            <div class="fileupload-queue files mt-10">
            </div>
        </div>
    </div>

    <div class="w-100pr mt-20">
        <div style="position: relative">
            <label for="article-form--input--content">
                <i class="text-red">*</i> Content:
            </label>
            <div class="file-upload__message mt-10 mb-10">
                <div class="alert alert-info">
                    <i class="ep-icon ep-icon_info-stroke"></i>
                    <div>
                        Write or Drop your text here. Once you have submitted your article <strong>no edits</strong> can be made;
                        therefore, please be sure you are happy with your content <strong>before</strong> clicking submit.
                    </div>
                    <div>
                        &bull; Use "Insert/edit button" (<i class="mce-ico mce-i-image"></i>) button to add or edit article images. Note: no more than <?php echo !empty($blogs_photos_amount) ? $blogs_photos_amount : 0; ?> images allowed.
                    </div>
                    <div>
                        &bull; Use "Insert/edit media" (<i class="mce-ico mce-i-media"></i>) button to embed videos into the article text.
                    </div>
                    <div>
                        &bull; Use "Insert/edit link" (<i class="mce-ico mce-i-link"></i>) button to add or edit the external links in the article text.
                    </div>
                </div>
            </div>
            <div style="position: relative" class="mt-10">
                <style>
                    div.mce-fullscreen {
                        top: 50px;
                    }
                </style>
                <textarea id="article-form--input--content"
                    name="content"
                    placeholder="Enter the content"></textarea>
            </div>
        </div>
    </div>

    <div class="w-100pr mt-20">
        <label for="article-form--input--category">
            Tags:
        </label>
        <div class="file-upload__message mt-10 mb-10">
            <div class="alert alert-info">
                <i class="ep-icon ep-icon_info-stroke"></i>
                <div> Tags are simple keywords that are related to the content and describe it informing quickly the reader what your content is about.</div>
                <div> Tags must be delimited by comma</div>
            </div>
        </div>
        <div class="mt-5" style="position: relative">
            <select id="article-form--input--tags"
                name="tags[]"
                multiple="multiple"
                data-prompt-position="topLeft:0,-10">
            </select>
        </div>
    </div>

    <div class="w-100pr">
        <div class="file-upload__message">
            <div class="alert alert-info">
                <i class="ep-icon ep-icon_warning-circle-stroke"></i>
                <div>
                    By submitting the form you agree to transfer the rights of the article to Export Portal without remuneration.
                    The blog can be used by our team for any purposes.
                </div>
                <div>
                    Copyright claims are not accepted.
                </div>
            </div>
        </div>
        <div>
            <label class="clearfix display-b pt-10 b-form-checkbox">
                <input class="label-input mr-5 validate[required]" type="checkbox" name="terms_cond" >
                <span class="pull-left">
                    <?php echo translate('label_i_agree_with');?>
                    <a data-title="Export Portal Terms and Conditions" href="<?php echo __SITE_URL;?>terms_and_conditions/tc_bloggers" target="_blank"><?php echo translate('label_terms_and_conditions');?></a>
                </span>
            </label>
        </div>

        <button
            type="button"
            id="article-form--button--preview"
            class="button mt-10 pull-left"
            data-form="#article-form"
            data-shadow-form="#article-preview-form">
            Preview
        </button>
        <button
            type="submit"
            id="article-form--button--submit"
            class="button mt-10 pull-right">
            Send
        </button>
    </div>
</form>
<form id="article-preview-form"
    name="article-preview-form"
    action="<?php echo $url['preview']; ?>"
    target="_blank"
    method="post"
    style="display: none">
</form>
<div id="article-form--success-notice" class="content-form content-form--modal  mt-35 mb-35 m-auto" style="display: none;">
    <p class="mw-100pr">
        Dear Blogger/Writer Candidate:
        <br />
        <br />
        Thank you for your Submission. Our team will review it and <strong>pending approval</strong>, you will receive an email notification, soon. Please, do check your e-mail.
    </p>
    <button type="button"
        id="article-form--notice-close"
        class="button mt-20 pull-left call-function"
        data-callback="closeModal">
        Close
    </button>
    <p class="mt-15 fs-10" style="color: gray; font-size: 10px">This message will close by itself after <span class="time-placeholder">20</span> second(s)</p>
</div>

<?php if( !DEBUG_MODE ){ ?>
<script type="application/javascript">
    tinyMCE.suffix = '.min';
    tinyMCE.baseURL = __files_url + "public/plug_bloggers/tinymce-4-8-3";
</script>
<?php } ?>

<script type="application/javascript">
    var timeoutAnchor;
    var closeModal = function() {
        $('.validate-modal').validationEngine('detach');
        $.fancybox.close();
    }
    var modalFormCallBack = function(form, caller){
        var url = form.attr('action');
        var data = form.serializeArray();

        addLoader('body');
        $.post(url, data, null, 'json').done(function(response){
            if(response.mess_type && response.mess_type !== 'success') {
                Messenger.notification(response.mess_type, response.message || 'Service is temporary unavailable');

                return;
            }

            var notice = $('#article-form--success-notice');
            if(notice.length) {
                form.closest('.fancybox-wrap').find('a.modal-close').removeClass('confirm-dialog').addClass('call-function');
                form.remove();
                notice.show();
                $.fancybox.update();
                tinymce.editors = [];

                var count = 20;
                var counter = setInterval(function(){
                    count--;
                    notice.find('.time-placeholder').text(count);
                }, 1000)

                timeoutAnchor = setTimeout(function() {
                    $.fancybox.close();
                    clearInterval(counter);
                }, 20000);
            } else {
                Messenger.notification(response.mess_type, response.message);
                $.fancybox.close();
            }
        }).fail(function(error) {
            Messenger.error('Service is temporary unavailable');
        }).always(function(){
            removeLoader('body');
        });
    };

    $(document).ready(function() {
        var inlineFileUpload = function (callback, value, meta) {
            if (meta.filetype !== 'image') {
                return;
            }

            var body = $(body);
            var url = this.settings.file_picker_upload_url || location.origin;
            var input = body.find('input#blog-inline-image-upload');
            var onLoad = function (file) {
                return function(){
                    addLoader('body');

                    // Note: Now we need to register the blob in TinyMCEs image blob
                    // registry. In the next release this part hopefully won't be
                    // necessary, as we are looking to handle it internally.
                    var id = 'blobid' + (new Date()).getTime();
                    var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                    var base64 = this.result.split(',')[1];
                    var blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);

                    // Note: Now we send blob of the image to the server to
                    // handle file upload. On finish we will call tinyMCE callback with
                    // uploaded file URL
                    var formData = new FormData();
                    formData.append("files[]", blobInfo.blob());
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: "JSON",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if(response.mess_type == 'success'){
                                // call the callback and populate the Title field with the file name
                                callback(__img_url + response.files[0].path, { title: file.name });
                            } else {
                                Messenger.notification(response.mess_type,  response.message);
                            }
                        },
                        complete: function(){
                            removeLoader('body');
                        }
                    });
                };
            }

            if(input.length === 0) {
                input = $('<input>');
                input.css({display: 'none'});
                input.attr('id', 'blog-inline-image-upload');
                input.attr('type', 'file');
                input.attr('accept', 'image/*');
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
        var saveContent = function () {
            tinymce.EditorManager.triggerSave();
        };
        var uploadFinished = function (e, data) {
            // var validationHandler = $("#article-form--input--files--validation-handle");
            var addImage = function (index, file) {
                var itemID = (new Date()).getTime();
                var closeButton = $('<a>').attr({
                    href: '#',
                    class: 'confirm-dialog',
                    title: 'Delete'
                }).data({
                    file: file.name,
                    action: __bloggers_url + 'bloggers/ajax_delete_images/' + imageUploadTimestamp,
                    message: 'Are you sure you want to delete this image?',
                    callback: function(button){
                        if(typeof fileuploadRemove !== 'undefined') {
                            fileuploadRemove(button)
                        }
                        // if(validationHandler.length) {
                        //     validationHandler.show();
                        // }
                    },
                }).append(
                    $('<i class="ep-icon ep-icon_remove"></i>')
                );

                $('.fileupload-queue').html(templateFileUpload('img', 'item-middle', itemID));
                $('#fileupload-item-' + itemID + ' .img-b').append('<img src="' + file.path + '">');
                $('#fileupload-item-' + itemID + ' .img-b').append('<input type="hidden" name="images[]" value="' + file.path + '">');
                $('#fileupload-item-' + itemID + ' .cancel').append(closeButton);
            };

            if(data.result.mess_type == 'success'){
                $.each(data.result.files, addImage);
                // if(validationHandler.length) {
                //     validationHandler.hide();
                //     validationHandler.siblings('.formError').remove();
                // }
            } else {
                Messenger.notification(data.result.mess_type,  data.result.message);
            }

            removeLoader('body');
        };
        var updateCategories = function () {
            var that = $(this);
            var articleLanguage = that.val();
            var onSuccess = function(resp){
                if (resp.mess_type == 'success') {
                    var options = '';
                    if (resp.categories.length > 0) {
                        $.each(resp.categories, function(index, category){
                            options += '<option value="' + category.id_category + '">' + category.name + '</option>';
                        });
                        categorySelect.html(options).prop('disabled', false);
                    } else {
                        options = '<option value="">Not found</option>';
                        categorySelect.html(options);
                    }
                } else {
                    Messenger.notification(resp.mess_type, resp.message);
                }
            };
            var onError = function(jqXHR){
                Messenger.error('Error: Please try again later.');
                jqXHR.abort();
            };

            $.ajax({
                url: categoriesUrl,
                type: 'POST',
                dataType: "JSON",
                data: { blog_lang: articleLanguage },
                beforeSend: function(){
                    categorySelect.prop('disabled', true);
                },
                success: onSuccess,
                error: onError,
            });
        };
        var delegateClick = function (e) {
            e.preventDefault();

            var self = $(this);
            var target = self.data('target') || null;
            if(null !== target) {
                $(target).click();
            }
        };
        var openPreview = function (e) {
            e.preventDefault();

            var self = $(this);
            var form = $(self.data('form') || null);
            var shadowForm = $(self.data('shadowForm') || null);
            if(!form.length || !shadowForm.length) {
                return;
            }

            var originalCallback = form.data('jqv').onValidationComplete;
            form.data('jqv').onValidationComplete = null;
            if(form.validationEngine('validate')) {
                shadowForm.empty();
                form.data('jqv').onValidationComplete = originalCallback;
                form.serializeArray().forEach(function(entry) {
                    var shadowInput = $('<input>').attr({
                        type: 'hidden',
                        name: entry.name,
                        value: entry.value
                    });
                    shadowForm.append(shadowInput);
                });
                shadowForm.submit();
            } else {
                form.data('jqv').onValidationComplete = originalCallback;
                Messenger.error("Form fields contain invalid values.\nPlease check the entered information to proceed further");
            }
        };

        var form = $("#article-form");
        var categorySelect = $('#article-form--input--category');
        // var countrySelect = $('#article-form--input--country');
        var agreementInput = $('.label-input');
        var langSelect = $('#article-form--input--lang');
        var tagsSelect = $('#article-form--input--tags');
        var fileUploader = $('#article-form--input--files');
        var uploadButton = $('#article-form--button--files');
        var previewButton = $('#article-form--button--preview');
        var imageUploadMaxSize = "<?php echo $fileupload_max_file_size?>";
        var imageUploadTimestamp = "<?php echo $upload_folder;?>";
        var categoriesUrl = __bloggers_url + 'bloggers/ajax_get_categories';
        var imageUploadUrl = __bloggers_url + 'bloggers/ajax_upload_images/' + imageUploadTimestamp;
        var imagePickerUploadUrl = __bloggers_url + 'bloggers/ajax_upload_images/' + imageUploadTimestamp + '/inline';
        var contentEditorHeight = 1000;
        var winWidth = $( window ).width();
        if(winWidth <= 1024 && winWidth >= 768){
            contentEditorHeight = 500;
        } else if(winWidth <= 767){
            contentEditorHeight = 300;
        }

        var contentEditorOptions = {
            selector:'#article-form--input--content',
            theme: 'modern',
            height: contentEditorHeight,
            max_height: contentEditorHeight,
            min_height: contentEditorHeight,
			timestamp: imageUploadTimestamp,
			menubar: false,
			statusbar : false,
			media_poster: false,
			media_alt_source: false,
            relative_urls: false,
			plugins: [
				"autolink lists link media image contextmenu paste fullscreen"
			],
			dialog_type : "modal",
			toolbar: "undo redo | bold italic underline | numlist bullist | link media image | fullscreen",
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
            remote_preview_url: __bloggers_url + 'bloggers/ajax_preview_content',
            remote_preview_debug: true,
            remote_preview_map_to: function(responseBody){
                if(typeof responseBody === 'string') {
                    try {
                        var payload = $.parseJSON(responseBody);

                        return payload.content || responseBody;
                    } catch (error) {
                        return responseBody;
                    }

                    return $.parseJSON(responseBody) || responseBody;
                }

                return responseBody.content || '';
            },
			resize: false
        }
        var uploadOptions = {
            url: imageUploadUrl,
            dataType: 'json',
            done: uploadFinished,
            maxFileSize: imageUploadMaxSize,
            acceptFileTypes: /(\.|\/)(jpe?g|png|bmp)$/i,
            loadImageFileTypes: /^image\/(p?jpeg|png|bmp|x-windows-bmp)$/,
            maxNumberOfFiles: 1,
            beforeSend: function () {
                addLoader('body');
            },
            processalways: function(e,data){
                if (data.files.error){
                    Messenger.error( data.files[0].error);
                }

                removeLoader('body');
            }
        };

        form.on('jqv.form.validating', saveContent);
        langSelect.on('change', updateCategories);
        uploadButton.on('click', delegateClick)
        previewButton.on('click', openPreview);
        tinymce.remove('#article-form--input--content');
        tinymce.init(contentEditorOptions);
        fileUploader.fileupload(uploadOptions).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');
        categorySelect.select2({width: "100%", placeholder: "Select category", minimumResultsForSearch: Infinity});
        // countrySelect.select2({width: "100%", placeholder: "Select country", minimumResultsForSearch: 2});
        langSelect.select2({width: "100%", placeholder: "Select language", minimumResultsForSearch: Infinity});
        tagsSelect.select2({
            width: '100%',
            tags: true,
            multiple: true,
            placeholder: "Enter the tags",
            minimumResultsForSearch: Infinity,
            tokenSeparators: [',']
        });

        // Using javascript black arts we select the first element in selects:
        categorySelect.val(categorySelect.find('option').eq(1).val()).trigger('change.select2');
        // countrySelect.val(countrySelect.find('option').eq(1).val()).trigger('change.select2');
        langSelect.val(langSelect.find('option').eq(1).val()).trigger('change.select2');

        // Init checkboxes
        agreementInput.iCheck({
            labelHover: false,
            cursor: true
        });
    });
</script>
