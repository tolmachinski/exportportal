<script type="text/javascript" src="<?php echo __SITE_URL . 'public/plug_admin/tinymce-4-3-10/tinymce.min.js';?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_admin/jquery-tags-input-master/jquery.tagsinput.min.js');?>"></script>

<div class="wr-modal-b">
	<form method="post" id="admin-downloadable-materials-form" name="form-add" class="relative-b validateModal">
		<div class="wr-form-content w-900 h-550">

            <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr m-auto vam-table mt-20">
                <tbody>
                    <tr>
						<td width="20%">Title</td>
						<td>
                            <div class="form-group">
                                <input type="text" name="title" class="w-100pr validate[required,maxSize[250]] w1Input" value="<?php echo cleanOutput($downloadableMaterials['title'] ?: '');?>" placeholder="Downloadable material title"/>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr m-auto vam-table mt-20">
                <tbody>
                    <tr>
						<td width="20%">Short description</td>
						<td>
                            <div class="form-group">
                                <textarea name="short_description" class="w-100pr validate[required,maxSize[500]] w1Textarea" placeholder="Short description"><?php echo $downloadableMaterials['short_description'] ?: '';?></textarea>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table cellspacing="0" cellpadding="0" class="data table-bordered w-100pr m-auto vam-table mt-20">
				<tr>
					<td>
						<textarea name="content" class="js-content"><?php echo $downloadableMaterials['content'] ?: '';?></textarea>
					</td>
				</tr>
            </table>

            <table cellspacing="0" cellpadding="0" id="cover-upload" class="data table-bordered w-100pr m-auto vam-table">
				<tbody>
                    <tr>
                        <td width="20%">Upload cover image</td>
                        <td>
                            <span class="btn btn-success fileinput-button">
                                <i class="ep-icon ep-icon_plus"></i>
                                <span>Select file...</span>
                                <!-- The file input field used as target for the file upload widget -->
                                <input id="upload_cover_image" type="file" name="cover" accept=".jpg,.jpeg,.png">
                            </span>

                            <span class="main_image-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL . 'public/img/loader.gif';?>" alt="loader"> Uploading...</span>

                            <?php if (!empty($coverImageRules)) {?>
                                <div class="info-alert-b mt-10">
                                    <i class="ep-icon ep-icon_info"></i>
                                    <?php if (!empty($coverImageRules['size_placeholder'])) {?>
                                        <div> &bull; <?php echo 'The maximum file size has to be ' . cleanOutput($coverImageRules['size_placeholder']) . '.';?></div>
                                    <?php }?>
                                    <?php if (!empty($coverImageRules['format'])) {?>
                                        <div> &bull; <?php echo 'Allowed formats: ' . cleanOutput($coverImageRules['format']) . '.';?></div>
                                    <?php }?>
                                    <?php if (!empty($coverImageRules['min_width'])) {?>
                                        <div> &bull; <?php echo 'Min width: ' . cleanOutput($coverImageRules['min_width']) . '.';?></div>
                                    <?php }?>
                                    <?php if (!empty($coverImageRules['max_width'])) {?>
                                        <div> &bull; <?php echo 'Max width: ' . cleanOutput($coverImageRules['max_width']) . '.';?></div>
                                    <?php }?>
                                    <?php if (!empty($coverImageRules['min_height'])) {?>
                                        <div> &bull; <?php echo 'Min height: ' . cleanOutput($coverImageRules['min_height']) . '.';?></div>
                                    <?php }?>
                                    <?php if (!empty($coverImageRules['max_height'])) {?>
                                        <div> &bull; <?php echo 'Max height: ' . cleanOutput($coverImageRules['max_height']) . '.';?></div>
                                    <?php }?>
                                    <?php if (!empty($coverImageRules['limit'])) {?>
                                        <div> &bull; <?php echo 'You cannot upload more than ' . cleanOutput($coverImageRules['limit']) . ' image.';?></div>
                                    <?php }?>
                                </div>
                            <?php }?>

                            <!-- The container for the uploaded files -->
                            <div class="fileupload-queue files mt-10">
                                <?php if (!empty($downloadableMaterials['cover'])) {?>
                                    <div class="uploadify-queue-item item-middle" id="js-already-uploaded-cover-image">
                                        <div class="img-b">
                                            <img src="<?php echo __IMG_URL . getImage(getDownloadableMaterialsCoverPath($downloadableMaterials['id'], (string) $downloadableMaterials['cover']), 'public/img/no_image/no-image-512x512.png');?>"/>
                                        </div>
                                    </div>
                                <?php }?>
                            </div>
                        </td>
                    </tr>
				</tbody>
            </table>

            <table cellspacing="0" cellpadding="0" id="file-upload" class="data table-bordered w-100pr m-auto vam-table">
				<tbody>
                    <tr>
                        <td>
                            <label class="input-label">Upload File</label>
                            <?php if (!empty($pdfRules)) {?>
                                <div class="info-alert-b mb-15">
                                    <i class="ep-icon ep-icon_info"></i>
                                    <?php if (!empty($pdfRules['size_placeholder'])) {?>
                                        <div> &bull; <?php echo 'The maximum file size has to be ' . cleanOutput($pdfRules['size_placeholder']) . '.';?></div>
                                    <?php }?>
                                    <?php if (!empty($pdfRules['format'])) {?>
                                        <div> &bull; <?php echo 'Allowed formats: ' . cleanOutput($pdfRules['format']) . '.';?></div>
                                    <?php }?>
                                </div>
                            <?php }?>

                            <div class="juploader-b">
                                <span class="btn btn-success fileinput-button">
                                    <span><?php echo translate("blog_dashboard_modal_field_photo_upload_button_text");?></span>
                                    <input class="js-upload-field" type="file" accept=".pdf" name="file[]">
                                    <input class="js-upload-temp" type="hidden" name="file">
                                </span>
                                <span class="fileinput-loader-btn fileinput-loader-img js-upload-loader" style="display:none;">
                                    <img class="image" src="<?php echo __IMG_URL . 'public/img/loader.gif';?>" alt="loader">
                                    <?php echo translate("blog_dashboard_modal_field_photo_upload_placeholder");?>
                                </span>
                                <div class="pt-10 js-upload-name">
                                    <strong><?php echo $downloadableMaterials['file'] ?: '';?></strong>
                                </div>
                            </div>
                        </td>
                    </tr>
				</tbody>
            </table>
		</div>
		<div class="wr-form-btns clearfix">
            <input type="hidden" name="upload_folder" value="<?php echo $uploadFolder;?>">
			<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span>Save</button>
		</div>
	</form>
