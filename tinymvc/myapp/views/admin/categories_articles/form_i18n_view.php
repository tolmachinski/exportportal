<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr" >
			<tr>
				<td>Language</td>
				<td>
                    <?php $translations_data = json_decode($article['translations_data'], true);?>
                    <?php if(empty($article_i18n)) { ?>
                        <select class="form-control" name="lang_article">
                            <?php $translations_data['en'] = 1; /* default language - english*/?>
                            <?php foreach($tlanguages as $lang){?>
                                <option value="<?php echo $lang['lang_iso2'];?>" <?php if(array_key_exists($lang['lang_iso2'], $translations_data)){echo 'disabled';}?>><?php echo $lang['lang_name'];?></option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>
                        <?php echo $translations_data[$article_i18n['lang_article']]["lang_name"];?>
                        <input type="hidden" name="lang_article" value="<?php echo $article_i18n['lang_article']; ?>"/>
                    <?php } ?>
				</td>
			</tr>
			<tr>
				<td>Text</td>
				<td>
					<textarea id="text_block" class="w-100pr h-200 validate[required]" name="text_article"><?php if(!empty($article_i18n)) echo $article_i18n['text'] ?></textarea>
				</td>
			</tr>
            <tr>
                <td>Photo</td>
                <td>
					<span class="btn btn-success fileinput-button">
						<i class="ep-icon ep-icon_plus"></i>
						<span>Select files...</span>
						<!-- The file input field used as target for the file upload widget -->
						<input id="add_fileupload_i18n" type="file" name="files[]" multiple>
					</span>
					<span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...</span>
					<div class="info-alert-b mt-10">
						<i class="ep-icon ep-icon_info"></i>
						<div> &bull; The maximum file size has to be 2MB.</div>
						<div> &bull; Min width: 235px, Min height: 100px.</div>
						<div> &bull; You cannot upload more than 1 photo(s).</div>
						<div> &bull; File available formats (jpg,jpeg,png,gif,bmp).</div>
					</div>

					<div class="fileupload-queue files mt-10">
						<?php if ((isset($article_i18n)) && (!empty($article_i18n['photo']))) { ?>
							<div class="uploadify-queue-item">
								<div class="img-b">
									<img src="<?php echo $article_i18n['photoLink'];?>" />
								</div>
								<div class="cancel">
                                    <a data-action="categories_articles/ajax_article_delete_db_photo_i18n" data-file="<?php echo $article_i18n['id_article_i18n'];?>" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog ep-icon ep-icon_remove txt-gray" href="#" title="Delete"></a>
								</div>
							</div>
						<?php } ?>
                    </div>
                </td>
            </tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($article_i18n)){?>
			<input type="hidden" name="id_article_i18n" value="<?php echo $article_i18n['id_article_i18n'];?>" />
        <?php } ?>
        <input type="hidden" name="id_article" value="<?php echo $article['id']?>"/>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script type="text/javascript" src="<?php echo __SITE_URL ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="text/javascript">
	function modalFormCallBack(form){
		var $form = $(form);
		var fdata = $form.serialize();
		var $wrform = $form.closest('.wr-modal-b');

		<?php if(!empty($article_i18n)){?>
			var url = '<?php echo __SITE_URL;?>categories_articles/ajax_articles_edit_i18n';
		<?php }else{?>
            var url = '<?php echo __SITE_URL;?>categories_articles/ajax_articles_add_i18n';
		<?php }?>

		$.ajax({
			type: 'POST',
			url: url,
			data: fdata,
			dataType: 'JSON',
			beforeSend: function(){
				showFormLoader($wrform, 'Sending article...');
				$form.find('button[type=submit]').addClass('disabled');
			},
			success: function(resp){
				hideFormLoader($wrform);
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					callbackManageArticles(resp);
					closeFancyBox();
				}else{
					$form.find('button[type=submit]').removeClass('disabled');
				}
			}
		});
	}

    function fileuploadRemoveFromJsOnly($thisBtn){
        $thisBtn.closest('.uploadify-queue-item').remove();
    }

    $(function(){
        var url = 'categories_articles/ajax_upload_temp_photo/';
        var uploadFileLimit = intval('<?php echo $fileupload_total; ?>');

        $('#add_fileupload_i18n').fileupload({
        url: url,
            dataType: 'json',
            maxNumberOfFiles: 1,
            maxFileSize: <?php echo $fileupload_max_file_size?>,
            beforeSend: function (event, data) {
                var upload_files = data.files.length;
                var total_files = $('.fileupload-queue').find('.uploadify-queue-item').length;

                if(upload_files + total_files > uploadFileLimit){
                    event.abort();
                    systemMessages( 'You can not upload more than '+ uploadFileLimit +' files for one report.', 'error' );
                } else{
                    $('.fileinput-loader-btn').fadeIn();
                }
            },
            done: function (e, data) {
                if(data.result.mess_type == 'success'){
                    var file = data.result.image;
                    var itemID = +(new Date());
                    $('.fileupload-queue').append(templateFileUpload('img','',itemID));
                    $('#fileupload-item-'+itemID+' .img-b').append('<img src="'+file.fullPath+'">');
                    $('#fileupload-item-'+itemID+' .img-b').append('<input type="hidden" name="image" value="'+file.path+'">');
                    $('#fileupload-item-'+itemID+' .cancel').append('<a data-file="'+file.name+'" data-callback="fileuploadRemoveFromJsOnly" data-message="Are you sure you want to delete this image?" class="confirm-dialog ep-icon ep-icon_remove txt-gray" href="#" title="Delete"></a>');
				}else{
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
            height: 250,
            plugins: ["autolink lists link textcolor"],
            dialog_type: "modal",
            toolbar: "bold italic underline forecolor backcolor link | numlist bullist ",
			resize: false
        });

	});

</script>
