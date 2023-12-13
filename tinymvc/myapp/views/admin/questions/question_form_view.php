<form  class="validateModal relative-b" method="post">
	<div class="wr-form-content w-700">
	<table cellspacing="0" cellpadding="0" class="data table-bordered table-striped w-100pr">
		<tr>
			<td class="w-100">Country</td>
			<td>
				<select name="country" class="w-100pr validate[required]">
				    <option value="">Select</option>
				<?php foreach($countries as $country){?>
				    <option value="<?php echo $country['id']?>" <?php echo selected($country['id'], $question['id_country']);?>><?php echo $country['country']?></option>
				<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Category</td>
			<td>
				<select name="category" class="w-100pr validate[required]">
					<option selected="selected" value="">Select</option>
					<?php foreach($quest_cats as $category){?>
						<option <?php echo selected($category['idcat'],$question['id_category'] ) ?> value="<?php echo $category['idcat']?>"><?php echo $category['title_cat']?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Title</td>
			<td>
				<input value="<?php echo cleanOutput($question['title_question']) ?>" type="text" name="title" class="w-100pr validate[required]"/>
			</td>
		</tr>
		<tr>
			<td>Text</td>
			<td>
				<textarea name="text" class="w-100pr h-100 validate[required]"><?php echo cleanOutput($question['text_question']) ?></textarea>
			</td>
		</tr>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="id_question" value="<?php echo $question['id_question'] ?>"/>
		<button class="pull-right btn btn-default" type="submit" name="update_question"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script type="text/javascript">
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>community_questions/ajax_questions_operation/edit_question',
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
