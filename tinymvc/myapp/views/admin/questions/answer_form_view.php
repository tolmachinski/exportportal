<form  class="validateModal relative-b" method="post">
	<div class="wr-form-content w-700">
	<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr">
		<tr>
			<td class="w-120">Title</td>
			<td>
				<input class="w-100pr validate[required]" type="text" name="title" value="<?php echo $answer['title_answer'] ?>" placeholder="Answer title"/>
			</td>
		</tr>
		<tr>
			<td>Answer</td>
			<td>
				<textarea class="w-100pr h-100 validate[required]" name="text" placeholder="Answer text"><?php echo cleanOutput($answer['text_answer']); ?></textarea>
			</td>
		</tr>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="answer" value="<?php echo $answer['id_answer']?>"/>
		<button class="pull-right btn btn-default" type="submit" name="update_answer"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script type="text/javascript">
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>community_questions/ajax_answers_operation/edit_answer',
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
