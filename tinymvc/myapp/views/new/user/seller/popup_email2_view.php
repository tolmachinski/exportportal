<div class="js-modal-flex wr-modal-flex inputs-40">
	<form id="js-popup-email-form" class="modal-flex__form validateModal2">
		<div class="modal-flex__content">
			<ul class="nav nav-tabs nav--borders" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" href="#about-b2b" aria-controls="title" role="tab" data-toggle="tab">About</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="#meeting-b2b" aria-controls="title" role="tab" data-toggle="tab">Meeting</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="#phone-b2b" aria-controls="title" role="tab" data-toggle="tab">Phone</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="#meeting_else-b2b" aria-controls="title" role="tab" data-toggle="tab">Meeting else</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="#purchase_order-b2b" aria-controls="title" role="tab" data-toggle="tab">Purchase Order</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="#special_order-b2b" aria-controls="title" role="tab" data-toggle="tab">Special Order</a>
				</li>
			</ul>

			<div class="tab-content tab-content--borders">
				<div role="tabpanel" class="tab-pane fade show active" id="about-b2b">
					<div class="row">
						<div class="col-12">
							<label class="input-label input-label--required">Insert email addresses</label>
							<?php global $tmvc;?>
							<input class="validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[<?php echo $tmvc->my_config['email_this_max_email_count'];?>]]" type="text" name="emails1" value="" placeholder="Email addresses"/>
							<p class="fs-12 txt-red">*Please use comma as email separators </p>
						</div>
						<div class="col-12">
							<label class="input-label input-label--required">Message</label>
							<textarea class="validate[required,maxSize[1000]]" data-max="1000" name="message1" placeholder="Message"><?php if(!empty($text)) echo $text;?></textarea>
						</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="meeting-b2b">
					<div class="row">
					<div class="col-12">
						<label class="input-label input-label--required">Insert email addresses</label>
						<?php global $tmvc;?>
						<input class="validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[<?php echo $tmvc->my_config['email_this_max_email_count'];?>]]" type="text" name="emails2" value="" placeholder="Email addresses"/>
						<p class="fs-12 txt-red">*Please use comma as email separators </p>
					</div>
					<div class="col-12">
						<label class="input-label input-label--required">Message</label>
						<textarea class="validate[required,maxSize[1000]]" data-max="1000" name="message2" placeholder="Message"><?php if(!empty($text)) echo $text;?></textarea>
					</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="phone-b2b">
					<div class="row">
					<div class="col-12">
						<label class="input-label input-label--required">Insert email addresses</label>
						<?php global $tmvc;?>
						<input class="validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[<?php echo $tmvc->my_config['email_this_max_email_count'];?>]]" type="text" name="emails3" value="" placeholder="Email addresses"/>
						<p class="fs-12 txt-red">*Please use comma as email separators </p>
					</div>
					<div class="col-12">
						<label class="input-label input-label--required">Message</label>
						<textarea class="validate[required,maxSize[1000]]" data-max="1000" name="message3" placeholder="Message"><?php if(!empty($text)) echo $text;?></textarea>
					</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="meeting_else-b2b">
					<div class="row">
					<div class="col-12">
						<label class="input-label input-label--required">Insert email addresses</label>
						<?php global $tmvc;?>
						<input class="validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[<?php echo $tmvc->my_config['email_this_max_email_count'];?>]]" type="text" name="emails4" value="" placeholder="Email addresses"/>
						<p class="fs-12 txt-red">*Please use comma as email separators </p>
					</div>
					<div class="col-12">
						<label class="input-label input-label--required">Message</label>
						<textarea class="validate[required,maxSize[1000]]" data-max="1000" name="message4" placeholder="Message"><?php if(!empty($text)) echo $text;?></textarea>
					</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="purchase_order-b2b">
					<div class="row">
					<div class="col-12">
						<label class="input-label input-label--required">Insert email addresses</label>
						<?php global $tmvc;?>
						<input class="validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[<?php echo $tmvc->my_config['email_this_max_email_count'];?>]]" type="text" name="emails5" value="" placeholder="Email addresses"/>
						<p class="fs-12 txt-red">*Please use comma as email separators </p>
					</div>
					<div class="col-12">
						<label class="input-label input-label--required">Message</label>
						<textarea class="validate[required,maxSize[1000]]" data-max="1000" name="message5" placeholder="Message"><?php if(!empty($text)) echo $text;?></textarea>
					</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="special_order-b2b">
					<div class="row">
					<div class="col-12">
						<label class="input-label input-label--required">Insert email addresses</label>
						<?php global $tmvc;?>
						<input class="validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[<?php echo $tmvc->my_config['email_this_max_email_count'];?>]]" type="text" name="emails6" value="" placeholder="Email addresses"/>
						<p class="fs-12 txt-red">*Please use comma as email separators </p>
					</div>
					<div class="col-12">
						<label class="input-label input-label--required">Message</label>
						<textarea class="validate[required,maxSize[1000]]" data-max="1000" name="message6" placeholder="Message"><?php if(!empty($text)) echo $text;?></textarea>
					</div>
					</div>
				</div>
			</div>
            <input type="hidden" value="<?php echo $id_company?>" name="id" />
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Send</button>
            </div>
		</div>
	</form>
