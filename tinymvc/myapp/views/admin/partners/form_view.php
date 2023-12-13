<link rel="stylesheet" type="text/css" media="screen" href="<?php echo __FILES_URL;?>public/plug_admin/cropper-4.0.0/cropper.min.css"/>
<script type="text/javascript" src="<?php echo __FILES_URL;?>public/plug_admin/cropper-4.0.0/cropper.min.js"></script>

<form id="form-partners" class="validateModal relative-b"  method="post" enctype="multipart/form-data">
	<div class="wr-form-content w-700 h-440">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table" >
			<tr>
				<td>Country</td>
				<td>
					<select class="w-100pr validate[required]" name="country" >
					<?php if(!isset($partner)){?> <option selected="selected" value="">Please choose a country</option> <?php }?>
					<?php foreach($country as $country_item) {?>
						<option value="<?php echo $country_item['id']?>" <?php if(isset($partner)) echo selected($partner['id_country'],$country_item['id'])?>><?php echo $country_item['country'] ?></option>
					<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Name</td>
				<td><input type="text" name="name" class="w-100pr validate[required,custom[onlyLetterSp],maxSize[255]]" value="<?php if(isset($partner)) echo $partner['name_partner']?>" /></td>
			</tr>
			<tr class="<?php if(!isset($partner)){ ?> manually-hide <?php } ?>">
				<td>Website</td>
				<td><input type="text" name="link" class="w-100pr validate[required,custom[url]]" value="<?php if(isset($partner)) echo $partner['website_partner']?>" /></td>
			</tr>
			<tr>
				<td>Description</td>
				<td>
					<textarea name="description" class="w-100pr h-100 validate[required]"><?php if(isset($partner)) echo $partner['description_partner']?></textarea>
				</td>
			</tr>
			<tr>
				<td>Logo</td>
				<td>
					<span class="btn btn-success fileinput-button">
						<i class="ep-icon ep-icon_plus"></i>
						<span>Select files...</span>
						<!-- The file input field used as target for the file upload widget -->
						<input id="add_fileupload" type="file" name="files[]" multiple>
					</span>
					<span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...</span>
					<div class="info-alert-b mt-10">
						<i class="ep-icon ep-icon_info"></i>
						<div> &bull; The maximum file size has to be 2MB.</div>
						<div> &bull; Recomended width: 170px, Recomended height: 170px.</div>
						<div> &bull; You cannot upload more than 1 photo(s).</div>
						<div> &bull; File available formats (jpg,jpeg,png,gif,bmp).</div>
					</div>

					<!-- The container for the uploaded files -->
					<div class="fileupload-queue files mt-10">
						<?php if(!empty($partner['img_partner'])){?>
							<div class="uploadify-queue-item item-large">
								<div class="img-b">
									<img src="<?php echo $partner['imageUrl'] ?>" alt="img" />
								</div>
								<div class="cancel"><a data-action="partners/ajax_partners_delete_db_photo/" data-file="<?php echo $partner['id_partner'];?>" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog ep-icon ep-icon_remove txt-grey" href="#" title="Delete"></a></div>
							</div>
						<?php }?>
					</div>
				</td>
			</tr>
			<tr>
				<td>Visible</td>
				<td><input type="checkbox" name="visible" <?php if(isset($partner)) echo checked($partner['visible_partner'], 1)?>/></td>
			</tr>
			<tr>
				<td>On home</td>
				<td><input type="checkbox" name="on_home" <?php if(isset($partner)) echo checked($partner['on_home'], 1)?>/></td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($partner)){?>
			<input type="hidden" name="id" value="<?php if(isset($partner)) echo $partner['id_partner']?>" />
		<?php }?>
		<input type="hidden" name="upload_folder" value="<?php echo $upload_folder ?>" />
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
	var cropper;
    function modalFormCallBack(form, data_table){
        if (cropper === undefined) {
            var $form = form;
            $.ajax({
                type: 'POST',
                url: "partners/ajax_partners_operation/<?php echo (isset($partner) ? 'update' : 'create')?>_partner",
                data: $form.serialize(),
                beforeSend: function () {
                    showLoader($(form));
                },
                dataType: 'json',
                success: function(data){
                    systemMessages( data.message, 'message-' + data.mess_type );

                    if(data.mess_type == 'success'){
                        closeFancyBox();
                        if(data_table != undefined)
                            data_table.fnDraw();
                    }else{
                        hideLoader($form);
                    }
                }
            });
        }
        else {
            cropper.cropper('getCroppedCanvas', {
                width: 170,
                height: 170,
            }).toBlob((blob) => {
                var $form = new FormData(form[0]);
                $form.append('croppedImage', blob, 'croppedImage.png');

                $.ajax({
                    type: 'POST',
                    url: "partners/ajax_partners_operation/<?php echo (isset($partner) ? 'update' : 'create')?>_partner",
                    data: $form,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        // showLoader($(form));
                    },
                    dataType: 'json',
                    success: function(data){
                        systemMessages( data.message, 'message-' + data.mess_type );

                        if(data.mess_type == 'success'){
                            closeFancyBox();
                            if(data_table != undefined)
                                data_table.fnDraw();
                        }else{
                            hideLoader($form);
                        }
                    }
                });
            });
        }
    }

    function fileuploadRemovePartnerImg($thisBtn){
        $thisBtn.closest('.uploadify-queue-item').remove();
        $('#progress').fadeOut().html('');
        $('#add_fileupload').val("");
    }

    var url = 'partners/ajax_partners_upload_photo/<?php echo $upload_folder;?>/<?php echo $partner['id_partner'];?>'
    $('#add_fileupload').on('click', function () {
        $(this).val("");
    });

    $('#add_fileupload').on('change', function () {
        if ($('#form-partners .fileupload-queue .img-b').length) {
            return systemMessages( 'Logo already exist', 'message-error');
        }
        var input = this;
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                var itemID = +(new Date());

                $('.fileupload-queue').append(templateFileUpload('img','item-large',itemID));
                $('#fileupload-item-'+itemID+' .img-b').append('<img id="cropped-image-'+itemID+'" src="'+e.target.result+'" alt="img">');
                $('#fileupload-item-'+itemID+' .cancel').append('<a data-callback="fileuploadRemovePartnerImg" data-message="Are you sure you want to delete this image?" class="ep-icon ep-icon_remove txt-grey confirm-dialog" href="#" title="Delete"></a>');

                cropper = $('#cropped-image-'+itemID).cropper({
                    viewMode: 3,
                    aspectRatio: 1 / 1,
                    cropBoxResizable: false,
                    crop: function(event) {
                        // console.log(event.detail.x);
                        // console.log(event.detail.y);
                        // console.log(event.detail.width);
                        // console.log(event.detail.height);
                        // console.log(event.detail.rotate);
                        // console.log(event.detail.scaleX);
                        // console.log(event.detail.scaleY);
                    }
                });
            }
            reader.fileName = input.files[0].name;
            reader.readAsDataURL(input.files[0]);
        }
    });


    /* $('#add_fileupload').fileupload({
        url: url,
        dataType: 'json',
        maxFileSize: <?php echo $fileupload_max_file_size?>,
        beforeSend: function () {
            $('.fileinput-loader-btn').fadeIn();
        },
        done: function (e, data) {
            if(data.result.mess_type == 'success'){
                $.each(data.result.files, function (index, file) {
                    var itemID = +(new Date());
                    $('.fileupload-queue').append(templateFileUpload('img','item-large',itemID));
                    $('#fileupload-item-'+itemID+' .img-b').append('<img src="'+file.path+'" alt="img">');
                    $('#fileupload-item-'+itemID+' .img-b').append('<input type="hidden" name="image" value="'+file.path+'">');
                    $('#fileupload-item-'+itemID+' .cancel').append('<a data-action="partners/ajax_partners_delete_photo/<?php echo $upload_folder;?>" data-file="'+file.name+'" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="ep-icon ep-icon_remove txt-grey confirm-dialog" href="#" title="Delete"></a>');
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
        .parent().addClass($.support.fileInput ? undefined : 'disabled'); */
</script>
