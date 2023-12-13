<form method="post" class="validateModal relative-b" id="addPartnerForm">
    <div class="wr-form-content w-900 mh-700">
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
            <tbody>
                <tr>
                    <td>Name</td>
                    <td>
                        <input class="w-100pr validate[required,maxSize[100]]"
                               type="text"
                               name="name"
                               value="<?php echo cleanOutput($partner['name'] ?? ''); ?>"
                               placeholder="Name" />
                    </td>
                </tr>
                <tr>
                    <td>Image</td>
                    <td>
                        <span class="btn btn-success fileinput-button">
                            <i class="ep-icon ep-icon_plus"></i>
                            <span>Select file...</span>
                            <!-- The file input field used as target for the file upload widget -->
                            <input id="upload_image" type="file" name="image">
                        </span>

                        <span class="image-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL . 'public/img/loader.gif'; ?>" alt="loader"> Uploading...</span>

                        <?php if (!empty($imageRules)) { ?>
                            <div class="info-alert-b mt-10">
                                <i class="ep-icon ep-icon_info"></i>
                                <?php if (!empty($imageRules['size_placeholder'])) { ?>
                                    <div> &bull; <?php echo 'The maximum file size has to be ' . cleanOutput($imageRules['size_placeholder']) . '.'; ?></div>
                                <?php } ?>
                                <?php if (!empty($imageRules['format'])) { ?>
                                    <div> &bull; <?php echo 'Allowed formats: ' . cleanOutput($imageRules['format']) . '.'; ?></div>
                                <?php } ?>
                                <?php if (!empty($imageRules['min_width'])) { ?>
                                    <div> &bull; <?php echo 'Min width: ' . cleanOutput($imageRules['min_width']) . '.'; ?></div>
                                <?php } ?>
                                <?php if (!empty($imageRules['min_height'])) { ?>
                                    <div> &bull; <?php echo 'Min height: ' . cleanOutput($imageRules['min_height']) . '.'; ?></div>
                                <?php } ?>
                                <?php if (!empty($imageRules['limit'])) { ?>
                                    <div> &bull; <?php echo 'You cannot upload more than ' . cleanOutput($imageRules['limit']) . ' image.'; ?></div>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <!-- The container for the uploaded files -->
                        <div class="fileupload-queue files mt-10">
                            <?php if (!empty($partner['image'])) { ?>

                                <div class="uploadify-queue-item item-middle" id="js-already-uploaded-image">
                                    <div class="img-b">
                                        <img src="<?php echo $partner['image']; ?>" />
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="wr-form-btns clearfix">
        <input type="hidden" name="upload_folder" value="<?php echo $uploadFolder; ?>">
        <button class="pull-right btn btn-default" type="submit">
            <span class="ep-icon ep-icon_ok"></span>Save
        </button>
    </div>
</form>

<script type="text/javascript">
    $('#upload_image').fileupload({
            url: '<?php echo __SITE_URL . 'ep_events_partners/ajax_operations/upload_image/' . $uploadFolder; ?>',
            dataType: 'json',
            maxFileSize: <?php echo config('fileupload_max_file_size'); ?>,
            beforeSend: function() {
                $('.image-loader-btn').fadeIn();
            },
            done: function(e, data) {
                if (data.result.mess_type == 'success') {
                    $('#js-already-uploaded-image').hide();

                    $.each(data.result.files, function(index, file) {
                        renderUploadedImage(file);
                    });
                } else {
                    systemMessages(data.result.message, 'message-' + data.result.mess_type);
                }

                $('.image-loader-btn').fadeOut();
            },
            processalways: function(e, data) {
                if (data.files.error) {
                    systemMessages(data.files[0].error, 'message-error');
                }
            }
        }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');

    function modalFormCallBack(form, data_table) {
        var form = $(form);

        $.ajax({
            type: 'POST',
            url: '<?php echo $submitFormUrl; ?>',
            data: form.serialize(),
            beforeSend: function() {
                showLoader(form);
            },
            dataType: 'json',
            success: function(data) {
                systemMessages(data.message, 'message-' + data.mess_type);

                if (data.mess_type == 'success') {
                    closeFancyBox();
                    if (data_table != undefined)
                        data_table.fnDraw();
                } else {
                    hideLoader(form);
                }
            }
        });
    }

    var showImage = function(element) {
        $('#js-already-uploaded-image').show();
    }

    var renderUploadedImage = function (file) {
        var itemID = +(new Date());
        var element = $('.fileupload-queue').find('.img-b');
        if (element) {
            $('.fileupload-queue').empty();
        }

        $('.fileupload-queue').append(templateFileUpload('img', 'item-middle', itemID));
        $('#fileupload-item-' + itemID + ' .img-b').append('<img src="' + file.url + '" alt="img">');
        $('#fileupload-item-' + itemID + ' .img-b').append('<input type="hidden" name="partner_image" value="' + file.path + '">');
        $('#fileupload-item-' + itemID + ' .cancel').append('<a class="call-function" data-callback="fileuploadRemove" data-additional-callback="showImage" data-action="<?php echo __SITE_URL . 'ep_events_partners/ajax_operations/delete_temp_image/' . $uploadFolder; ?>" data-file="' + file.name + '" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a>');
    };
</script>
