<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr">
			<tr>
				<td class="w-120">Visible</td>
				<td>
					<label><input class="validate[required]" type="radio" name="visible_cat" <?php if(isset($category)) echo checked($category['visible_cat'], 1); else echo 'checked="checked"' ?> value="1"> Yes</label>
					<label><input class="validate[required]" type="radio" name="visible_cat" <?php if(isset($category)) echo checked($category['visible_cat'], 0)?> value="0"> No  </label>
				</td>
			</tr>
			<tr>
				<td class="w-120">On main page</td>
				<td>
					<label><input type="radio" name="on_main_page" <?php if(isset($category)) echo checked($category['on_main_page'], 1); ?> value="1"> Yes</label>
					<label><input type="radio" name="on_main_page" <?php if(isset($category)) echo checked($category['on_main_page'], 0); else echo 'checked="checked"' ?> value="0"> No  </label>
				</td>
			</tr>
			<tr>
				<td class="w-120">Title</td>
				<td><input class="w-100pr" type="text" name="title_cat" value="<?php echo $category['title_cat'];?>"></td>
			</tr>
			<tr>
				<td class="w-120">Icon</td>
				<td><input class="w-100pr" type="text" name="icon" value="<?php echo $category['icon'];?>"></td>
			</tr>
			<tr>
				<td class="w-120">Order</td>
				<td>
					<select class="w-100pr" name="order">
						<?php for($i = 0; $i <= 6; $i++){?>
							<option value="<?php echo $i; ?>" <?php echo selected($category['order_number'], $i); ?>><?php echo $i;?></option>
						<?php }?>
					</select>
				</td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($category)){?>
		<input type="hidden" name="id_category" value="<?php echo $category['idcat'];?>">
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
function modalFormCallBack(form, data_table){
	var $form = $(form);
	var fdata = $form.serialize();

	<?php if(isset($category)){?>
		var url = "community_questions/ajax_question_categories_operation/edit_category";
	<?php }else{?>
		var url = "community_questions/ajax_question_categories_operation/create_category";
	<?php }?>

	$.ajax({
		type: 'POST',
		url: url,
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();
				if(data_table != undefined)
					data_table.fnDraw(false);
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
