<div class="wr-modal-b">
    <form class="modal-b__form validateModal" data-callback="upload_signed_doc">
        <div class="modal-b__content w-600 mh-700 mt-10">
            <div class="row">
                <div class="col-xs-12 initial-b_i">
                    <label class="modal-b__label">Upload the document (allowed file type: <?php echo $format_read;?>)</label>
                    <div class="row">
                        <div class="col-xs-8">
                            <label class="modal-b__label">Remove all the entries that have been added from the file:</label>
                        </div>
                        <div class="col-xs-4 input-group">
                            <label class="input-group-addon">
                                <input class="" type="radio" name="delete_record" value="1">
                                <span class="input-group__desc">Yes</span>
                            </label>
                            <label class="input-group-addon">
                                <input class="" type="radio" name="delete_record" value="0" checked>
                                <span class="input-group__desc">No</span>
                            </label>
                        </div>
                    </div>

                    <div class="required-field__ico"></div>

                    <span class="btn btn-success fileinput-button">
                        <i class="ep-icon ep-icon_plus"></i>
                        <span>Select document...</span>
                        <input id="upload_doc" type="file" name="file_excell" multiple="">
                    </span>
                    <span class="fileinput-loader-btn" style="display:none;">
                        <img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...
                    </span>

                    <!-- The container for the uploaded files -->
                    <div class="fileupload-queue files mt-10"></div>
                </div>
            </div>
        </div>
        <div class="modal-b__btns clearfix">
            <button class="btn btn-primary pull-right" type="submit"><i class="ep-icon ep-icon_ok"></i> Confirm</button>
        </div>
    </form>
</div>
<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>
<script>
    function upload_signed_doc(form, data_table){
        var $form = $(form);
        var $wrform = $form.closest('.wr-modal-b');

        $.ajax({
            url: '<?php echo __SITE_URL . $current_contoller?>/ajax_library_operation/file_parse',
            type: 'POST',
            data:  $form.serialize(),
            dataType: 'json',
            beforeSend: function(){
                showFormLoader($wrform);
            },
            success: function(resp){
                systemMessages(resp.message, 'message-' + resp.mess_type );
                hideFormLoader($wrform);
                if(resp.mess_type == 'success'){

                    closeFancyBox();
                    if(data_table != undefined)
                        data_table.fnDraw(false);

                    if(resp.src){
                        $('.download_last_excel').show();
                        $('.download_last_excel').data('file_name', resp.src);
                    }

                    upload_signed_doc_callback();
                }
            }
        });
    }

    $(document).ready(function(){
        $('#upload_doc').fileupload({
            url: '<?php echo __SITE_URL . $current_contoller;?>/ajax_library_operation/save_from_file',
            dataType: 'json',
            beforeSend: function () {
                $('.fileinput-loader-btn').fadeIn();
            },
            done: function (e, data) {
                systemMessages( data.result.message, 'message-' + data.result.mess_type );
                if(data.result.mess_type == 'success'){
                    var itemID = +(new Date());
                    $('.modal-b__btns').append('<input type="hidden" name="file_excel_name" value="'+ data.result.file_excel +'">');
                    $('.fileupload-queue').append(templateFileUpload('files', 'item-middle', itemID, data.result.file_type));
                    $('#fileupload-item-'+itemID+' .cancel').append('<a data-action="<?php echo __SITE_URL. $current_contoller;?>/ajax_library_operation/delete_uploaded" data-file="'+data.result.file_excel+'" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this file?" class="confirm-dialog" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a>');
                }

                $('.fileinput-loader-btn').fadeOut();
            },
            progressall: function (e, data) {
            }
        }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');
    });
</script>
