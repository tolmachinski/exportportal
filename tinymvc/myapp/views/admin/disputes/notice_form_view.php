<?php
    $dispute_canceled = in_array($dispute['status'], ['canceled', 'closed']);
    $isMyDispute = is_my($dispute['id_ep_manager']);
?>

<form class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<div class="user-dispute-notices <?php if($dispute_canceled || !$isMyDispute){?>h-auto mh-500<?php }?>">
			<?php if (!empty($dispute['timeline'])) {?>
                <ul>
                    <?php foreach ($dispute['timeline'] as $notice) {?>
                        <?php if (!empty($notice)) {?>
                            <li>
                                <div class="ttl-b clearfix">
                                    <div class="name-b pull-left">by <strong><?php echo $notice['add_by'] ?></strong>: <?php echo $notice['title'] ?></div>
                                    <div class="date-b pull-right"><?php echo $notice['add_date'] ?></div>
                                </div>
                                <p><?php echo $notice['notice'] ?></p>
                            </li>
                        <?php }?>
                    <?php }?>
                </ul>
			<?php } else {?>
			    <strong>Has not notices for this user</strong>
			<?php } ?>
		</div>
		<?php if (!$dispute_canceled && $isMyDispute) {?>
			<textarea name="notice" class="w-100pr h-100 validate[required,maxSize[500]] textcounter-dispute_notice" data-max="500"></textarea>
		<?php }?>
	</div>
	<?php if (!$dispute_canceled && $isMyDispute) {?>
        <div class="wr-form-btns clearfix">
            <input type="hidden" name="disput" value="<?php echo $dispute['id']?>" />
            <button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Add notice</button>
        </div>
	<?php }?>
</form>

<?php if (!$dispute_canceled && $isMyDispute) {?>
	<script>
		$(function(){
			$('.textcounter-dispute_notice').textcounter({
				countDown: true,
				countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
				countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
			});
		});

		function modalFormCallBack(form){
			var $form = $(form);
			$.ajax({
				dataType: "JSON",
				type: "POST",
				url: "<?php echo __SITE_URL ?>dispute/ajax_operation/add_notice",
				data: $form.serialize(),
				beforeSend: function(){
					showLoader($form);
				},
				success: function (json) {
					if(json.mess_type != 'error'){
						if($('.user-dispute-notices ul').length == 0){
							$('.user-dispute-notices').html('<ul></ul>');
						}

						var template = '<li>\
											<div class="ttl-b clearfix">\
												<div class="name-b pull-left">by <strong>' + json.add_by + '</strong>: ' + json.title + '</div>\
												<div class="date-b pull-right">' + json.add_date + '</div>\
											</div>\
											<p>' + json.notice + '</p>\
										</li>';
						$('.user-dispute-notices ul').prepend(template);
						$form[0].reset();
					}
					systemMessages(json.message, 'message-' + json.mess_type);

					hideLoader($form);
				},
			});
		}
	</script>
<?php }?>
