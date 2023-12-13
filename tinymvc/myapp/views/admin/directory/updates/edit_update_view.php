<form  id="edit_update_form" method="post" class="validateModal relative-b">
    <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr m-auto vam-table">
        <tbody>
            <tr>
                <td class="w-100">
                    Text
                </td>
                <td>
                    <textarea class="w-100pr validate[required] update_text_block" name="text" id="edit_update_text_block" placeholder="Write your update here"><?php if(isset($update)) echo $update['text_update'];?></textarea>
                </td>
                <?php if(!empty($update['photo_path'])){?>
                <td class="vat w-75">
                    <div class="img-list-b pull-left mr-5 mb-5 relative-b">
                        <img src="<?php echo $update['imageLink']; ?>" />
                        <a class="ep-icon ep-icon_remove txt-red absolute-b pos-r0 m-0 bg-white confirm-dialog" data-message="Are you sure want to delete this image?" title="Delete image" data-update="<?php echo $update['id_update'];?>" data-callback="delete_update_image"></a>
                    </div>
                </td>
                <?php }?>
            </tr>
		</tbody>
	</table>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="id" value="<?php if(isset($update)) echo $update['id_update'];?>"/>
		<button class="pull-right btn btn-default" type="submit" name="edit_news"><span class="ep-icon ep-icon_ok"></span> Save changes</button>
	</div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="text/javascript">
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL;?>directory/ajax_company_updates_operation/edit_update',
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

	tinymce.init({
		selector:'.update_text_block',
		menubar: false,
		statusbar : false,
		height : 140,
		plugins: ["autolink lists link textcolor"],
		dialog_type : "modal",
		toolbar: "bold italic underline forecolor backcolor link | numlist bullist ",
		resize: false
	});
</script>