</div>

<script type="text/javascript">
    formOperations = {

        fileName: $('#file-upload .js-upload-name'),
        fileLoader: $("#file-upload .js-upload-loader"),
        tempFileName: $("#file-upload .js-upload-temp"),
        fileField: $('#file-upload .js-upload-field'),
        initTinyMce: function() {
            tinymce.init({
                selector:'.js-content',
                menubar: false,
                statusbar : true,
                height : 300,
                plugins: ["lists charactercount powerpaste link"],
                style_formats: [
                    {title: 'H2', block: 'h2'}
                ],
                powerpaste_html_import: "merge",
                toolbar: "styleselect | bold italic underline | bullist | link",
                resize: false
            });
        },
        uploadTempFile: function () {
            formOperations.fileField.fileupload({
                dataType: 'json',
                maxNumberOfFiles: 1,
                acceptFileTypes: /(pdf)$/i,
                url: '<?php echo __SITE_URL . 'downloadable_materials/ajaxUploadTmpFiles/' . $uploadFolder;?>',
                beforeSend: function (e, data) {
                    formOperations.fileLoader.show();
                },
                done: function (e, data) {
                    if (data.result.data) {
                        formOperations.tempFileName.val(data.result.data.new_name);
                        formOperations.fileName.html("<strong>Uploaded</strong> " + data.result.data.old_name);
                    }
                    if (data.result.mess_type == 'error') {
                        return systemMessages(data.result.message, 'error');
                    }
                    formOperations.fileLoader.hide();
                },
                processalways: function(e,data) {
                    if (data.files.error) {
                        systemMessages(data.files[0].error, 'error');
                    }
                }
            });
        }
    }

    formOperations.initTinyMce();
    formOperations.uploadTempFile();

    $('#upload_cover_image').fileupload({
        url: '<?php echo __SITE_URL . 'downloadable_materials/uploadTempCoverImage/' . $uploadFolder;?>',
        dataType: 'json',
        maxFileSize: <?php echo config('fileupload_max_file_size');?>,
        beforeSend: function () {
            $('.main_image-loader-btn').fadeIn();
        },
        done: function (e, data) {
            if (data.result.mess_type == 'success') {
                $('#js-already-uploaded-cover-image').hide();

                $.each(data.result.files, function (index, file) {
                    var itemID = +(new Date());
                    $('.fileupload-queue').append(templateFileUpload('img', 'item-middle', itemID));
                    $('#fileupload-item-' + itemID + ' .img-b').append('<img src="'+ file.path +'" alt="img">');
                    $('#fileupload-item-' + itemID + ' .img-b').append('<input type="hidden" name="cover_image" value="' + file.path + '">');
                    $('#fileupload-item-' + itemID + ' .cancel').append('<a class="call-function" data-callback="fileuploadRemove" data-additional-callback="showCoverImage" data-action="<?php echo __SITE_URL . 'downloadable_materials/removeTempCoverImage/' . $uploadFolder;?>" data-file="' + file.name + '" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a>');
                });
            } else {
                systemMessages( data.result.message, 'message-' + data.result.mess_type );
            }

            $('.main_image-loader-btn').fadeOut();
        },
        processalways: function(e,data){
            if (data.files.error){
                systemMessages( data.files[0].error, 'message-error' );
            }
        }
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');

    var showCoverImage = function (element) {
        $('#js-already-uploaded-cover-image').show();
    }

    var modalFormCallBack = function(form) {
        var form = $(form),
            wrform = form.closest('.wr-modal-b');

        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . 'downloadable_materials/ajaxPopupAdministration/' . (empty($downloadableMaterials) ? 'create' : 'update/' . $downloadableMaterials['id']);?>',
            data: form.serialize(),
            dataType: 'JSON',
            beforeSend: function() {
                showFormLoader(wrform, 'Sending right...');
                form.find('button[type=submit]').addClass('disabled');
            },
            success: function(resp) {
                hideFormLoader(wrform);
                systemMessages(resp.message, 'message-' + resp.mess_type );

                if (resp.mess_type == 'success') {
                    dtDownloadableMaterials.fnDraw();
                    closeFancyBox();
                } else {
                    form.find('button[type=submit]').removeClass('disabled');
                }
            }
        });
    }
</script>
