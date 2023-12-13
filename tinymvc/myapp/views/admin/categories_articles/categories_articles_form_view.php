<form method="post" class="relative-b validateModal">
    <div class="wr-form-content w-700 h-400">
    <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr mt-15 vam-table">
        <tbody>
            <tr>
                <td class="w-100">Visible</td>
                <td>
                    <input type="checkbox" name="visible" value="1" <?php if(isset($cat_art['visible']) && $cat_art['visible'] == 1) { ?>checked="checked"<?php }?>/>
                </td>
            </tr>
            <tr>
                <td class="w-100">Category</td>
                <td>
                    <?php if(isset($categories)){?>
                    <div class="w-100pr cats-level" data-level="1">
                        <select class="w-95pr validate[required] category-name pull-left" name="category[1]">
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat) { ?>
                                <option value="<?php echo $cat['id'] ?>" <?php echo selected($cat_art['id_category'], $cat['id']); ?>><?php echo $cat['cat_name'] ?></option>
                            <?php } ?>
                        </select>
                        <span class="ep-icon ep-icon_plus txt-blue view-next-subcat w-3pr pull-right mt-7"></span>
                    </div>
                    <?php }elseif(isset($cat_art['cat_name'])){
                        echo $cat_art['cat_name'];
                    }?>
                </td>
            </tr>
            <?php if(isset($breadcrumbs_str)){?>
            <tr>
                <td class="w-100">Category tree</td>
                <td>
                   <?php echo $breadcrumbs_str;?>
                </td>
            </tr>
            <?php }?>
            <tr>
                <td>Text</td>
                <td>
                    <textarea class="w-100pr h-150 validate[required] article-text-block" name="text"><?php echo ((isset($cat_art['text']) ? $cat_art['text'] : '')); ?></textarea>
                </td>
            </tr>
            <tr>
                <td>Photo</td>
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
						<div> &bull; Min width: 235px, Min height: 100px.</div>
						<div> &bull; You cannot upload more than 1 photo(s).</div>
						<div> &bull; File available formats (jpg,jpeg,png,gif,bmp).</div>
					</div>

					<div class="fileupload-queue files mt-10">
						<?php if ((isset($cat_art)) && (!empty($cat_art['photo']))) { ?>
							<div class="uploadify-queue-item">
								<div class="img-b">
									<img src="<?php echo $cat_art['photoLink'];?>" />
								</div>
								<div class="cancel">
									<a data-action="categories_articles/ajax_article_delete_db_photo" data-file="<?php echo $cat_art['id'];?>" data-callback="fileuploadRemove" data-message="Are you sure you want to delete this image?" class="confirm-dialog ep-icon ep-icon_remove txt-gray" href="#" title="Delete"></a>
								</div>
							</div>
						<?php } ?>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    </div>
    <div class="wr-form-btns clearfix">
        <?php if(isset($cat_art)){?><input type="hidden" name="id" value="<?php echo $cat_art['id']; ?>"/> <?php }?>
        <button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
    </div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="text/javascript">
	var url = 'categories_articles/ajax_upload_temp_photo/';
    cur_lvl_cats = 1;
    $(function(){
        var uploadFileLimit = intval('<?php echo $fileupload_total; ?>');
		$('#add_fileupload').fileupload({
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
            selector: '.article-text-block',
            menubar: false,
            statusbar: false,
            height: 250,
            plugins: ["autolink lists link textcolor"],
            dialog_type: "modal",
            toolbar: "bold italic underline forecolor backcolor link | numlist bullist ",
			resize: false
        });
	});

    function fileuploadRemoveFromJsOnly($thisBtn){
        $thisBtn.closest('.uploadify-queue-item').remove();
    }

    function modalFormCallBack(form, data_table){
        var $form = $(form);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>categories_articles/ajax_categories_articles_operation/<?php echo (isset($cat_art) ? 'edit': 'save'  )?>_category_article',
            data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
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
</script>
