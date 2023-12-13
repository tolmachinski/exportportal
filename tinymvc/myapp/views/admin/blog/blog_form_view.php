<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-900 mh-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
        <tbody>
			<tr>
				<td class="w-150">Country</td>
				<td>
                    <div class="form-group">
                        <select class="w-100pr validate[required]" name="country" >
                            <?php $id_country = empty($blog_info) ? 0 : (int) $blog_info['id_country']; ?>
                            <option value="0" <?php echo selected(0, $id_country); ?>>Any</option>
                            <?php echo getCountrySelectOptions($blog_countries, $id_country, array('include_default_option' => false));?>
                        </select>
                    </div>
				</td>
			</tr>
			<tr>
				<td class="w-150">Blog language</td>
				<td>
                    <div class="form-group">
                        <?php $selected_language = (empty($blog_info))?__SITE_LANG:$blog_info['lang'];?>
                        <span class="required-field"></span>
                        <select class="validate[required]" name="blog_lang" >
                            <?php foreach($tlanguages as $tlanguage){?>
                                <option value="<?php echo $tlanguage['lang_iso2'];?>" <?php echo selected($tlanguage['lang_iso2'], $selected_language); ?>><?php echo $tlanguage['lang_name'];?></option>
                            <?php }?>
                        </select>
                    </div>
				</td>
			</tr>
			<tr>
				<td class="w-150">Category</td>
				<td>
                    <div class="form-group">
                        <select class="w-100pr validate[required]" name="category" >
                            <?php foreach($blog_categories as $category){?>
                            <option value="<?php echo $category['id_category'];?>" <?php echo selected($category['id_category'], $blog_info['id_category']); ?>><?php echo $category['name'];?></option>
                            <?php }?>
                        </select>
                    </div>
				</td>
			</tr>
			<tr>
				<td class="w-150">Publish on</td>
				<td>
					<?php $today_date = date('m/d/Y');?>
					<?php if(empty($blog_info['publish_on']) OR ($blog_info['publish_on'] > $today_date)){?>
						<input class="w-100pr" type="text" data-title="Publish on" name="publish_on" value="<?php echo empty($blog_info['publish_on']) ? $today_date : $blog_info['publish_on']?>" readonly>
					<?php }else{?>
						<?php echo $today_date;?>
					<?php }?>
				</td>
			</tr>
			<tr>
				<td>Title</td>
				<td>
                    <div class="form-group">
                        <input class="w-100pr validate[required,maxSize[255]]" type="text" name="title" value="<?php echo $blog_info['title']?>"/>
                    </div>
				</td>
			</tr>
			<tr>
				<td>SEO description</td>
				<td>
                    <div class="form-group">
					    <textarea class="w-100pr h-100 validate[required,maxSize[500]]" name="short_description" ><?php echo $blog_info['short_description']?></textarea>
                    </div>
				</td>
			</tr>
			<tr>
				<td>Description</td>
				<td>
                    <div class="form-group">
					    <textarea class="w-100pr h-100 validate[required,maxSize[200]]" name="description" ><?php echo $blog_info['description']?></textarea>
                    </div>
				</td>
			</tr>
            <tr>
                <td colspan="2">
                    <div class="alert alert-info" role="alert">
                        To display a slider with products, copy the text by clicking on the link: <a href="#" class="js-copy preview-content-link" data-text="[[EXPORT_PORTAL_PRODUCT_ADS_SLIDER]]">COPY</a> and paste it into the right place in the text from a new line.
                    </div>
                </td>
            </tr>
			<tr>
				<td class="bg-white" colspan="2">
					<ul class="nav-b nav nav-tabs clearfix pt-10" role="tablist">
						<li role="presentation" class="active"><a href="#blog-content" aria-controls="title" role="tab" data-toggle="tab">Content</a></li>
						<li role="presentation"><a class="preview-content-link" href="#blog-preview-content" aria-controls="title" role="tab" data-toggle="tab">Preview content</a></li>
					</ul>

					<div class="tab-content nav-info clearfix">
						<div role="tabpanel" class="tab-pane active" id="blog-content">
                            <div class="form-group">
                                <textarea id="blog-text-block" class="blog-text-block validate[required]" name="content" ><?php echo $blog_info['content']?></textarea>
                            </div>
						</div>
						<div role="tabpanel" class="tab-pane" id="blog-preview-content">
							<div class="preview-blog-text-block"></div>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>Tags</td>
				<td>
					<?php if(!empty($blog_info['tags'])){?>
						<?php $blog_tags = explode(',', $blog_info['tags']);?>
						<select class="blog-tags w-100pr" name="tags[]" multiple>
							<?php foreach($blog_tags as $tag){ ?>
							<option selected="selected"><?php echo $tag?></option>
							<?php } ?>
						</select>
					<?php }else{ ?>
						<select class="blog-tags w-100pr" name="tags[]" multiple></select>
					<?php } ?>
				</td>
			</tr>
            <tr>
				<td>Caption Main photo</td>
				<td>
                    <div class="form-group">
                        <textarea class="w-100pr mnh-70 validate[maxSize[250]]" name="photo_caption" ><?php echo $blog_info['photo_caption']?></textarea>
                    </div>
                </td>
			</tr>
			<tr>
				<td>Main photo</td>
				<td>
					<span class="btn btn-success fileinput-button">
						<i class="ep-icon ep-icon_plus"></i>
						<span>Select file...</span>
						<!-- The file input field used as target for the file upload widget -->
						<input id="edit_fileupload" type="file" name="files[]">
					</span>
					<span class="fileinput-loader-btn" style="display:none;"><img src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader"> Uploading...</span>
					<div class="info-alert-b mt-10">
						<i class="ep-icon ep-icon_info"></i>
						<div> &bull; The maximum file size has to be 2MB.</div>
						<div> &bull; Min width: 1150px, Min height: 500px.</div>
						<div> &bull; You cannot upload more than 1 photo.</div>
						<div> &bull; Format: jpg,jpeg,png,bmp.</div>
					</div>

					<!-- The container for the uploaded files -->
					<div class="fileupload-queue files mt-10">
						<?php if(!empty($blog_info['photo'])){?>
							<div class="uploadify-queue-item item-middle">
								<div class="img-b">
									<img src="<?php echo $blogImage; ?>" />
								</div>
							</div>
						<?php }?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
		<label class="lh-30 vam pull-left mr-10">
			<input class="vam" type="checkbox" name="visible" <?php if(isset($blog_info)){ echo checked($blog_info['visible'], 1); }else{?>checked="checked"<?php }?>/>
			Visible
		</label>
		<label class="lh-30 vam pull-left mr-10">
			<input class="vam" type="checkbox" name="status" <?php if(isset($blog_info)){ echo checked($blog_info['status'], 'moderated'); }?>/>
			Moderated
		</label>
		<?php if(!empty($blog_info)){?>
			<input type="hidden" name="post" value="<?php echo $blog_info['id']?>"/>
		<?php }?>
		<input type="hidden" name="upload_folder" value="<?php echo $upload_folder;?>"/>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="text/javascript">
    $(".js-copy").on("click", function(e) {
        e.preventDefault();

        var textarea = document.createElement("textarea");
        textarea.id = "js-copy-fake-textarea";
        textarea.value = this.dataset.text;
        this.closest("form").appendChild(textarea);
        textarea.focus();
        textarea.select();
        document.execCommand('copy');
        textarea.remove();

        var posTop = $(this).offset().top-30;
        var posLeft = $(this).offset().left+200;
        $('body').append('<div class="alert_info" style="padding: 5px 10px; color: white; background-color: black; position: absolute; top:'+posTop+'px; left: '+posLeft+'px; z-index: 2147000000;">Copied...</div>')
        setTimeout(function(){
            $('.alert_info').remove()
        }, 200);
    });
	$(document).ready(function(){

		tinymce.init({
			selector:'.blog-text-block',
			timestamp: '<?php echo $upload_folder;?>',
			menubar: false,
			statusbar : false,
			height : 260,
			media_poster: false,
			media_alt_source: false,
			relative_urls: false,
			plugins: [
				"autolink lists link media image table contextmenu directionality"
            ],
			dialog_type : "modal",
            style_formats: [
                {title: 'H2', block: 'h2'},
                {title: 'H3', block: 'h3'},
                {title: 'H4', block: 'h4'},
            ],
            toolbar: " undo redo | styleselect | outdent indent | bold italic underline link | alignleft aligncenter alignright alignjustify | numlist bullist | ltr rtl | insertfile | media | image | table",
            image_caption: true,
            file_picker_types: 'image',
            file_picker_upload_url: '/blogs/upload_photo/<?php echo $upload_folder;?>',
            file_picker_callback: function(callback, value, meta){
                if (meta.filetype !== 'image') {
                    return;
                }

                var url = location.origin + (this.settings.file_picker_upload_url || '');
                var body = $(body);
                var input = body.find('input#blog-inline-image-upload');
                if(input.length === 0) {
                    input = $('<input>');
                    input.css({display: 'none'});
                    input.attr('id', 'blog-inline-image-upload');
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
                                    callback(location.origin + response.message, { title: file.name });
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
            resize: false,
            contextmenu: 'inserttable | cell row column deletetable'
		});

		$('body').on('click', '.preview-content-link', function(){
			var content =  tinymce.get('blog-text-block').getContent();
			var $wrform = $(this).closest('.wr-modal-b');
			var timestamp =  $('.modal-b__content').find('input[name=timestamp]').val();

			if(content.length){
				$.ajax({
					type: 'POST',
					url: '<?php echo __SITE_URL?>blogs/ajax_blogs_operation/preview_content',
					data: { content : content, timestamp: timestamp },
					beforeSend: function(){ showFormLoader($wrform); },
					dataType: 'json',
					success: function(resp){
						hideFormLoader($wrform);

						if(resp.mess_type == 'success'){
							$('.preview-blog-text-block').html(resp.content);
						}else{

						}
					}
				});
			}else{
				$('.preview-blog-text-block').html('<div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> Content is empty.</div>');
			}
		});

		$(".blog-tags").select2({ width: '100%', tags: true, tokenSeparators: [',']});

		$('select[name="blog_lang"]').on('change', function(){
			var $this = $(this);
			var blog_lang = $this.val();
			$.ajax({
				url: '<?php echo __SITE_URL;?>blogs/ajax_blogs_operation/get_blog_categories',
				type: 'POST',
				dataType: "JSON",
				data: {blog_lang:blog_lang},
				beforeSend: function(){
					$('select[name="category"]').prop('disabled', true);
				},
				success: function(resp){
					if(resp.mess_type == 'success'){
						var options = '';
						if(resp.categories.length > 0){
							$.each(resp.categories, function(index, category){
								options += '<option value="'+category.id_category+'">'+category.name+'</option>';
							});
							$('select[name="category"]').html(options).prop('disabled', false);
						} else{
							options = '<option value="">Categories not found</option>';
							$('select[name="category"]').html(options);
						}
					}else{
						systemMessages( resp.message, 'message-' + resp.mess_type );
					}
				},
				error: function(jqXHR, textStatus, errorThrown){
					systemMessages( 'Error: Please try again later.', 'message-error' );
					jqXHR.abort();
				}
			});
		});

		$('input[name="publish_on"]').datepicker();
	});

    const clearVideoContent = newContent => {
        newContent.find('[data-video="video-iframe"]').each((i, e) => {
            const el = $(e);

            if (el.find("> iframe").length) {

            } else if (el.find("iframe").length) {
                el.html(el.find("iframe"));
            } else if (el.find('[data-video="video-iframe"]').length) {
                el.replaceWith(el.find('[data-video="video-iframe"]').last().html());
            } else {
                el.replaceWith(el.html());
            }
            // console.log(el, e, i);
        });

        let node = newContent[0];
        let result = "";
        while (node) {
            result += node.data ?? node.outerHTML;
            node = node.nextSibling;
        }

        $("#blog-text-block").val(result);
    }

	function modalFormCallBack(form, data_table){
        tinymce.triggerSave();
        const newContent = $(tinymce.get('blog-text-block').getContent());

        if (newContent.find('[data-video="video-iframe"]').length) {
            clearVideoContent(newContent);
        }

		var $form = $(form);

		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>blogs/ajax_blogs_operation/<?php echo (isset($blog_info) ? 'edit': 'add'  )?>_blog',
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

	var url_photo = 'blogs/ajax_blog_upload_photo/<?php echo $upload_folder;?>';

	$('#edit_fileupload').fileupload({
		url: url_photo,
		dataType: 'json',
		maxFileSize: <?php echo config('fileupload_max_file_size');?>,
		beforeSend: function () {
			$('.fileinput-loader-btn').fadeIn();
		},
		done: function (e, data) {
			if(data.result.mess_type == 'success'){
				$.each(data.result.files, function (index, file) {
					var itemID = +(new Date());
					$('.fileupload-queue').html(templateFileUpload('img','item-middle',itemID));
					$('#fileupload-item-'+itemID+' .img-b').append('<img src="'+file.url+'">');
					$('#fileupload-item-'+itemID+' .img-b').append('<input type="hidden" name="images[]" value="'+file.path+'">');
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
</script>
