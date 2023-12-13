<ul class="spersonal-pic-comments" id="parrent-0">
	<?php if (!empty($comments)) {
		views()->display('new/user/seller/videos/comments_items_view');
	} else { ?>
		<li class="no-comments"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <?php echo translate('seller_videos_no_comments_yet_text')?></div></li>
	<?php } ?>
</ul>
