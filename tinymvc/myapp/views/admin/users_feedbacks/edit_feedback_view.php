<form class="validateModal relative-b" method="post">
	<div class="wr-form-content w-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr">
	<tr>
	    <td class="w-100">Title</td>
	    <td><input class="w-100pr" type="text" name="title" value="<?php echo $feedback['title'] ?>" placeholder="Headline or title of the feedback"/></td>
	</tr>
	<tr>
	    <td>Text</td>
	    <td>
		<textarea class="w-100pr validate[required] h-100" name="text" placeholder="Feedback"><?php echo $feedback['text'] ?></textarea>
	    </td>
	</tr>
	<tr>
	    <td>Seller's reply</td>
	    <td>
		<textarea class="w-100pr validate[required] h-100" name="reply" placeholder="Seller's reply"><?php echo $feedback['reply_text'] ?></textarea>
	    </td>
	</tr>
    </table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="feedback" value="<?php echo $feedback['id_feedback'] ?>"/>
		<button class="pull-right btn btn-default" type="submit" name="edit_review"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript">
function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>feedbacks/ajax_feedbacks_administration_operation/edit',
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

