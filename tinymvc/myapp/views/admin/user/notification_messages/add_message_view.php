<form  method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700 mh-500">
    <table cellspacing="0" cellpadding="0" class="data table-striped w-100pr mt-15 vam-table ">
        <tbody>
			<tr>
				<td class="w-120">Message title</td>
				<td>
					<input class="w-100pr validate[required]" type="text" name="message_title" placeholder="Message title"/>
				</td>
			</tr>
			<tr>
				<td>Message module</td>
				<td>
					<select class="w-100pr validate[required]" name="message_module">
						<option value="">Select module</option>
						<option value="accreditation">Accreditation</option>
						<option value="billing">Billing</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Message text</td>
				<td>
					<textarea name="message_text" class="w-100pr h-100 validate[required]"></textarea>
				</td>
			</tr>
		</tbody>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
		<button class="pull-right btn btn-success" type="submit">
            <span class="ep-icon ep-icon_ok"></span> Save
        </button>
	</div>
</form>

<script>
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: "users/ajax_operations/add_notification_message",
		data: $form.serialize(),
		beforeSend: function(){ 
			showLoader($form); 
		},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			if(data.mess_type == 'success'){
				if(data_table != undefined)
						data_table.fnDraw();
                        
				closeFancyBox();
			}else{
				hideLoader($form);
			}
		}
	});
}
</script>
