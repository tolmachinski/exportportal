<div class="wr-modal-b">
	<form id="contact-user-form" class="modal-b__form validateModal">
		<div class="modal-b__content w-700">
			<table>
				<tr>
					<td>
                        <div class="img-b tac pull-left mr-10 w-55 h-40 relative-b">
                            <img class="mw-55 mh-40 img-position-center" src="<?php echo $user_info['photo']; ?>" alt="<?php echo $user_info['fname'] . ' ' . $user_info['lname']; ?>"/>
                        </div>
                        <div class="text-b pull-left">
                            <div class="top-b lh-20 clearfix">
                                <?php echo $user_info['fname'] . ' ' . $user_info['lname'] ?>
                            </div>
                            <div class="w-100pr lh-20 txt-gray-light">
                                <?php echo $user_info['gr_name']; ?>
                            </div>
                        </div>
					</td>
				</tr>
				<tr>
					<td>
						<label class="modal-b__label">Subject</label>
                        <input class="validate[required]" type="text" name="subject" value="" placeholder="Subject"/>
					</td>
				</tr>
				<tr>
					<td>
						<label class="modal-b__label">Message</label>
						<textarea class="validate[required] h-150" name="content" placeholder="Message"></textarea>
					</td>
				</tr>
			</table>
		</div>
		<div class="modal-b__btns clearfix">
			<input type="hidden" value="<?php echo $user_info['idu'];?>" name="id_user" />
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
		url: 'contact/ajax_contact_operations/email_user',
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showFormLoader($wrform, 'Sending email...');
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
