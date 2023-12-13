<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/jquery-multiple-select-1-1-0/js/jquery.multiple.select.js"></script>

<div class="wr-modal-b">
	<form class="modal-b__form validateModal">
		<div class="modal-b__content w-700">
			<label class="modal-b__label">Question</label>
			<textarea class="validate[required] h-100" name="question" placeholder="Question text"><?php echo $faq_info['question'];?></textarea>
			<div class="clearfix"></div>

			<label class="modal-b__label">Answer</label>
			<textarea id="text-block" class="validate[required] h-250" name="answer" placeholder="Answer text"><?php echo $faq_info['answer'];?></textarea>

			<label class="modal-b__label">Tags</label>
			<select id="faq_tags" multiple class="form-control" name="faq_tags[]">
				<?php $attached_faq_tags = isset($attached_faq_tags) ? $attached_faq_tags : []; ?>
				<?php foreach($faq_tags as $tag){ ?>
					<option value="<?php echo $tag['id_tag'];?>" <?php echo selected(true, in_array($tag['id_tag'], $attached_faq_tags)) ?>><?php echo $tag['name'];?></option>
				<?php } ?>
			</select>
		</div>
		<div class="modal-b__btns clearfix">
			<input type="hidden" name="faq" value="<?php echo $faq_info['id_faq'] ?>"/>
            <input type="hidden" name="encripted_folder" value="<?php echo $uploadFolder;?>">
			<button class="btn btn-primary pull-right" type="submit">Submit</button>
		</div>
	</form>
</div>

<script>

var $tags = null;

function tags_select_init(){
	$tags.select2({
		multiple: true,
		placeholder: "Select tags",
		minimumResultsForSearch: 2,
		maximumSelectionLength: <?php echo config('faq_tags_max_count'); ?>
	});
}

$(document).ready(function(){
	$tags = $('select[name="faq_tags[]"]');
	tags_select_init();

	tinymce.init({
		selector:'#text-block',
		menubar: false,
		statusbar : false,
		plugins: ["autolink link lists image"],
		toolbar: "bold italic underline | link | numlist bullist | image",
		resize: false,
        relative_urls: false,
        file_picker_types: 'image',
        file_picker_upload_url: '<?php echo '/faq/ajax_faq_operation/upload_temp_inline_image/' . $uploadFolder;?>',
        file_picker_callback: function(callback, value, meta){
            if (meta.filetype !== 'image') {
                return;
            }

            var url = location.origin + (this.settings.file_picker_upload_url || '');
            var body = $(body);
            var input = body.find('input#faq-inline-image-upload');
            if(input.length === 0) {
                input = $('<input>');
                input.css({display: 'none'});
                input.attr('id', 'faq-inline-image-upload');
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
                    formData.append('faq_inline_image', blobInfo.blob());
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
                                callback(location.origin + '/' + response.path, { title: file.name });
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
	});
});

<?php if(!empty($faq_info)){?>
	var link = 'faq/ajax_faq_operation/edit_faq';
<?php }else{?>
	var link = 'faq/ajax_faq_operation/add_faq';
<?php }?>

function modalFormCallBack(form, data_table){
	var $form = $(form);
	var $wrform = $form.closest('.wr-modal-b');
	var fdata = $form.serialize();

	$.ajax({
		type: 'POST',
		url: link,
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showFormLoader($wrform);
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			hideFormLoader($wrform);
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();

				if(data_table != undefined)
					data_table.fnDraw();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
