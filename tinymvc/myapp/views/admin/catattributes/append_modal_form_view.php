<form method="post" class="validateModal relative-b" data-attribute-id="<?php echo $attr['id']?>">
   <div class="wr-form-content w-700">
	<table cellpadding="5" cellspacing="0" class="mt-10 data table-striped w-100pr">
		<tr>
			<td>Attribute's Values:</td>
			<td><textarea name="values" class="pull-left w-98pr validate[required]" rows="5"></textarea><span class="pull-left"> e.g.: red; green</span></td>
		</tr>
		<tr>
			<td colspan="2"><span>* If you want to insert many attribute's values, please use a semicolon (;) as delimiter.</span></td>
		</tr>
	</table>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="id" value="<?php echo $attr['id']?>"/>
		<button class="pull-right btn btn-default ml-10" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: 'catattr/ajax_attr_operation/append_values',
		data: $form.serialize(),
		beforeSend: function () {
			showLoader($form);
		},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			hideLoader($form);

			if(data.mess_type == 'success'){
				closeFancyBox();
				$('.details[data-id="<?php echo $attr['id']?>"]').click().click();
			}
		}
	});
}
</script>
