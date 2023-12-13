<form class="validateModal relative-b">
	<div class="wr-form-content w-700 h-330">
		<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr m-auto">
			<tr>
				<td class="w-100">Category</td>
				<td>
					<select name="name_category" class="w-100pr validate[required]">
						<?php foreach($question_categories as $category){?>
							<option value="<?php echo $category['id_category']?>" <?php if(isset($question_info)) echo selected($question_info['id_category'],$category['id_category']);?>><?php echo $category['name_category']?></option>
						<?php }?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="w-100">Title</td>
				<td><input type="text" class="w-100pr validate[required,maxSize[255]]" name="title" placeholder="Title" value="<?php if(isset($question_info)) echo $question_info['title_question']?>"/></td>
			</tr>
			<tr>
				<td class="w-100">Question</td>
				<td colspan="2">
					<textarea class="w-100pr h-100 validate[required,maxSize[500]] textcounter-question_text" data-max="500" name="description" placeholder="Question description"><?php if(isset($question_info)) echo $question_info['question']?></textarea>
				</td>
			</tr>
			<tr>
				<td class="w-100">Reply</td>
				<td colspan="2">
					<textarea class="w-100pr h-100 validate[required,maxSize[5000]] textcounter-question_reply" data-max="5000" name="reply" placeholder="Question reply"><?php if(isset($question_info)) echo $question_info['reply']?></textarea>
				</td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($question_info)){?>
			<input type="hidden" name="question" value="<?php echo $question_info["id_q"];?>"/>
			<?php } ?>
		<button class="pull-right btn btn-default" type="submit" name="edit_question"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript">
	$(function(){
		$('.textcounter-question_text, .textcounter-question_reply').textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		});
	});
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>items_questions/ajax_question_operation/edit_question_admin',
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
