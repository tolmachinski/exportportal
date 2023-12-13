<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
        <tbody>
			<tr>
				<td class="w-150">Title</td>
				<td>
					<input class="w-100pr validate[required, maxSize[200]]" type="text" name="title" placeholder="Title of document" value="<?php if(isset($document)) echo $document['title_file'];?>"/>
                </td>
			</tr>
			<tr>
				<td class="w-150">Access type</td>
				<td>
					<input type="radio" name="file_type" value="public" <?php if(isset($document)) echo checked($document['type_file'], 'public');?> id="r-1"/> <label for="r-1">Public</label><br />
                    <input type="radio" name="file_type" value="private" <?php if(isset($document)) echo checked($document['type_file'], 'private');?> id="r-2"/> <label for="r-2">Private</label>
				</td>
			</tr>
            <tr>
                <td>
                    Text
                </td>
                <td>
                    <textarea class="w-100pr h-100 validate[required] news_text_block" name="text" placeholder="Document description"><?php if(isset($document)) echo $document['description_file'];?></textarea>
                </td>
            </tr>
		</tbody>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
		 <input type="hidden" name="id" value="<?php if(isset($document)) echo $document['id_file'];?>"/>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok" name="edit_document"></span> Save</button>
	</div>
</form>
<script type="text/javascript">
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL;?>directory/ajax_company_library_operation/edit_document',
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
