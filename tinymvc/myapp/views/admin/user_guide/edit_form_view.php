<form method="post" class="validateModal relative-b">
   <div class="wr-form-content w-900 h-550">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table mt-10">
			<tbody>
				<tr>
					<td>Menu parent</td>
					<td>
						<?php if(!empty($menu_info['menu_breadcrumbs'])){
							foreach ($menu_info['menu_breadcrumbs'] as $bread){
								foreach ($bread as $id_menu => $menu_title){
									$out[] = $menu_title;
								}
							}
							echo implode('<span class="crumbs-delimiter fs-16 pr-5 pl-5">&raquo;</span>', $out);?>
						<?php }?>
					</td>
				</tr>
				<tr>
					<td>User type</td>
					<td>
                        <?php if(!empty($parent_menu_users)){?>
                            <?php foreach($parent_menu_users as $parent_menu_user){?>
                                <label class="w-100pr">
                                    <input type="checkbox" name="user_type[]" class="validate[required] mt-1" value="<?php echo $parent_menu_user;?>"  <?php echo checked($parent_menu_user,$menu_users);?>>
                                    <span><?php echo ucfirst($parent_menu_user);?></span>
                                </label>
                            <?php }?>
                        <?php } else{?>
                            <label class="w-100pr">
                                <input type="checkbox" name="user_type[]" class="validate[required] mt-1" value="buyer" <?php echo checked('buyer', $menu_users);?> >
                                <span>Buyer</span>
                            </label>
                            <label class="w-100pr">
                                <input type="checkbox" name="user_type[]" class="validate[required] mt-1" value="seller" <?php echo checked('seller', $menu_users);?> >
                                <span>Seller</span>
                            </label>
                            <label class="w-100pr">
                                <input type="checkbox" name="user_type[]" class="validate[required] mt-1" value="shipper" <?php echo checked('shipper', $menu_users);?> >
                                <span>Freight Forwarder</span>
                            </label>
                            <label class="w-100pr">
                                <input type="checkbox" name="user_type[]" class="validate[required] mt-1" value="admin" <?php echo checked('admin', $menu_users);?> >
                                <span>Admin</span>
                            </label>
                        <?php }?>
					</td>
				</tr>
				<tr>
					<td>Menu title</td>
					<td>
						<input type="text" name="name" class="validate[required,maxSize[255]] w-100pr" value="<?php echo $menu_info['menu_title'];?>" />
					</td>
				</tr>
				<tr>
					<td>Document alias</td>
					<td>
						<?php echo $menu_info['menu_alias'];?>
					</td>
				</tr>
				<tr>
					<td>Document icon</td>
					<td>
						<input type="text" name="name_icon" class="w-100pr" value="<?php echo $menu_info['menu_icon'];?>" placeholder="e.g. ep-icon_pencil"/>
					</td>
				</tr>
				<tr>
					<td>Menu intro</td>
					<td>
						<textarea name="intro" class="validate[required,maxSize[200]] h-100 w-100pr"><?php echo $menu_info['menu_intro'];?></textarea>
					</td>
				</tr>
				<tr>
					<td>Menu description</td>
					<td>
						<textarea name="text" class="doc-text-block"><?php echo $menu_info['menu_description'];?></textarea>
					</td>
				</tr>
				<tr>
					<td>Video Buyer</td>
					<td>
						<input type="text" name="video_buyer" class="w-100pr" value="<?php echo $menu_info['menu_video_buyer'];?>" />
					</td>
				</tr>

				<tr>
					<td>Video Seller</td>
					<td>
						<input type="text" name="video_seller" class="w-100pr" value="<?php echo $menu_info['menu_video_seller'];?>" />
					</td>
				</tr>

				<tr>
					<td>Video Freight Forwarder</td>
					<td>
						<input type="text" name="video_shipper" class="w-100pr" value="<?php echo $menu_info['menu_video_shipper'];?>" />
					</td>
				</tr>

			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
		<input type="hidden" name="id_menu" value="<?php echo $menu_info['id_menu'];?>">
        <input type="hidden" name="upload_folder" value="<?php echo $uploadFolder;?>"/>
	</div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL . 'public/plug_admin/tinymce-4-3-10/tinymce.min.js?' . time();?>"></script>
<script>
	function modalFormCallBack(form, data_table){
		tinymce.triggerSave();
		var $form = $(form);
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL . 'user_guide/ajax_admin_operations/edit_menu';?>',
			data: $form.serialize(),
			beforeSend: function () {
				showLoader($form);
			},
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
                    get_menu();
					closeFancyBox();
				}else{
					hideLoader($form);
				}
			}
		});
	}

    $(function(){
		tinymce.init({
			selector:'.doc-text-block',
            timestamp: '<?php echo $uploadFolder;?>',
			menubar: false,
			statusbar : false,
			height : 250,
			plugins: ["autolink lists link media image table contextmenu code textcolor preview fullscreen"],
            toolbar: "code fullscreen | fontsizeselect | bold italic underline forecolor backcolor link insertfile | media | image | numlist | bullist | alignleft aligncenter alignright alignjustify",
			fontsize_formats: '8px 10px 12px 14px 16px 18px 20px 22px 24px 36px',
            dialog_type : "modal",
			media_poster: false,
			media_alt_source: false,
			relative_urls: false,
            file_picker_types: 'image',
            file_picker_upload_url: '<?php echo '/user_guide/upload_photo/' . $uploadFolder;?>',
            file_picker_callback: function(callback, value, meta){
                if (meta.filetype !== 'image') {
                    return;
                }

                var url = location.origin + (this.settings.file_picker_upload_url || '');
                var body = $(body);
                var input = body.find('input#user-guide-inline-image-upload');
                if(input.length === 0) {
                    input = $('<input>');
                    input.css({display: 'none'});
                    input.attr('id', 'user-guide-inline-image-upload');
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
	});
</script>
