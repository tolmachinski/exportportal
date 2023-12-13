<form class=" validateModal relative-b" method="post">
	<div class="wr-form-content w-700">
	<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr m-auto">
		<tr>
			<td>Text</td>
			<td>
				<textarea class="validate[required] w-100pr h-100" name="description" placeholder="Write your comment here"><?php echo $comment_info['comment']?></textarea>
			</td>
		</tr>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="comment" value="<?php echo $comment_info['id_comm'];?>"/>
		<button class="pull-right btn btn-default" type="submit" name="edit_comment"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript">
function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>items_comments/ajax_comments_administration_operation/edit',
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
