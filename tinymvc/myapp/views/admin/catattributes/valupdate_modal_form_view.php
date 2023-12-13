<form method="post" class="validateModal relative-b" data-attribute-id="<?php echo $value['attribute']?>">
   <div class="wr-form-content w-700">
    <table cellpadding="5" cellspacing="0"  class="data table-striped temp w-100pr">
        <tr>
            <td>Attribute's value:</td>
            <td><input type="text" name="value" class="w-98pr validate[required]" value="<?php echo $value['value']?>"/></td>
        </tr>
    </table>
	</div>	
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="id" value="<?php echo $value['id'];?>" />
		<button class="pull-right btn btn-default ml-10" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: 'catattr/ajax_attr_operation/update_values',
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
				$('.details[data-id="<?php echo $value['attribute'];?>"]').click().click();
			}
		}
	});
}
</script>
