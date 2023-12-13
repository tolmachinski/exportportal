<div class="js-modal-flex wr-modal-flex inputs-40">
	<form id="contact-applicant-form" class="modal-flex__form validateModal">
		<div class="modal-flex__content w-700">
			<table>
				<tr>
					<td>
                        <div class="img-b tac pull-left mr-10 w-55 h-40 relative-b">
                            <img
                                class="mw-55 mh-40 img-position-center"
                                src="<?php echo $applicant_info['photo']; ?>" alt="<?php echo $applicant_info['fullname']; ?>"/>
                        </div>
                        <div class="text-b pull-left">
                            <div class="top-b lh-20 clearfix">
                                <?php echo $applicant_info['fullname']; ?>
                            </div>
                        </div>
					</td>
				</tr>
            </table>

            <div class="form-group">
                <label class="input-label">Subject</label>
                <input class="validate[required]" style="width: 100%;" type="text" name="subject" value="" placeholder="Subject"/>
            </div>

            <div class="form-group">
                <label class="input-label">Message</label>
				<textarea class="validate[required] h-150" name="content" placeholder="Message"></textarea>
            </div>
            <input type="hidden" value="<?php echo $applicant_info['id_applicant'];?>" name="applicant" />
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Send</button>
            </div>
		</div>
	</form>
</div>
<script>
function modalFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();
	$.ajax({
		type: 'POST',
		url: "<?php echo $contact['url']; ?>",
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showLoader($wrform, 'Sending email...');
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			hideLoader($wrform);
			systemMessages( resp.message, resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
