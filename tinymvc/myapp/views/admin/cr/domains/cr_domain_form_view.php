<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700 h-400">
		<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr">
			<tr>
				<td class="w-140 vam">Country</td>
				<td>
                    <?php if(!empty($cr_domain)){?>
                        <?php echo $cr_domain['country'];?>
                    <?php } else{?>
                        <select name="country">
                            <option value="">Select country</option>
                            <?php foreach($countries as $country){?>
                                <option value="<?php echo $country['id'];?>"><?php echo $country['country'];?></option>
                            <?php }?>
                        </select>
                    <?php }?>
				</td>
			</tr>
            <tr>
                <td class="w-140">Video</td>
                <td>
                    <input type="text" name="video" class="w-100pr validate[maxSize[255]]" value="<?php if(!empty($video_link))  echo $video_link;?>" id="form-validation-field-0">
                </td>
            </tr>
            <tr>
                <td class="w-140">Short description</td>
                <td><textarea name="short_description" class="w-100pr validate[maxSize[500]]" cols="60" rows="5"><?php if(isset($cr_domain['short_description'])) echo $cr_domain['short_description'];?></textarea></td>
            </tr>
			<tr>
				<td class="w-140">Header image</td>
				<td>
					<span class="btn btn-success fileinput-button">
						<i class="ep-icon ep-icon_plus"></i>
						<span>Select files...</span>
						<!-- The file input field used as target for the file upload widget -->
						<input id="edit_fileupload" type="file" name="files[]">
					</span>
					<span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...</span>
					<div class="info-alert-b mt-10">
						<i class="ep-icon ep-icon_info"></i>
						<div> &bull; The maximum file size has to be 2MB.</div>
						<div> &bull; You cannot upload more than 1 photo(s).</div>
						<div> &bull; File available formats (jpg,jpeg,png,gif,bmp).</div>
					</div>

					<!-- The container for the uploaded files -->
					<div class="fileupload-queue files mt-10">
						<?php if(!empty($cr_domain) && !empty($cr_domain['domain_photo'])){?>
							<div class="uploadify-queue-item m-0">
								<div class="img-b h-auto">
									<img src="<?php echo __IMG_URL.'public/img/country_representative/'.$cr_domain['domain_photo'];?>" class="w-100pr"/>
									<input type="hidden" name="domain_photo" value="<?php echo $cr_domain['domain_photo']?>">
								</div>
								<div class="cancel"><a data-action="<?php echo __SITE_URL;?>cr_domains/ajax_operations/delete_cr_image/<?php echo $cr_domain['id_domain'];?>" data-file="<?php echo $cr_domain['domain_photo'];?>" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a></div>
							</div>
						<?php }?>
					</div>
				</td>
			</tr>
		</table>
	</div>

	<div class="wr-form-btns clearfix">
		<?php if(!empty($cr_domain)){?>
			<input type="hidden" name="id_domain" value="<?php echo $cr_domain['id_domain']?>">
		<?php }?>

		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript">
    $(document).ready(function(){
        $('#edit_fileupload').fileupload({
            url: '<?php echo __SITE_URL;?>cr_domains/ajax_operations/upload_cr_image<?php if(!empty($cr_domain)){echo '/'.$cr_domain['id_domain'];}?>',
            dataType: 'json',
            maxFileSize: <?php echo $fileupload_max_file_size;?>,
            beforeSend: function () {
                $('.fileinput-loader-btn').fadeIn();
            },
            done: function (e, data) {
                if(data.result.mess_type == 'success'){
                    var itemID = uniqid();
                    var file = data.result.file;
                    $('.fileupload-queue').append(templateFileUpload('img','m-0',itemID));
                    $('#fileupload-item-'+itemID+' .img-b').addClass('h-auto');
                    $('#fileupload-item-'+itemID+' .img-b').append('<img src="'+file.path+'/'+file.name+'" class="w-100pr">');
                    $('#fileupload-item-'+itemID+' .img-b').append('<input type="hidden" name="domain_photo" value="'+file.name+'">');
                    $('#fileupload-item-'+itemID+' .cancel').append('<a data-action="<?php echo __SITE_URL;?>cr_domains/ajax_operations/delete_cr_image" data-file="'+file.name+'" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog" href="#" title="Delete"><i class="ep-icon ep-icon_remove"></i></a>');
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
    });

	function modalFormCallBack(form){
		var $form = $(form);
		var fdata = $form.serialize();
		var $wrform = $form.closest('.wr-modal-b');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL;?>cr_domains/ajax_operations/<?php if(!empty($cr_domain)){?>edit<?php } else{?>add<?php }?>_cr_domain',
			data: fdata,
			dataType: 'JSON',
			beforeSend: function(){
				showFormLoader($wrform, 'Sending right...');
				$form.find('button[type=submit]').addClass('disabled');
			},
			success: function(resp){
				hideFormLoader($wrform);
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					closeFancyBox();
                    manage_cr_domains_callback(resp);
				}else{
					$form.find('button[type=submit]').removeClass('disabled');
				}
			}
		});
	}
</script>
