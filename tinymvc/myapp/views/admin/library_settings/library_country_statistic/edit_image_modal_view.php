<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<div class="wr-modal-b">
    <div class="modal-b__form">
        <div class="modal-b__content w-700 pb-30">
            <div class="description-b pb-5 pt-15">
                <h3>Image</h3>
            </div>

            <div class="juploader-b">
                <span class="btn btn-success fileinput-button">
                    <i class="ep-icon ep-icon_plus"></i>
                    <span>Select file...</span>
                    <!-- The file input field used as target for the file upload widget -->
                    <input id="add_fileupload" type="file" name="files[]">
                </span>
                <span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...</span>
                <div class="info-alert-b mt-10">
                    <i class="ep-icon ep-icon_info"></i>
                    <div> &bull; The maximum file size has to be 3MB.</div>
                    <div> &bull; Min width: 1000px, Min height: 200px.</div>
                    <div> &bull; File available formats (jpg,jpeg,png,gif,bmp).</div>
                </div>

                <!-- The container for the uploaded files -->
                <div class="fileupload-queue mt-30 clearfix">
                    <img class="export-import-image" src="<?php echo $path ?>" />
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('#add_fileupload').fileupload({
        url: '<?php echo __SITE_URL; ?>library_country_statistic/ajax_upload_image',
        dataType: 'json',
        formData: {
            id: <?php echo $id; ?>
        },
        maxFileSize: 3 * 1024 * 1024,
        beforeSend: function (event, files, index, xhr, handler, callBack) {
            $('.fileinput-loader-btn').fadeIn();
        },
        done: function (e, data) {
            if(data.result.mess_type === 'success'){
                closeFancyBox();
                window.dtExportImportStatistic && window.dtExportImportStatistic.fnDraw(false);
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
