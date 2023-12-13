<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        data-callback="complainsFormCallBack"
        data-js-action="complains:report-form.submit"
    >
		<div class="modal-flex__content">
			<label class="input-label input-label--required"><?php echo translate('report_company_popup_form_theme_label');?></label>
			<select class="validate[required]" <?php echo addQaUniqueIdentifier('popup__complains__report-form_reason-select') ?> id="js-select-complain-theme" name="id_theme">
				<option value=""><?php echo translate('report_company_popup_form_select_theme_placeholder');?></option>
				<?php foreach($themes as $theme) {?>
					<option value="<?php echo $theme['id_theme'];?>"><?php echo $theme['theme'];?></option>
				<?php }?>
				<option value="0"><?php echo translate('report_company_popup_form_select_theme_other_option');?></option>
			</select>

			<div style="display: none" id="js-div-text-theme">
				<label class="input-label input-label--required"><?php echo translate('report_company_popup_form_select_theme_other_option');?></label>
				<input class="validate[required,maxSize[100],custom[alphaNumeric]]" <?php echo addQaUniqueIdentifier('popup__complains__report-form_theme-input') ?> type="text" name="theme" id="text-theme"/>
			</div>

			<label class="input-label input-label--required"><?php echo translate('report_company_popup_form_message_label');?></label>
			<textarea class="validate[required,maxSize[500]] js-textcounter-message" <?php echo addQaUniqueIdentifier('popup__complains__report-form_message-textarea') ?> data-max="500" name="text" placeholder="<?php echo translate('report_company_popup_form_message_label', null, true);?>"><?php if(!empty($text)) echo $text;?></textarea>
		</div>
		<div class="modal-flex__btns">
			<input type="hidden" name="id_type" value="<?php echo $id_type?>"/>
			<input type="hidden" name="id_item" value="<?php echo $id_item?>"/>
			<input type="hidden" name="id_to" value="<?php echo $id_to?>"/>
			<input type="hidden" name="id_company" value="<?php echo $id_company ?? 0?>"/>

			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit" <?php echo addQaUniqueIdentifier('popup__complains__report-form_save-btn') ?>>
                    <?php echo translate('report_company_popup_form_submit_btn');?>
                </button>
			</div>
		</div>
	</form>
</div>

<?php if (!$webpackData) { ?>
<script>
$('#js-select-complain-theme').on('change', function(){
	if($(this).val() != 0){
		$('#js-div-text-theme').hide();
	}else{
		$('#js-div-text-theme').show();
    }
    $.fancybox.update();
});

function complainsFormCallBack(form, $caller_btn){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();

	$.ajax({
		type: 'POST',
		url: 'complains/ajax_complains_operations/add_complain/',
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showLoader($wrform, '<?php echo translate('sending_message_form_loader', null, true);?>');
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

$('.js-textcounter-message').textcounter({
    countDown: true,
    countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
    countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
});
</script>
<?php } else { ?>
    <?php echo dispatchDynamicFragment('complains:add-report', ['loaderMessage' => translate('sending_message_form_loader', null, true)]) ?>
<?php } ?>
