<form  class="ask-comment-edit validateModal relative-b" method="post">
	<div class="wr-form-content w-700">
	<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr">
		<tr>
			<td class="w-100">Comment</td>
			<td>
				<textarea class="w-100pr h-100 validate[required]" name="text" placeholder="Your reply"><?php echo cleanOutput($comment['text_comment']); ?></textarea>
			</td>
		</tr>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="id_comment" value="<?php echo $comment['id_comment'];?>"/>
		<button class="pull-right btn btn-default" type="submit" name="update_comment"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script type="text/javascript">
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL ?>community_questions/ajax_comments_operation/edit_comment',
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
