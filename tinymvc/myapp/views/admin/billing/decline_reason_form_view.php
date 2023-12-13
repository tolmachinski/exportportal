<div class="wr-modal-b">
	<form id="contact-user-form" class="modal-b__form validateModal">
		<div class="modal-b__content w-700">
			<table>
				<tr>
					<td class="w-50pr">
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
					<td>
                        <div class="text-b tar pull-right">
                            <div class="top-b lh-20 clearfix">
                                Bill number: <strong><?php echo orderNumber($bill_info['id_bill']);?></strong>
                            </div>
                            <div class="w-100pr lh-20 txt-gray-light">
                                <?php echo $bill_info['bill_description'];?>
                            </div>
                        </div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<label class="modal-b__label">Decline reason</label>
                        <select name="notification_message" id="notification_messages" class="form-control">
                            <option value="">Select reason</option>
                            <?php foreach($notification_messages as $notification_message){?>
                                <option value="<?php echo $notification_message['id_message'];?>"><?php echo $notification_message['message_title'];?></option>
                            <?php }?>
                            <option value="other">Other</option>
                        </select>
                        <input class="notification_message mt-10" style="display:none;" type="text" name="subject" value="" placeholder="Subject"/>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<label class="modal-b__label">Message</label>
						<textarea class="validate[required] h-150" name="reason_text" placeholder="Message"></textarea>
					</td>
				</tr>
			</table>
		</div>
		<div class="modal-b__btns clearfix">
			<input type="hidden" value="<?php echo $bill_info['id_bill'];?>" name="bill" />
			<button class="btn btn-primary pull-right" type="submit">Send</button>
		</div>
	</form>
</div>
<script>
var notification_messages = $.parseJSON('<?php echo json_encode($notification_messages);?>');
$(function(){
    $('#notification_messages').on('change', function(){
        var selected_value = $(this).val();
        if(selected_value == 'other'){
            $('input.notification_message').show();
            $('textarea[name="reason_text"]').text('');
        } else if(selected_value != ''){
            $('input.notification_message').hide();
            $('textarea[name="reason_text"]').text(notification_messages[selected_value].message_text);
        } else{
            $('input.notification_message').hide();
            $('textarea[name="reason_text"]').text('');
        }
    });
});
function modalFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.wr-modal-b');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL;?>billing/ajax_bill_operations/decline_bill',
		data: $form.serialize(),
		dataType: 'JSON',
		beforeSend: function(){
			showFormLoader($wrform, 'Sending email...');
		},
		success: function(resp){
			hideFormLoader($wrform);
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
                $(globalThis).trigger('billing:success-decline-bill');

                try {
                    dt_redraw_callback();
                } catch (error) {
                    // If the function was undefined
                }

				closeFancyBox();
			}
		}
	});
}
</script>
