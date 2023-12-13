<?php $url_sufix = !empty($hiring['id_vacancy']) ?  '/'.$hiring['id_vacancy'] : "";?>
<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700 h-430">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr" >
			<tr>
				<td>Country</td>
				<td>
					<select class="w-100pr validate[required]" name="country">
                        <option value="all" <?php echo selected(0, $hiring['id_country']); ?>>Worldwide</option>
                        <?php foreach ($countries as $country) { ?>
                            <option value="<?php echo $country['id']?>" <?php echo selected($country['id'], $hiring['id_country']); ?>><?php echo $country['country']; ?></option>
                        <?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Office</td>
				<td>
					<select class="w-100pr" name="office">
                        <?php if (isset($offices)) { ?>
                        <option selected="selected" value="">Choose</option>
                            <?php foreach ($offices as $office) { ?>
                                <option value="<?php echo $office['id_office']?>" <?php echo selected($office['id_office'], $hiring['id_office']); ?>><?php echo $office['name_office']; ?></option>
                            <?php } ?>
                        <?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Post</td>
				<td><input type="text" name="post" class="w-100pr validate[required,maxSize[255]]" value="<?php echo $hiring['post_vacancy']; ?>" /></td>
			</tr>
			<tr>
				<td>Description</td>
				<td>
					<textarea name="description" id="text_block" class="w-100pr h-100 validate[required]"><?php echo $hiring['description_vacancy']; ?></textarea>
				</td>
			</tr>
            <tr>
                <td>Image</td>
                <td>
					<span class="btn btn-success fileinput-button">
						<i class="ep-icon ep-icon_plus"></i>
						<span>Select files...</span>
                        <!-- The file input field used as target for the file upload widget -->
						<input id="add_fileupload" type="file" name="files[]" multiple>
					</span>
                    <span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL . 'public/img/loader.gif';?>" alt="loader"> Uploading...</span>
                    <div class="info-alert-b mt-10">
                        <i class="ep-icon ep-icon_info"></i>
                        <div> &bull; The maximum file size has to be 2MB.</div>
                        <div> &bull; Min width: 350px, Min height: 250px.</div>
                        <div> &bull; You cannot upload more than 1 photo(s).</div>
                        <div> &bull; File available formats (jpg,jpeg,png,gif,bmp).</div>
                    </div>

                    <div class="fileupload-queue files mt-10">
                        <?php if (isset($hiring) && !empty($hiring['photo'])) { ?>
                            <div class="uploadify-queue-item item-large">
                                <div class="img-b">
                                    <img src="<?php echo $hiringUrl; ?>" />
                                </div>
                                <div class="cancel">
                                    <a data-action="hiring/ajax_vacancy_delete_db_photo" data-file="<?php echo $hiring['id_vacancy'];?>" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog ep-icon ep-icon_remove txt-gray" href="#" title="Delete"></a>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Link</td>
                <td><input type="text" name="link" class="w-100pr validate[required,custom[url]]" value="<?php echo $hiring['link_vacancy']; ?>" /></td>
            </tr>
			<tr>
				<td>Visible</td>
				<td>
					<input class="validate[required,min[0],max[1],custom[integer]]" type="radio" name="visible" <?php echo checked($hiring['visible_vacancy'], '1'); ?> value="1"> Yes
					<input class="validate[required,min[0],max[1],custom[integer]]" type="radio" name="visible" <?php echo checked($hiring['visible_vacancy'], '0'); ?> value="0"> No
				</td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if (isset($hiring)) { ?>
			<input type="hidden" class="validate[required,min[1],custom[integer]]" name="id_vacancy" value="<?php echo $hiring['id_vacancy']; ?>" />
		<?php } ?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="text/javascript">
    $(function(){
        var url = 'hiring/ajax_vacancy_upload_photo<?php echo $url_sufix;?>';
        $(function(){
            $('#add_fileupload').fileupload({
                url: url,
                dataType: 'json',
                maxFileSize: <?php echo $fileupload_max_file_size?>,
                beforeSend: function () {
                    $('.fileinput-loader-btn').fadeIn();
                },
                done: function (e, data) {
                    if(data.result.mess_type == 'success'){
                        //dump(data.result.files);
                        $.each(data.result.files, function (index, file) {
                            var itemID = +(new Date());
                            $('.fileupload-queue').append(templateFileUpload('img','item-large',itemID));
                            $('#fileupload-item-'+itemID+' .img-b').append('<img src="'+file.path+'">');
                            $('#fileupload-item-'+itemID+' .img-b').append('<input type="hidden" name="image" value="'+file.path+'">');
                            $('#fileupload-item-'+itemID+' .cancel').append('<a data-action="hiring/ajax_vacancy_delete_files<?php echo $url_sufix;?>" data-file="'+file.name+'" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog ep-icon ep-icon_remove txt-gray" href="#" title="Delete"></a>');
                        });
                    } else{
                        systemMessages( data.result.message, 'message-' + data.result.mess_type );
                    }
                    $('.fileinput-loader-btn').fadeOut();
                },
                processalways: function(e,data){
                    if (data.files.error){
                        systemMessages( data.files[0].error, 'message-error' );
                    }
                }
            }).prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? undefined : 'disabled');

            tinymce.init({
                selector: '#text_block',
                menubar: false,
                statusbar: false,
                dialog_type: "modal",
                plugins: ['autolink lists link code'],
                style_formats: [
                    {title: 'H3', block: 'h3'},
                    {title: 'H4', block: 'h4'},
                    {title: 'H5', block: 'h5'},
                    {title: 'H6', block: 'h6'},
                ],
                toolbar: 'styleselect | bold italic underline | link | numlist bullist | removeformat | code',
			    resize: false
            });
        });
    });

    function modalFormCallBack($form){
        var url = 'hiring/ajax_operations/<?php echo (!empty($hiring))?'update':'create';?>';
        $.ajax({
            type: 'POST',
            url: url,
            data: $form.serialize(),
            dataType: 'JSON',
            beforeSend: function(){
                $form.find('button[type=submit]').addClass('disabled');
            },
            success: function(resp){
                systemMessages(resp.message, 'message-' + resp.mess_type);

                if (resp.mess_type === 'success') {
                    callbackManageVacancies(resp);
                    closeFancyBox();
                } else {
                    $form.find('button[type=submit]').removeClass('disabled');
                }
            }
        });
    }
</script>
