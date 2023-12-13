<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>
<?php $exist_event = !empty($event);?>
<form method="post" class="validateModal relative-b">
    <div class="wr-form-content w-700 h-500 pt-20">
        <table id="table" cellspacing="0" cellpadding="0" class="data table-striped w-100pr mt-15 vam-table">
            <tbody>
            <tr>
                <td>Name</td>
                <td>
                    <input class="w-100pr validate[required]" type="text" name="name" value="<?php echo $event['event_name']; ?>" placeholder="Name"/>
                </td>
            </tr>
            <tr>
                <td>Type</td>
                <td>
                    <select class="validate[required] w-100pr" name="type">
                        <option value="">Select Type</option>
                        <?php foreach ($types as $type) { ?>
                            <option value="<?php echo $type['id']; ?>" <?php echo selected($type['id'], $event['event_id_type']); ?>><?php echo $type['event_type_name']; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Image</td>
                <td>
                    <div class="juploader-b">
                        <span class="btn btn-success fileinput-button">
                            <i class="ep-icon ep-icon_plus"></i>
                            <span>Select file...</span>
                            <!-- The file input field used as target for the file upload widget -->
                            <input id="add_fileupload" type="file" name="files">
                        </span>
                        <span class="fileinput-loader-btn" style="display:none;">
                            <img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...
                        </span>
                        <div class="info-alert-b mt-10">
                            <i class="ep-icon ep-icon_info"></i>
                            <div> &bull; The maximum file size has to be 3MB.</div>
                            <div> &bull; Min width: 500px, Min height: 500px.</div>
                            <div> &bull; File available formats (jpg,jpeg,png,gif,bmp).</div>
                        </div>

                        <!-- The container for the uploaded files -->
                        <div class="fileupload-queue mt-30 clearfix">
                            <?php if ($event) { ?>
                                <?php
                                    $image_event_path = $image_path . '/' . $event['id_event'] . '/' . $event['event_image'];
                                    $image_event_exist = file_exists($image_event_path);
                                ?>
                                <div class="uploadify-queue-item item-middle">
                                    <div class="img-b">
                                        <img src="<?php echo __IMG_URL . ($image_event_exist ? $image_event_path : 'public/img/no_image/no-image-512x512.png'); ?>">
                                        <input type="hidden" name="main_image" value="<?php echo $image_event_path; ?>">
                                    </div>
                                    <?php if ($image_event_exist) {?>
                                        <div class="cancel">
                                            <a data-action="<?php echo __SITE_URL; ?>cr_events/ajax_event_delete_files?id=<?php echo $event['id_event']; ?>" data-file="<?php echo $event['event_image']; ?>" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog" href="#" title="Delete">
                                                <i class="ep-icon ep-icon_remove"></i>
                                            </a>
                                        </div>
                                    <?php }?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Start date</td>
                <td>
                    <input type="text" placeholder="Event start date" class="w-100pr date-picked" value="<?php echo isset($event['event_date_start']) ? date('m/d/Y', strtotime($event['event_date_start'])) : ''; ?>" name="date_start">
                </td>
            </tr>
            <tr>
                <td>End date</td>
                <td>
                    <input type="text" placeholder="Event end date" class="w-100pr date-picked" value="<?php echo isset($event['event_date_end']) ? date('m/d/Y', strtotime($event['event_date_end'])) : ''; ?>" name="date_end">
                </td>
            </tr>
            <tr>
                <td>Country</td>
                <td>
                    <select id="event-country" class="validate[required] w-100pr" name="country">
                        <option value="">Select Country</option>
                        <?php foreach ($countries as $country) { ?>
                            <option value="<?php echo $country['id_country']; ?>" <?php echo $exist_event ? selected($country['id_country'], $event['event_id_country']) : ''; ?>>
                                <?php echo $country['country']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>State</td>
                <td>
                    <select name="state" class="w-100pr validate[required]" id="event-state">
                        <option value=""><?php echo translate('form_placeholder_select2_state');?></option>
                        <?php if(!empty($states)) { ?>
                            <?php foreach ($states as $state) { ?>
                                <option value="<?php echo $state['id'];?>" <?php echo selected($event['event_id_state'], $state['id']);?>>
                                    <?php echo $state['state'];?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>City</td>
                <td>
                    <select name="city" class="w-100pr validate[required] event-select-city">
                        <option value="">Select city</option>
                        <?php if(!empty($city)) { ?>
                            <?php if (!empty($city)) { ?>
                                <option value="<?php echo $city['id'];?>" selected>
                                    <?php echo $city['city'];?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>ZIP</td>
                <td>
                    <input class="w-100pr validate[required,custom[zip_code],maxSize[20]]" maxlength="20" type="text" name="zip" value="<?php echo $event['event_zip']; ?>" placeholder="ZIP"/>
                </td>
            </tr>
            <tr>
                <td>Address</td>
                <td>
                    <input class="w-100pr validate[required]" type="text" name="address" value="<?php echo $event['event_address']; ?>" placeholder="Address"/>
                </td>
            </tr>
            <tr>
                <td>Short description</td>
                <td>
                    <textarea class="w-100pr h-100 validate[required]" name="short_description"><?php echo $event['event_short_description']; ?></textarea>
                </td>
            </tr>
            <tr>
                <td>Full description</td>
                <td>
                    <textarea class="event-text-block w-100pr h-100 validate[required]" name="description"><?php echo $event['event_description']; ?></textarea>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="wr-form-btns clearfix">
        <input type="hidden" name="upload_folder" value="<?php echo $upload_folder; ?>"/>
        <?php if ($event) { ?>
            <input type="hidden" name="id_event" value="<?php echo $event['id_event']; ?>"/>
        <?php } ?>
        <button class="pull-right btn btn-default" type="submit" name="edit_user"><span class="ep-icon ep-icon_ok"></span> Save</button>
    </div>
</form>

<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>

<style>
    #table td {
        position: relative;
    }
</style>

<script>
    $(function () {
        tinymce.init({
            selector: '.event-text-block',
            timestamp: '<?php echo $upload_folder;?>',
            menubar: false,
            statusbar: false,
            height: 260,
            media_poster: false,
            media_alt_source: false,
            relative_urls: false,
            plugins: [
                'autolink lists link media image'
            ],
            dialog_type: 'modal',
            style_formats: [
                {title: 'H3', block: 'h3'},
                {title: 'H4', block: 'h4'},
                {title: 'H5', block: 'h5'},
                {title: 'H6', block: 'h6'},
            ],
            toolbar: 'undo redo | styleselect | bold italic underline link | numlist bullist | insertfile | media ',//| alignleft aligncenter alignright alignjustify
			resize: false
        });



        $('.date-picked').datepicker();
        var $selectCity = $('.event-select-city');
        initSelectCity($selectCity);

        $('#event-state').on('change', function(){
            window.selectState = $(this).val();
            $selectCity.empty().trigger('change').prop('disabled', false);

            var select_text = '';
            if(window.selectState !== '' || window.selectState !== 0){
                select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_city'});
            } else{
                select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_state_first'});
                $selectCity.prop('disabled', true);
            }

            $selectCity.siblings('.select2').find('.select2-selection__placeholder').text(select_text);
        });

        $('#event-country').on('change', function(){
            selectCountry($(this), 'select#event-state');
            $selectCity.empty().trigger('change').prop('disabled', true);
        });


        $('#add_fileupload').fileupload({
            url: '<?php echo __SITE_URL; ?>cr_events/ajax_upload_image/<?php echo $upload_folder; ?>',
            dataType: 'json',
            maxFileSize: <?php echo $fileupload_max_file_size; ?>,
            beforeSend: function (event, files, index, xhr, handler, callBack) {
                $('.fileinput-loader-btn').fadeIn();
            },
            done: function (e, data) {
                if (data.result.mess_type === 'success') {
                    var itemID = +(new Date());
                    $('.fileupload-queue').html(templateFileUpload('img', 'item-middle', itemID));
                    $('#fileupload-item-'+itemID+' .img-b').append('<img src="' + data.result.path + '">');
                    $('#fileupload-item-'+itemID+' .img-b').append('<input type="hidden" name="main_image" value="' + data.result.path + '">');
                    $('#fileupload-item-'+itemID+' .cancel').append('<a data-action="<?php echo __SITE_URL; ?>cr_events/ajax_event_delete_temp_files/<?php echo $upload_folder;?>" data-file="' + data.result.name + '" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a>');
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
    });

    function modalFormCallBack($form, data_table) {
        $.ajax({
            type: 'POST',
            url: '<?php echo $action; ?>',
            data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
            },
            dataType: 'json',
            success: function (data) {
                systemMessages(data.message, 'message-' + data.mess_type);

                if (data.mess_type === 'success') {
                    closeFancyBox();
                    data_table && data_table.fnDraw();
                } else {
                    hideLoader($form);
                }
            }
        });
    }
</script>
