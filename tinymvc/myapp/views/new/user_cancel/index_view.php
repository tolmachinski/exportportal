<div class="container-center dashboard-container inputs-40">
	<?php if(empty($cancel_request)){?>
		<form class="validengine relative-b" method="post" data-callback="sendAccountCancel">
			<div class="row">
				<div class="col-12 col-md-6">
					<div class="dashboard-line">
						<h1 class="dashboard-line__ttl">
							Account cancellation
						</h1>
					</div>

					<div class="info-alert-b">
						<i class="ep-icon ep-icon_info-stroke"></i>
						<span><?php echo translate('user_cancel_description'); ?></span>
					</div>

					<label class="input-label input-label--required">Date of cancellation:</label>
					<input class="validate[required] js-datepicker-validate" type="text" name="close_date" id="js-datepicker" readonly autocomplete="new-date">

					<label class="input-label input-label--required">Reason for cancellation</label>
					<textarea class="validate[required,maxSize[1000]] js-textcounter-reason-cancel" data-max="1000" name="reason"></textarea>

					<label class="input-label">Is there anything else you'd like us to know?</label>
					<textarea class="validate[maxSize[1000]] js-textcounter-feedback-cancel" data-max="1000" name="feedback"></textarea>

					<button class="btn btn-primary w-150 mt-15 pull-right" type="submit">Save</button>
				</div>
			</div>
		</form>
		<script>
			$(document).ready(function() {
				var today = new Date();
				today.setDate(today.getDate() + 1);

				$("#js-datepicker").datepicker({
					minDate: today,
					beforeShow: function(input, instance) {
						$('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
					},
				});

				$('.js-textcounter-reason-cancel').textcounter({
					countDown: true,
					countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
					countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
				});

				$('.js-textcounter-feedback-cancel').textcounter({
					countDown: true,
					countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
					countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
				});
			});

			function sendAccountCancel(form) {
				var $form = $(form);
				var fdata = $form.serialize();

				$.ajax({
					type: 'POST',
					url: __current_sub_domain_url + 'user_cancel/ajax_user_cancel_operation/account_cancel',
					data: fdata,
					dataType: 'JSON',
					beforeSend: function() {
						$form.find('button[type=submit]').addClass('disabled');
					},
					success: function(resp) {

						if (resp.mess_type == 'success') {
							$form.fadeOut('normal', function() {
								$(this).after('<div class="success-alert-b"><i class="ep-icon ep-icon_ok-circle"></i> <span>' + resp.message + '</span></div>').remove();
							});
						} else {
							systemMessages(resp.message, resp.mess_type);
							$form.find('button[type=submit]').removeClass('disabled');
						}
					}
				});
			}
		</script>
	<?php } else{?>
		<div class="row">
			<div class="col-12 col-md-6">
				<div class="dashboard-line">
					<h1 class="dashboard-line__ttl">
						Account cancellation
					</h1>
				</div>

				<div class="warning-alert-b">
					<i class="ep-icon ep-icon_info-stroke"></i>
					<span>The Account cancelation request has been already submited.</span>
				</div>
			</div>
		</div>
	<?php }?>
</div>
