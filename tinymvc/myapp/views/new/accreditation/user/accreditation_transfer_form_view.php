<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal">
		<div class="modal-flex__content">
			<p><?php echo translate('accreditation_transfer_form_delegate_description');?></p>
			<label class="input-label input-label--required"><?php echo translate('accreditation_transfer_form_input_fname');?></label>
			<input class="validate[required,maxSize[40]]" type="text" name="transfer_fname" placeholder="<?php echo translate('accreditation_transfer_form_fname_placeholder', null, true);?>">

			<label class="input-label input-label--required"><?php echo translate('accreditation_transfer_form_input_lname');?></label>
			<input class="validate[required,maxSize[40]]" type="text" name="transfer_lname" placeholder="<?php echo translate('accreditation_transfer_form_lname_placeholder', null, true);?>">

			<label class="input-label input-label--required"><?php echo translate('accreditation_transfer_form_input_email');?></label>
			<input class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]" type="text" name="transfer_email" placeholder="<?php echo translate('accreditation_transfer_form_email_placeholder', null, true);?>"/>
		</div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit"><?php echo translate('accreditation_transfer_form_delegate_btn');?></button>
			</div>
		</div>
	</form>
</div>
<script>
function modalFormCallBack(form){
	var $form = $(form);
	var $wrapper = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();
	$.ajax({
		type: 'POST',
		url: '<?php echo 'accreditation/ajax_operations/user_transfer/' . $token;?>',
		dataType: 'JSON',
		data: fdata,
		beforeSend: function(){
			showLoader($wrapper);
		},
		success: function(resp){
			systemMessages(resp.message, resp.mess_type);
			hideLoader($wrapper);
			if(resp.mess_type == 'success'){
				closeFancyBox();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>

