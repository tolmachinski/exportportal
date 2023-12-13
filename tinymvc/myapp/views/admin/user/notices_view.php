<form class="validateModal relative-b" id="add-notice">
	<div class="wr-form-content w-700 mh-400">
		<?php if(!empty($notices)){?>
			<ul class="list-group mt-15">
				<?php foreach($notices as $notice){?>
					<?php if(!empty($notice)){?>
						<li class="list-group-item">
							<strong><?php echo $notice['add_date'] ?></strong> - <u>by <?php echo $notice['add_by'] ?></u> : <?php echo $notice['notice'] ?>
						</li>
					<?php }?>
				<?php }?>
			</ul>
		<?php }else{ ?>
		<div class="info-alert-b">
			<i class="ep-icon ep-icon_info"></i>
			<strong>This user does not have any notices.</strong>
		</div>
		<?php } ?>
		<textarea name="notice" class="validate[required] w-100pr h-100" placeholder="Notice"></textarea>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="user" value="<?php echo $iduser?>" />
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Add notice</button>
	</div>
</form>
<script>
function modalFormCallBack(form){
	var $form = $(form);
		$.ajax( {
			"dataType": "JSON",
			"type": "POST",
			"url": "<?php echo __SITE_URL ?>users/ajax_add_notice",
			"data": $form.serialize(),
			"beforeSend": function(){
				showLoader(form);
			},
			"success": function (json) {
				if(json.mess_type != 'error'){
					if($('.user-notices ul').length == 0){
						$('.user-notices').html('<ul></ul>');
					}
					$('.user-notices ul').prepend(json.content);
					$form[0].reset();
				}
				systemMessages(json.message, 'message-' + json.mess_type);
				hideLoader(form);
			},

		});
}
</script>
