<form method="post" class="validateModal relative-b">
    <div class="wr-form-content w-700 mh-700">
        <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr vam-table mt-15">
            <tbody>
                <tr>
                    <td>Title:</td>
                    <td><input type="text" name="title" value="<?php echo cleanOutput($compilation['title'] ?? ''); ?>" class="w-100pr validate[required,maxSize[256]]" /></td>
                </tr>
                <tr>
                    <td>URL:</td>
                    <td><input type="text" name="url" value="<?php echo cleanOutput($compilation['url'] ?? ''); ?>" class="w-100pr validate[required,custom[url]]" /></td>
                </tr>
                <tr>
                    <td>Keywords</td>
                    <td>
                        <select id="js-items-compilatation-selet2" name="keywords" data-title="keywords" class="validate[required]" multiple="multiple">
                            <?php foreach ($selectedItems as $selectedItem) { ?>
                                <option value="<?php echo $selectedItem['id']; ?>" title="<?php echo cleanOutput($selectedItem['title']); ?>" selected><?php echo $selectedItem['title']; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Background images</td>
                    <td>
                        <h3 class="w-100pr tt-capitalize">mobile/tablet</h3>
                        <div class="mb-10">
                            <span class="btn btn-success fileinput-button tablet-fileinput-button">
                                <i class="ep-icon ep-icon_plus"></i>
                                <span>Select files...</span>
                                <input id="js-fileupload-tablet" type="file" name="image">
                            </span>
                            <span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __SITE_URL . 'public/img/loader.gif'; ?>" alt="loader"> Uploading...</span>
                            <?php if (!empty($tabletImagesRules = config('img.items_compilation.tablet.rules'))) { ?>
                                <div class="info-alert-b">
                                    <i class="ep-icon ep-icon_info"></i>

                                    <?php foreach ($tabletImagesRules as $rule => $value) { ?>
                                        <div> • <?php echo ucfirst($rule); ?>: <strong><?php echo $value; ?></strong>.</div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            <div class="fileupload-queue js-fileupload-tablet-queue files mt-10">
                                <?php if (!empty($compilation['background_images']['tablet'])) { ?>
                                    <div class="uploadify-queue-item" id="js-already-uploaded-tablet-image">
                                        <div class="img-b">
                                            <img src="<?php echo getDisplayImageLink(['{FILE_NAME}' => $compilation['background_images']['tablet']], 'items_compilation.tablet'); ?>" alt="Tablet image">
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <h3 class="w-100pr tt-capitalize">desktop</h3>
                        <div class="mb-10">
                            <span class="btn btn-success fileinput-button desktop-fileinput-button">
                                <i class="ep-icon ep-icon_plus"></i>
                                <span>Select files...</span>
                                <input id="js-fileupload-desktop" type="file" name="image">
                            </span>
                            <span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __SITE_URL . 'public/img/loader.gif'; ?>" alt="loader"> Uploading...</span>
                            <?php if (!empty($desktopImagesRules = config('img.items_compilation.desktop.rules'))) { ?>
                                <div class="info-alert-b">
                                    <i class="ep-icon ep-icon_info"></i>

                                    <?php foreach ($desktopImagesRules as $rule => $value) { ?>
                                        <div> • <?php echo ucfirst($rule); ?>: <strong><?php echo $value; ?></strong>.</div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            <div class="fileupload-queue js-fileupload-desktop-queue files mt-10">
                                <?php if (!empty($compilation['background_images']['desktop'])) { ?>
                                    <div class="uploadify-queue-item" id="js-already-uploaded-desktop-image">
                                        <div class="img-b">
                                            <img src="<?php echo getDisplayImageLink(['{FILE_NAME}' => $compilation['background_images']['desktop']], 'items_compilation.desktop'); ?>" alt="Desktop image">
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="wr-form-btns clearfix">
        <input type="hidden" name="upload_folder" value="<?php echo $uploadFolder; ?>">
        <?php if (!empty($compilation['id'])) { ?>
            <input type="hidden" name="id" value="<?php echo $compilation['id']; ?>">
        <?php } ?>
        <button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span>Save</button>
    </div>
</form>
<script type="text/javascript" src="<?php echo __FILES_URL; ?>public/plug_admin/select2-4-0-3/js/select2.min.js"></script>
<script>
    function formatSelection({
        text,
        title,
        id
    }) {

        if (!title) {
            return text
        }

        return `
        <div class="flex-display">
            ${title}
            <input name="itemsIds[]" type="hidden" value="${id}" title="${title}">
        </div>
    `;
    }

    function formatResult({
        loading,
        text,
        title,
        photoUrl,
        itemUrl,
        id
    }) {
        if (loading) {
            return text
        };

        return `
            <div class="flex-display flex-ai--c fs-12">
                <img class="w-30" src="${photoUrl}" alt="${title}" />
                ${title}
                <a
                    class="js-select2-results-link relative-b h-30 w-30 flex-display flex-ai--c flex-jc--c"
                    style="margin-left: auto; z-index: 999;"
                    href="${itemUrl}"
                    target="_blank"
                >
                    <i class="ep-icon ep-icon_link "></i>
                </a>
                <input name="name[]" type="hidden" id="${id}">
            </div>
         `;
    };

    function initSelect2(selector) {
        var select2 = $(selector);
        select2 = $(selector).select2({
            ajax: {
                type: "POST",
                url: '<?php echo __SITE_URL; ?>items_compilation/ajax_search_items',
                dataType: "json",
                delay: 300,
                data(params) {
                    return {
                        page: params.page,
                        keywords: params.term,
                    };
                },
                processResults(data, params) {
                    return {
                        results: data.items[0],
                    };
                },
            },
            theme: "default",
            width: "544px",
            closeOnSelect: false,
            scrollAfterSelect: false,
            minimumInputLength: 2,
            maximumSelectionLength: 10,
            placeholder: "Keywords",
            templateResult: formatResult,
            templateSelection: formatSelection,
            escapeMarkup(markup) {
                return markup;
            },
            language: {
                inputTooShort: function (e) {
                    return "Enter a word consisting of 2 or more characters";
                }
            }
        });

        select2
            .data("select2")
            .$container.attr("id", "keywords--formfield--container")
            .addClass("validate[required]")
            .setValHookType("keywords");

        $.valHooks["keywords"] = {
            get() {
                return select2.val() || [];
            },
            set(el, val) {
                select2.val(val);
            },
        };

        if (select2) {
            $('body').unbind("mouseover mouseenter");
        }

        select2.on("select2:selecting", function(e) {
            if (e?.params?.args?.originalEvent?.target?.classList.contains('ep-icon')) {
                e.preventDefault();
            }
            if(document.querySelector(".select2-selection__rendered").childElementCount > 10) {
                e.preventDefault();
                systemMessages("Should be selected max 10 items.");
            }
        });
    }

    initSelect2("#js-items-compilatation-selet2");

    $('#js-fileupload-tablet').fileupload({
        url: '<?php echo __SITE_URL . 'items_compilation/ajax_operations/upload_temp_image/tablet/' . $uploadFolder; ?>',
        dataType: 'json',
        maxFileSize: <?php echo config('fileupload_max_file_size'); ?>,
        done: function(e, data) {
            if (data.result.mess_type == 'success') {
                $('#js-already-uploaded-tablet-image').hide();
                $('.tablet-fileinput-button').hide();

                $.each(data.result.files, function(index, file) {
                    var itemID = +(new Date());
                    $('.js-fileupload-tablet-queue').append(templateFileUpload('img', '', itemID));
                    $('#fileupload-item-' + itemID + ' .img-b').append('<img src="' + file.path + '" alt="img">');
                    $('#fileupload-item-' + itemID + ' .img-b').append('<input type="hidden" name="tablet_image" value="' + file.name + '">');
                    $('#fileupload-item-' + itemID + ' .cancel').append('<a class="call-function" data-callback="fileuploadRemove" data-additional-callback="showTabletImage" data-action="<?php echo __SITE_URL . 'items_compilation/ajax_operations/delete_temp_image/tablet/' . $uploadFolder; ?>" data-file="' + file.name + '" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a>');
                });

                $.fancybox.reposition();
            } else {
                systemMessages(data.result.message, 'message-' + data.result.mess_type);
            }
        },
        processalways: function(e, data) {
            if (data.files.error) {
                systemMessages(data.files[0].error, 'message-error');
            }
        }
    }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

    $('#js-fileupload-desktop').fileupload({
        url: '<?php echo __SITE_URL . 'items_compilation/ajax_operations/upload_temp_image/desktop/' . $uploadFolder; ?>',
        dataType: 'json',
        maxFileSize: <?php echo config('fileupload_max_file_size'); ?>,
        done: function(e, data) {
            if (data.result.mess_type == 'success') {
                $('#js-already-uploaded-desktop-image').hide();
                $('.desktop-fileinput-button').hide();

                $.each(data.result.files, function(index, file) {
                    var itemID = +(new Date());
                    $('.js-fileupload-desktop-queue').append(templateFileUpload('img', '', itemID));
                    $('#fileupload-item-' + itemID + ' .img-b').append('<img src="' + file.path + '" alt="img">');
                    $('#fileupload-item-' + itemID + ' .img-b').append('<input type="hidden" name="desktop_image" value="' + file.name + '">');
                    $('#fileupload-item-' + itemID + ' .cancel').append('<a class="call-function" data-callback="fileuploadRemove" data-additional-callback="showDesktopImage" data-action="<?php echo __SITE_URL . 'items_compilation/ajax_operations/delete_temp_image/desktop/' . $uploadFolder; ?>" data-file="' + file.name + '" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a>');
                });

                $.fancybox.reposition();
            } else {
                systemMessages(data.result.message, 'message-' + data.result.mess_type);
            }
        },
        processalways: function(e, data) {
            if (data.files.error) {
                systemMessages(data.files[0].error, 'message-error');
            }
        }
    }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

    function modalFormCallBack(form, dataTable) {
        var $form = $(form);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . 'items_compilation/ajax_operations/' . (empty($compilation) ? 'add_compilation' : 'edit_compilation'); ?>',
            data: $form.serialize(),
            beforeSend: function() {
                showLoader($form);
            },
            dataType: 'json',
            success: function(data) {
                systemMessages(data.message, 'message-' + data.mess_type);

                if (data.mess_type == 'success') {
                    closeFancyBox();

                    if (dataTable) {
                        dataTable.fnDraw(false);
                    }
                } else {
                    hideLoader(form);
                }
            }
        });
    }

    var showTabletImage = function(element) {
        $('#js-already-uploaded-tablet-image').show();
        $('.tablet-fileinput-button').show();
    }

    var showDesktopImage = function(element) {
        $('#js-already-uploaded-desktop-image').show();
        $('.desktop-fileinput-button').show();
    }
</script>
