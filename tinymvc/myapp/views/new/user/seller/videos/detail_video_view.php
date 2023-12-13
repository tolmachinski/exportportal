<a class="btn btn-primary btn-panel-left mb-25 fancyboxSidebar fancybox" data-title="<?php echo translate('mobile_screen_sidebar_btn', null, true);?>" href="#main-flex-card__fixed-left">
	<i class="ep-icon ep-icon_items"></i>
	<?php echo translate('mobile_screen_sidebar_btn');?>
</a>

<div class="hmedia">
	<div class="detail-info spersonal-pic-detail">
		<div class="title-public pt-0">
			<h1 class="title-public__txt" <?php echo addQaUniqueIdentifier('page__seller-videos__details-title'); ?>><?php echo $video['title_video'];?></h1>
		</div>

		<div class="spersonal-pic-detail__category" <?php echo addQaUniqueIdentifier('page__seller-videos__details-category'); ?>>
			<?php echo $video['category_title'];?>
		</div>

		<div class="spersonal-pic-detail__video">
			<?php echo generate_video_html($video['short_url_video'], $video['source_video'], '100%', 443, false); ?>
		</div>

		<div class="spersonal-pic-detail__txt" <?php echo addQaUniqueIdentifier('page__seller-videos__details-text'); ?>>
			<?php echo $video['description_video'];?>
		</div>
	</div>

	<div class="detail-info">
		<div class="title-public">
			<h2 class="title-public__txt"><?php echo translate('general_comments_word'); ?> (<span id="counter_comment" <?php echo addQaUniqueIdentifier('global__comment-counter'); ?>><?php echo $video['comments_count'];?></span>)</h2>

			<?php if (logged_in() && have_right('write_comments')) {?>
                <div class="dropdown">
                    <a
                        class="dropdown-toggle"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                        href="#"
                        <?php echo addQaUniqueIdentifier('page__seller-videos__comments_dropdown-btn'); ?>
                    >
                        <i class="ep-icon ep-icon_menu-circles"></i>
                    </a>

                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item fancybox.ajax fancyboxValidateModal"
                            data-fancybox-href="<?php echo __SITE_URL . 'seller_videos/popup_forms/add_comment/' . $video['id_video'];?>"
                            data-title="<?php echo translate('general_button_add_comment_text', null, true); ?>"
                            title="<?php echo translate('general_button_add_comment_text', null, true); ?>"
                            <?php echo addQaUniqueIdentifier('page__seller-videos__comments_dropdown-menu_leave-a-comment-btn'); ?>
                        >
                            <i class="ep-icon ep-icon_pencil"></i>
                            <?php echo translate('general_button_add_comment_text'); ?>
                        </a>
                    </div>
                </div>
			<?php }?>
		</div>

		<?php views()->display('new/user/seller/videos/list_comment_view'); ?>
	</div>

	<div class="title-public">
		<h2 class="title-public__txt"><?php echo translate('seller_videos_more_video_text'); ?> (<?php echo $count_videos;?>)</h2>
	</div>

	<?php views()->display('new/user/seller/videos/list_video_view'); ?>
</div>

<?php if(have_right('have_videos')) { ?>
	<script type="text/javascript">
		var callbackAddSellerVideos = function(response){
			_notifyContentChangeCallback();
		}

		var callbackEditSellerVideos = function(response){
			_notifyContentChangeCallback();
		}
	</script>
<?php } ?>
<?php if (have_right('write_comments')) { ?>
    <script type="text/javascript">
        var callbackAddVideoComment = function() {
            _notifyContentChangeCallback();
        };
        var callbackEditVideoComment = function() {
            _notifyContentChangeCallback();
        };
    </script>
<?php } ?>
<?php if (have_right('moderate_content')) { ?>
    <script type="text/javascript">
        var moderate_comment = function(obj){
            var $this = $(obj);
            var comment = $this.data('comment');

            $.ajax({
                type: 'POST',
                url: '<?php echo __SITE_URL . 'seller_videos/ajax_videos_operation/moderate_comment';?>',
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

        var censored_comment = function(obj){
            var $this = $(obj);
            var comment = $this.data('comment');

            $.ajax({
                type: 'POST',
                url: '<?php echo __SITE_URL . 'seller_videos/ajax_videos_operation/censor_comment'?>',
                data: { comment: comment },
                beforeSend: function(){ },
                dataType: 'json',
                success: function(resp){
                    systemMessages( resp.message, resp.mess_type );

                    if(resp.mess_type == 'success'){
                        if (resp.parent == 0) {
                            $('#comment-' + resp.comment).find('.spersonal-pic-comments__ttl').first().text('<?php echo translate('general_censored_word', null, true); ?>');
                            $('#comment-' + resp.comment).find('.spersonal-pic-comments__text').first().text('<?php echo translate('general_censored_word', null, true); ?>');
                        } else {
                            $('#comment-' + resp.comment).find('.spersonal-pic-comments__res-ttl').first().text('<?php echo translate('general_censored_word', null, true); ?>');
                            $('#comment-' + resp.comment).find('.spersonal-pic-comments__res-text').first().text('<?php echo translate('general_censored_word', null, true); ?>');
                        }

                        $this.parent('.ep-actions').remove();
                    }
                }
            });
        }
    </script>
<?php } ?>
