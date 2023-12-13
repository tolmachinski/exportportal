<div class="js-modal-flex wr-modal-flex inputs-40">
    <form id="js-news-archive-form" class="modal-flex__form validateModal">
        <div class="modal-flex__content">
            <label class="input-label">Title</label>
            <input
                class="form-control validate[required,maxSize[200]]"
                name="title"
                value="<?php echo $news_archive['title'];?>"
                placeholder="Title"
            >

            <label class="input-label">Description</label>
            <textarea
                class="form-control validate[required,maxSize[500]] h-100"
                name="description"
                placeholder="Description"
            ><?php echo $news_archive['description'];?></textarea>

            <label class="input-label">Published on</label>
            <input
                type="text"
                data-title="Published on"
                name="published_on"
                value="<?php echo empty($news_archive['published_on']) ? date('m/d/Y') : getDateFormat($news_archive['published_on'], "Y-m-d H:i:s", 'm/d/Y');?>"
                readonly
            >

            <?php if (empty($news_archive)) { ?>
                <label class="input-label">Archive with template</label>
                <div class="juploader-b">
                    <span class="btn btn-success fileinput-button">
                        <span><?php echo translate("blog_dashboard_modal_field_photo_upload_button_text"); ?></span>
                        <input id="js-news-archive-files-field" type="file" accept=".zip" name="files[]">
                    </span>
                    <span class="fileinput-loader-btn fileinput-loader-img" style="display:none;">
                        <img class="image" src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> <?php echo translate("blog_dashboard_modal_field_photo_upload_placeholder"); ?>
                    </span>
                    <div id="js-uploaded-filename" class="pt-10">
                    </div>
                    <div class="info-alert-b mt-15">
                        <i class="ep-icon ep-icon_info"></i>
                        <div> &bull; File maximum size 2MB.</div>
                        <div> &bull; You cannot upload more than 1 archive.</div>
                        <div> &bull; Archive must contain index.html file.</div>
                        <div> &bull; The index.html file must contain base tag with the href {BASE_URL}.</div>
                        <div> &bull; File available formats (zip).</div>
                    </div>
                </div>
            <?php } ?>

            <input type="hidden" name="news_archive" value="<?php echo $news_archive['id_archive'] ?>"/>
            <input type="hidden" name="file_news_archive" value=""/>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Submit</button>
            </div>
        </div>
    </form>
</div>

<script>

<?php if(!empty($news_archive)) {?>
    var link = 'newsletter/ajax_ep_news_operations/edit_news_archive';
<?php }else{?>
    var link = 'newsletter/ajax_ep_news_operations/add_news_archive';
<?php }?>

function modalFormCallBack(form, data_table){
    var $form = $(form);
    var $wrform = $form.closest('.js-modal-flex');
    var fdata = $form.serialize();

    $.ajax({
        type: 'POST',
        url: link,
        data: fdata,
        dataType: 'JSON',
        beforeSend: function(){
            showFormLoader($wrform);
            $form.find('button[type=submit]').addClass('disabled');
        },
        success: function(resp){
            hideFormLoader($wrform);
            systemMessages( resp.message, 'message-' + resp.mess_type );

            if(resp.mess_type == 'success'){
                closeFancyBox();

                if(data_table != undefined)
                    data_table.fnDraw();
            }else{
                $form.find('button[type=submit]').removeClass('disabled');
            }
        }
    });
}

$(function () {

    $('input[name="published_on"]').datepicker();

    <?php if (empty($news_archive)) { ?>
        var fileUploader = $('#js-news-archive-files-field');
        var fileUploadTimestamp = "<?php echo $upload_folder;?>";
        var loaderBtn = $('#js-news-archive-form .fileinput-loader-img');

        fileUploader.fileupload({
            url: __site_url + 'newsletter/ajax_upload_news_archive/' + fileUploadTimestamp,
            dataType: 'json',
            beforeSend: function (e, data) {
                loaderBtn.fadeIn();
            },
            done: function (e, data) {
                loaderBtn.fadeOut();
                if (data.result.mess_type == 'error') {
                    return systemMessages(data.result.message, 'error');
                }
                $('#js-news-archive-form input[name="file_news_archive"]').val(data.result.files[0].path);
                $('#js-uploaded-filename').text('Uploaded file: ' + data.files[0].name);
            },
            maxFileSize: <?php echo $max_document_file_size ?>,
            acceptFileTypes: /(zip)$/i,
            maxNumberOfFiles: 1,
            processalways: function(e,data) {
                if (data.files.error) {
                    systemMessages(data.files[0].error, 'error');
                }
            },
        });
    <?php } ?>
});
</script>
