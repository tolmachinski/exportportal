<script type="text/javascript">
<?php if(logged_in() && have_right('moderate_content')){?>
moderate_comment = function(obj){
	var $this = $(obj);
	var comment = $this.data('comment');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>seller_pictures/ajax_pictures_operation/moderate_comment',
		data: { comment: comment},
		beforeSend: function(){ },
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, resp.mess_type );

			if(resp.mess_type == 'success'){
				$this.parent('.ep-actions').remove();
			}
		}
	});
}

censored_comment = function(obj){
	var $this = $(obj);
	var comment = $this.data('comment');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>seller_pictures/ajax_pictures_operation/censor_comment',
		data: { comment: comment},
		beforeSend: function(){ },
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, resp.mess_type );

			if(resp.mess_type == 'success'){
				if(resp.parent == 0){
					$('#comment-'+resp.comment).find('.spersonal-pic-comments__ttl').first().text('<?php echo translate('general_censored_word', null, true);?>');
					$('#comment-'+resp.comment).find('.spersonal-pic-comments__text').first().text('<?php echo translate('general_censored_word', null, true);?>');
				}else{
					$('#comment-'+resp.comment).find('.spersonal-pic-comments__res-ttl').first().text('<?php echo translate('general_censored_word', null, true);?>');
					$('#comment-'+resp.comment).find('.spersonal-pic-comments__res-text').first().text('<?php echo translate('general_censored_word', null, true);?>');
				}
				$this.parent('.ep-actions').remove();
			}
		}
	});
}
<?php }?>
</script>

<ul class="spersonal-pic-comments" id="parrent-0">
	<?php if(!empty($comments)){
		tmvc::instance()->controller->view->display('new/user/seller/pictures/comments_items_view');
	 }else{?>
		<li class="no-comments"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <?php echo translate('seller_pictures_no_comments_yet_text');?></div></li>
	<?php }?>
</ul>
