<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-900 mh-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td>Title</td>
					<td>
						<input class="w-100pr validate[required,maxSize[255]]" type="text" name="title" value="<?php echo $news_info['title']?>"/>
					</td>
				</tr>
				<tr>
					<td>Short description</td>
					<td>
						<textarea class="w-100pr h-100 validate[required,maxSize[500]] js-textcounter-short-description" data-max="500" name="short_description" ><?php echo $news_info['short_description']?></textarea>
					</td>
				</tr>
				<tr>
					<td class="bg-white" colspan="2">
						<ul class="nav-b nav nav-tabs clearfix pt-10" role="tablist">
							<li role="presentation" class="active"><a href="#js-ttrade-news-tab-content" aria-controls="title" role="tab" data-toggle="tab">Content</a></li>
							<li role="presentation"><a class="preview-content-link" href="#js-ttrade-news-tab-preview-content" aria-controls="title" role="tab" data-toggle="tab">Preview content</a></li>
						</ul>

						<div class="tab-content nav-info clearfix">
							<div role="tabpanel" class="tab-pane active" id="js-ttrade-news-tab-content">
								<textarea id="js-news-text-block" class="validate[required]" name="content" ><?php echo $news_info['content']?></textarea>
							</div>
							<div role="tabpanel" class="tab-pane" id="js-ttrade-news-tab-preview-content">
								<div
									id="js-preview-news-text-block"
									class="ep-tinymce-text"></div>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td>Main photo</td>
					<td>
						<?php views()->display('new/user/photo_cropper2_view'); ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<label class="lh-30 vam pull-left mr-10">
			<input class="vam" type="checkbox" name="visible" <?php if(isset($news_info)){ echo checked($news_info['is_visible'], 1); }else{?>checked="checked"<?php }?>/>
			Visible
		</label>
		<?php if(!empty($news_info)){?>
			<input type="hidden" name="post" value="<?php echo $news_info['id_trade_news']?>"/>
		<?php }?>
		<input type="hidden" name="upload_folder" value="<?php echo $upload_folder;?>"/>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script type="text/javascript" src="<?php echo 'public/plug_admin/tinymce-4-3-10/tinymce.min.js'; ?>"></script>
<script type="text/javascript">
	$(document).ready(function(){

		$('.js-textcounter-short-description').textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug: 'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug: 'textcounter', text: 'count_down_text_after'})
		});

		tinymce.init({
			selector:'#js-news-text-block',
			timestamp: '<?php echo $upload_folder;?>',
			menubar: false,
			statusbar : false,
			height : 260,
			media_poster: false,
			media_alt_source: false,
			relative_urls: false,
			plugins: [
				"autolink lists link media image"
			],
			dialog_type : "modal",
			toolbar: "undo redo | bold italic underline link | alignleft aligncenter alignright alignjustify | numlist bullist | insertfile | media | image",
            file_picker_types: 'image',
            file_picker_upload_url: '/trade_news/upload_photo/<?php echo $upload_folder;?>',
            file_picker_callback: function(callback, value, meta){
                if (meta.filetype !== 'image') {
                    return;
                }

                var url = location.origin + (this.settings.file_picker_upload_url || '');
                var body = $(body);
                var input = body.find('input#news-inline-image-upload');
                if(input.length === 0) {
                    input = $('<input>');
                    input.css({display: 'none'});
                    input.attr('id', 'news-inline-image-upload');
                    input.attr('type', 'file');
                    input.attr('accept', 'image/*');
                    body.append(input);
                }

                input.on('change', function() {
                    var file = this.files[0];
                    var reader = new FileReader();
                    reader.onload = function () {
                        // Note: Now we need to register the blob in TinyMCEs image blob
                        // registry. In the next release this part hopefully won't be
                        // necessary, as we are looking to handle it internally.
                        var id = 'blobid' + (new Date()).getTime();
                        var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                        var base64 = reader.result.split(',')[1];
                        var blobInfo = blobCache.create(id, file, base64);
                        blobCache.add(blobInfo);

                        // Note: Now we send blob of the image to the server to
                        // handle file upload. On finish we will call tinyMCE callback with
                        // uploaded file URL
                        var formData = new FormData();
                        formData.append("userfile", blobInfo.blob());
                        $.ajax({
                            url: url,
                            type: 'POST',
                            dataType: "JSON",
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response){
                                if(response.mess_type == 'success'){
                                    // call the callback and populate the Title field with the file name
                                    callback(location.origin + response.file, { title: file.name });
                                }else{
                                    systemMessages( response.message, 'message-' + response.mess_type );
                                }
                            }
                        });
                    };
                    reader.readAsDataURL(file);
                });

                input.click();
			},
			resize: false
		});

		$('body').on('click', '.preview-content-link', function(){
			var content =  tinymce.get('js-news-text-block').getContent();
			var $wrform = $(this).closest('.js-modal-flex');
			var timestamp =  $('.modal-flex__content').find('input[name=timestamp]').val();

			if(content.length){
				$.ajax({
					type: 'POST',
					url: '<?php echo __SITE_URL?>trade_news/ajax_news_operation/preview_content',
					data: { content : content, timestamp: timestamp },
					beforeSend: function(){ showFormLoader($wrform); },
					dataType: 'json',
					success: function(resp){
						hideFormLoader($wrform);

						if(resp.mess_type == 'success'){
							$('#js-preview-news-text-block').html(resp.content);
						}
					}
				});
			}else{
				$('#js-preview-news-text-block').html('<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> Content is empty.</div>');
			}
		});
	});

	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>trade_news/ajax_news_operation/<?php echo (isset($news_info) ? 'edit': 'add'  )?>_news',
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
