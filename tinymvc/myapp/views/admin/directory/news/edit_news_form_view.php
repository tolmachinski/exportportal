<form method="post" class="validateModal relative-b">
<div class="wr-form-content w-700 h-400">
    <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr">
        <tbody>
            <?php if($item['image_news']!= ''){?>
			    <tr>
                    <td>
                        News photo
                    </td>
                    <td colspan="2" class="vat">
                        <div class="img-list-b relative-b pull-left">
                            <img src="<?php echo $item['imageLink']; ?>" width="80" height="80" />
                            <a data-callback="delete_image" class="ep-icon ep-icon_remove txt-red absolute-b pos-r0 pos-t0 m-0 bg-white confirm-dialog" title="Delete image" data-news="<?php echo $item['id_news'];?>" data-message="Are you sure want do delete image?"></a>
                        </div>
                    </td>
                </tr>
            <?php }?>
            <tr class="h-50">
                <td>
                    Title
                </td>
                <td>
                    <input class="w-100pr validate[required, maxSize[200]]" type="text" name="title" placeholder="Headline or title of your news" value="<?php if(isset($item)) echo $item['title_news'];?>"/>
                </td>
            </tr>
            <tr>
                <td>
                    Text
                </td>
                <td>
                    <textarea class="w-100pr validate[required] news_text_block" name="text" id="edit_news_text_block" placeholder="Write your news here"><?php if(isset($item)) echo $item['text_news'];?></textarea>
                </td>
            </tr>
		</tbody>
	</table>
</div>
<div class="wr-form-btns clearfix">
	<input type="hidden" name="id" value="<?php if(isset($item)) echo $item['id_news'];?>"/>
	<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save changes</button>
</div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="text/javascript">
	function modalFormCallBack(form, data_table){
		var $form = $(form);
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: '<?php echo __SITE_URL;?>directory/ajax_company_news_operation/edit_news',
            data: $form.serialize(),
            beforeSend: function(){
                showLoader($form);
            },
            success: function(resp){
                if(resp.mess_type == 'success'){
                    $('li#news-'+resp.id_news).replaceWith( resp.news );
                    closeFancyBox();
				    data_table.fnDraw();
                }
                systemMessages( resp.message, 'message-' + resp.mess_type );
                hideLoader($form);
            }
        });
	}

	tinymce.init({
		selector:'.news_text_block',
		menubar: false,
		statusbar : false,
		height : 250,
		plugins: ["autolink lists link"],
		dialog_type : "modal",
        style_formats: [
            {title: 'H3', block: 'h3'},
            {title: 'H4', block: 'h4'},
            {title: 'H5', block: 'h5'},
            {title: 'H6', block: 'h6'},
        ],
		toolbar: "styleselect | bold italic underline link | numlist bullist ",
        resize: false
	});
</script>
