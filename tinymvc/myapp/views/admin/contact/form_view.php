<div class="wr-modal-b">
	<form id="contact-user-form" class="modal-b__form validateModal">
		<div class="modal-b__content w-700">
			<table>
				<tr>
					<td>
						<label class="modal-b__label">Subject</label>
                        <input type="text" class="validate[required]" name="subject" placeholder="Subject">
					</td>
				</tr>
				<tr>
					<td>
						<label class="modal-b__label">Message</label>
						<textarea class="validate[required] h-150" name="content" placeholder="Message"><?php if(!empty($text)) echo $text;?></textarea>
					</td>
				</tr>
			</table>
		</div>
		<div class="modal-b__btns clearfix">
			<button class="btn btn-primary pull-right" type="submit">Send</button>
		</div>
	</form>
</div>
<script>
function modalFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.wr-modal-b');
	var fdata = $form.serialize();

	$.ajax({
		type: 'POST',
		url: 'contact/ajax_contact_operations/send_admin_message',
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showFormLoader($wrform, 'Sending message...');
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			hideFormLoader($wrform);
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
