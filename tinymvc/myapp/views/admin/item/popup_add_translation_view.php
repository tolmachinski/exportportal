<form  method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr mt-15 vam-table">
        <tbody>
            <tr>
				<td>Description</td>
				<td>
                    <?php if(isset($items_description['item_description'])){ echo $items_description['item_description']; }?>
				</td>
			</tr>
			<tr>
				<td>Translation</td>
				<td>
					<textarea class="js-translation-block w-100pr h-100" name="translation"><?php if(isset($item['description'])){ echo $item['description']; }?></textarea>
				</td>
			</tr>
		</tbody>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
	    <input type="hidden" name="id_item" value="<?php echo $items_description['id_item']; ?>">
		<button class="pull-right btn btn-default" type="submit">
			<span class="ep-icon ep-icon_ok"></span> Save
		</button>
	</div>
</form>

<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script>
	$(document).ready(function(){
        tinymce.init({
			selector:'.js-translation-block',
			menubar: false,
			statusbar : false,
			height : 260,
			media_poster: false,
			media_alt_source: false,
			relative_urls: false,
			plugins: [ "autolink lists link paste" ],
			dialog_type : "modal",
			toolbar: "undo redo | bold italic underline link | alignleft aligncenter alignright alignjustify | numlist bullist | insertfile",
			resize: false,
            paste_filter_drop: true,
            paste_enable_default_filters: true,
            paste_word_valid_elements: 'img,h3,h4,h5,h6,p,span,strong,em,b,i,u,a,ol,ul,li,br',
            paste_webkit_styles: 'none',
            paste_webkit_styles: 'text-decoration',
            paste_data_images: false,
            paste_retain_style_properties: 'text-decoration',
		});
    });

    function modalFormCallBack(form, data_table){
		var $form = $(form);

		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>items/ajax_item_operation/add_description_translation',
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
						data_table.fnDraw(false);
				}else{
					hideLoader($form);
				}
			}
        });
	}
</script>
