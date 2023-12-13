<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<div class="wr-modal-b">
    <div class="modal-b__form">
        <div class="modal-b__content w-700 pb-30 main-dashboard-form">
            <form id="seller-banner-form" method="post" class="validateModal relative-b">
                <div class="mb-5 clearfix">
                    <label class="modal-b__label">Enter the link</label>
                    <input type="text" value="<?php echo $banner['link']; ?>" name="link" class="w-100pr validate[required,maxSize[300]]">
                </div>

                <input type="hidden" value="<?php echo $banner['image']; ?>" name="image" id="banner-image-input">

                <?php if (!empty($banner['id'])) { ?>
                    <input type="hidden" value="<?php echo $banner['id']; ?>" name="id" id="banner-image-input">
                <?php } ?>

                <div class="mb-5 clearfix">
                    <label class="modal-b__label">Select target page</label>
                    <div class="row tac">
                        <div class="col-xs-4">
                            <label>
                                <input class="relative-b t-2" type="radio" <?php echo $banner['page'] == 'home' ? 'checked' : ''; ?> name="page" value="home"> On home page
                            </label>
                        </div>
                        <div class="col-xs-4">
                            <label>
                                <input class="relative-b t-2" type="radio" <?php echo $banner['page'] == 'store' ? 'checked' : ''; ?> name="page" value="store"> On store page
                            </label>
                        </div>
                        <div class="col-xs-4">
                            <label>
                                <input class="relative-b t-2" type="radio" <?php echo $banner['page'] == 'both' ? 'checked' : ''; ?> name="page" value="both"> On both pages
                            </label>
                        </div>
                    </div>
                </div>

                <div class="description-b pb-5">
                    <label class="modal-b__label">Banner image</label>
                    <div class="juploader-b">
                    <span class="btn btn-success fileinput-button">
                        <i class="ep-icon ep-icon_plus"></i>
                        <span>Select file...</span>
                        <!-- The file input field used as target for the file upload widget -->
                        <input id="add_fileupload" type="file" name="file">
                    </span>
                        <span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...</span>
                        <div class="info-alert-b mt-10">
                            <i class="ep-icon ep-icon_info"></i>
                            <div> &bull; The maximum file size has to be 3MB.</div>
                            <div> &bull; Minimum width: 870px, minimum height: 430px.</div>
                            <div> &bull; File available formats (jpg, jpeg, png, gif, bmp).</div>
                        </div>

                        <!-- The container for the uploaded files -->
                        <div class="fileupload-queue mt-30 clearfix tac">
                            <img class="uploaded-seller-banner-image" src="<?php echo __IMG_URL . getImage("$path/{$banner['image']}", 'public/img/no_image/no-image-512x512.png'); ?>" />
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-b__btns clearfix">
            <a id="save-banner" class="btn btn-primary pull-right" href="#">
                <i class="ep-icon ep-icon_save"></i> Save banner
            </a>
        </div>

    </div>
</div>

<script>
    function modalFormCallBack($form) {
        $.ajax({
            type: 'post',
            url: '<?php echo __SITE_URL; ?>seller_banners/form_banner_save',
            data: $form.serialize(),
            dataType: 'json',
            success: function(respone) {
                if(respone.mess_type === 'success') {
                    closeFancyBox();
                    window.bannersTable && window.bannersTable.fnDraw(false);
                } else {
                    systemMessages(respone.message, 'message-' + respone.mess_type);
                }
            }
        });
    }

    $('#save-banner').on('click', function (e) {
        e.preventDefault();
        $('#seller-banner-form').submit();
    });

    $('#add_fileupload').fileupload({
        url: '<?php echo __SITE_URL; ?>seller_banners/ajax_upload_image',
        dataType: 'json',
        formData: {
            id: 1
        },
        maxFileSize: 3 * 1024 * 1024,
        beforeSend: function (event, files, index, xhr, handler, callBack) {
            $('.fileinput-loader-btn').fadeIn();
        },
        done: function (e, data) {
            if(data.result.mess_type === 'success') {
                $('.uploaded-seller-banner-image').attr('src', '<?php echo __IMG_URL; ?>' + data.result.path + '/' + data.result.name);
                $('#banner-image-input').val(data.result.name);
                //closeFancyBox();
                //window.dtExportImportStatistic && window.dtExportImportStatistic.fnDraw(false);
            } else {
                systemMessages(data.result.message, 'message-' + data.result.mess_type);
            }

            $('.fileinput-loader-btn').fadeOut();
        },
        processalways: function(e,data) {
            if (data.files.error) {
                systemMessages(data.files[0].error, 'message-error');
            }
        }
    }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');
</script>