</div>
<script>
$(function(){
	$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
		e.target // newly activated tab
		e.relatedTarget // previous active tab

		// console.log(e.target.getAttribute('href'));
		validate_visible();
		// console.log(validateTab(e.target, e.target.getAttribute('href')));
		/*if(!validateTab()){
			console.log('add red');
			e.relatedTarget.classList.add("bg-red");
		}else{
			console.log('remove red');
			e.relatedTarget.classList.remove("bg-red");
		}*/
	});

	//validate_all();

	$('#js-popup-email-form').on('submit', function(e){
		e.preventDefault;
		validate_all();

		return false;
	})

	$('.validateModal2').validationEngine("attach", {
		validateNonVisibleFields: true,
		updatePromptsPosition:true,
		promptPosition : "topLeft:0",
		autoPositionUpdate : true,
		focusFirstField: false,
		scroll: false,
		showArrow : false,
		addFailureCssClassToField : 'validengine-border',
		onValidationComplete: function(form, status){
			statusValidate = status;

			$('.nav-link').removeClass('bg-red');

			$('.tab-pane').each(function(element){
				// console.log($(this));
				// console.log('len:'+$(this).find('.formError').length);

				if($(this).find('.formError').length){
					$('.nav-link[href="#'+$(this).attr('id')+'"]').addClass('bg-red');
				}
			});

			if(status){
				if($(form).data("callback") != undefined)
					window[$(form).data("callback")](form, $caller_btn);
				else
					modalFormCallBack(form, $caller_btn);
			} else {
				systemMessages(translate_js({ plug: 'general_i18n', text: 'validate_error_message' }), 'error');
			}
		}
	});
});

function validateTab(tab, tabContent){

	// console.log('length:' + $(tabContent).find('.formError').length);
	if($(tabContent).find('.formError').length){
		tab.classList.add("bg-red")
	}else{
		tab.classList.remove("bg-red");
	}

	return validate_visible();
}

function validate_visible(){
	var statusValidate = false;

	$('.validateModal2').validationEngine("validate", {
		updatePromptsPosition:true,
		promptPosition : "topLeft:0",
		autoPositionUpdate : true,
		focusFirstField: false,
		scroll: false,
		showArrow : false,
		addFailureCssClassToField : 'validengine-border',
		onValidationComplete: function(form, status){
			statusValidate = status;
		}
	});

	return statusValidate;
}

function validate_all(){

	var statusValidate = false;

	$('.validateModal2').validationEngine("validate", {
		validateNonVisibleFields: true,
		updatePromptsPosition:true,
		promptPosition : "topLeft:0",
		autoPositionUpdate : true,
		focusFirstField: false,
		scroll: false,
		showArrow : false,
		addFailureCssClassToField : 'validengine-border',
		onValidationComplete: function(form, status){
			statusValidate = status;

			$('.nav-link').removeClass('bg-red');

			$('.tab-pane').each(function(element){
				// console.log($(this));
				// console.log('len:'+$(this).find('.formError').length);

				if($(this).find('.formError').length){
					$('.nav-link[href="#'+$(this).attr('id')+'"]').addClass('bg-red');
				}
			});

			if(status){
				if($(form).data("callback") != undefined)
					window[$(form).data("callback")](form, $caller_btn);
				else
					modalFormCallBack(form, $caller_btn);
			}else{
				systemMessages(translate_js({ plug: 'general_i18n', text: 'validate_error_message' }), 'error');
			}
		}
	});

	return statusValidate;
}
</script>
