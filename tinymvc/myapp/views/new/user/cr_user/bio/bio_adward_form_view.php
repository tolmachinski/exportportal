<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal">
		<div class="modal-flex__content">
			<div class="container-fluid-modal">
				<label class="input-label input-label--required">Title</label>
				<input class="validate[required,maxSize[250]]" type="text" name="title" value="<?php if(!empty($user_award)){echo $user_award['title'];}?>" placeholder="Title"/>

				<div class="row">
					<div class="col-6">
						<label class="input-label">Issuer</label>
						<input class="validate[maxSize[250]]" type="text" name="issuer" value="<?php if(!empty($user_award)){echo $user_award['issuer'];}?>" placeholder="Issuer"/>
					</div>
					<div class="col-6">
						<label class="input-label input-label--required">Date</label>
						<input id="award_date" type="text" name="award_date" value="<?php if(!empty($user_award)){echo ($user_award['award_date'])?formatDate($user_award['award_date'], 'm/d/Y'):'';}?>" placeholder="Date" readonly>
					</div>
				</div>

				<label class="input-label">Description</label>
				<textarea class="validate[maxSize[1000]] textcounter_award-description" data-max="1000" name="description" placeholder="Description"><?php if(!empty($user_award)){echo $user_award['description'];}?></textarea>
			</div>
            <?php if(!empty($user_award)){?>
                <input type="hidden" name="id_award" value="<?php echo $user_award['id_award']?>"/>
            <?php }?>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
		</div>
	</form>
</div>
<script>
$(function(){
    $( "#award_date" ).datepicker({
		dateFormat: "mm/dd/yy",
		beforeShow: function (input, instance) {
            $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
        },
 	});

    $('.textcounter_award-description').textcounter({
		countDown: true,
		countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
		countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
	});
});
function modalFormCallBack(form){
	var $form = $(form);
    var fdata = $form.serialize();
    var $wrapper = $form.closest('.js-modal-flex');

	$.ajax({
		type: 'POST',
		url: 'cr_user/ajax_operations/<?php if(!empty($user_award)){?>edit<?php } else{?>add<?php }?>_award',
		dataType: 'JSON',
		data: fdata,
		beforeSend: function(){
			showLoader($wrapper);
            $form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			systemMessages(resp.message, resp.mess_type);

			if(resp.mess_type == 'success'){
				<?php if(!empty($user_award)){?>
					manage_awards_callback(resp, '<?php echo $user_award['id_award']?>');
				<?php }else{?>
					manage_awards_callback(resp);
				<?php }?>

				closeFancyBox();
			} else{
				$form.find('button[type=submit]').removeClass('disabled');
			    hideLoader($wrapper);
			}
		}
	});
}
</script>
