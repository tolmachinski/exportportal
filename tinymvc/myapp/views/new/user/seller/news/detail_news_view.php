<script type="text/javascript">
$(document).ready(function () {
	$(".comments-btn").click(function (e) {
		e.preventDefault();
		scrollToElement("#news_comments");
	});
});

<?php if (logged_in() && have_right('moderate_content') && !$news['moderated']) { ?>
	var moderate_seller_news = function(obj){
		var $this = $(obj);
		var news = $this.data('news');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>seller_news/ajax_news_operations/moderate_news',
			data: { news : news},
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, data.mess_type );

				if(data.mess_type == 'success'){
					$this.remove();
				}
			}
		});
	}
<?php } ?>

<?php if (logged_in() || have_right('moderate_content')) { ?>
	var remove_comment_seller_news = function(obj){
		var $this = $(obj);
		var comment = $this.data('comment');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>seller_news/ajax_news_operations/delete_comment',
			data: { comment : comment},
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, data.mess_type );

				if(data.mess_type == 'success'){
					$this.closest('li').fadeOut('normal', function(){
						$(this).remove();

						if($('#news_comments_list .spersonal-pic-comments__item').length < 1){
							$('#news_comments_list').html('<li class="default-alert-b"><i class="ep-icon ep-icon_remove-circle"></i> <span><?php echo translate('seller_news_add_comment_text', null, true); ?></span></li>');
						}
					});

				}
			}
		});
	}
<?php } ?>

<?php if (logged_in() && have_right('moderate_content')) { ?>
	var moderate_comment_seller_news = function(obj){
		var $this = $(obj);
		var comment = $this.data('comment');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL . 'seller_news/ajax_news_operations/moderate_comment';?>',
			data: { comment : comment},
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, data.mess_type );

				if(data.mess_type == 'success'){
					$this.remove();
				}
			}
		});
	}
<?php } ?>
</script>
<div class="spersonal-news-detail">
	<div class="title-public pt-0">
		<h1 class="title-public__txt"><?php echo $news['title_news']; ?></h1>
	</div>

	<div class="flex-display flex-jc--sb">
		<div class="spersonal-news-detail__date" <?php echo addQaUniqueIdentifier('page__company-news-detail__date'); ?>><?php echo formatDate($news['date_news'], 'F j, Y'); ?></div>

		<div class="dropdown">
			<a
                class="dropdown-toggle"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
                href="#"
                <?php echo addQaUniqueIdentifier('page__company-news-detail__actions-dropdown-btn'); ?>
            >
				<i class="ep-icon ep-icon_menu-circles"></i>
			</a>

			<div class="dropdown-menu dropdown-menu-right">
				<?php if (logged_in() && have_right('moderate_content') && !$news['moderated']) { ?>
					<a
                        class="dropdown-item confirm-dialog"
                        data-callback="moderate_seller_news"
                        data-message="<?php echo translate('seller_news_comment_moderated_question', null, true); ?>"
                        data-news="<?php echo $news['id_news'];?>"
                        href="#"
                        <?php echo addQaUniqueIdentifier('page__company-news-detail__actions-dropdown-menu_moderate-btn'); ?>
                    >
						<i class="ep-icon ep-icon_sheild-ok"></i><span class="txt"><?php echo translate('seller_news_moderate_word'); ?></span>
					</a>
				<?php } ?>
				<a
                    class="dropdown-item comments-btn"
                    href="#"
                    <?php echo addQaUniqueIdentifier('page__company-news-detail__actions-dropdown-menu_comments-btn'); ?>
                >
					<i class="ep-icon ep-icon_comment"></i>
                    <span class="txt">
                        <?php echo translate('general_comments_word'); ?> (<span <?php echo addQaUniqueIdentifier('page__company-news-detail__actions-dropdown-menu_comments-count'); ?>><?php echo $news['comments_count']; ?></span>)
                    </span>
				</a>
				<a
                    class="dropdown-item fancyboxValidateModal fancybox.ajax"
                    href="<?php echo __SITE_URL; ?>seller_news/popup_forms/email/<?php echo $news['id_news']; ?>"
                    data-title="<?php echo translate('general_email_this_text', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('page__company-news-detail__actions-dropdown-menu_email-btn'); ?>
                >
					<i class="ep-icon ep-icon_envelope-send"></i><span class="txt"><?php echo translate('seller_news_email_word'); ?></span>
				</a>
				<a
                    class="dropdown-item fancyboxValidateModal fancybox.ajax"
                    href="<?php echo __SITE_URL; ?>seller_news/popup_forms/share/<?php echo $news['id_news']; ?>"
                    data-title="<?php echo translate('general_share_news_text', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('page__company-news-detail__actions-dropdown-menu_share-btn'); ?>
                >
					<i class="ep-icon ep-icon_share-stroke"></i><span class="txt"><?php echo translate('seller_news_share_word'); ?></span>
				</a>
				<a
                    class="dropdown-item fancyboxValidateModal fancybox.ajax"
                    href="<?php echo __SITE_URL; ?>complains/popup_forms/add_complain/company_news/<?php echo $news['id_news']; ?>/<?php echo $news['id_seller']; ?>/<?php echo $news['id_company']; ?>"
                    data-title="<?php echo translate('general_button_report_news', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('page__company-news-detail__actions-dropdown-menu_report-btn'); ?>
                >
					<i class="ep-icon ep-icon_warning-circle-stroke"></i><span class="txt"><?php echo translate('seller_news_report_word'); ?></span>
				</a>
			</div>
		</div>
	</div>
	<?php if ($news['image_news'] != '') { ?>
		<div class="spersonal-news-detail__img">
			<img
                class="image"
                src="<?php echo $imageLink; ?>" alt="<?php echo $news['title_news']; ?>"
                <?php echo addQaUniqueIdentifier('page__company-news-detail__image'); ?>
            />
		</div>
	<?php } ?>

	<div class="ep-middle-text" <?php echo addQaUniqueIdentifier('page__company-news-detail__description'); ?>>
		<?php echo $news['text_news']; ?>
	</div>
</div>
<div id="news_comments" class="pt-35"></div>

<div class="title-public pt-0">
	<h2 class="title-public__txt"><?php echo translate('general_comments_word'); ?></h2>

	<?php if (logged_in()) { ?>
		<div class="dropdown">
			<a
                class="dropdown-toggle"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
                href="#"
                <?php echo addQaUniqueIdentifier('page__company-news-detail__comments-dropdown-btn'); ?>
            >
				<i class="ep-icon ep-icon_menu-circles"></i>
			</a>

			<div class="dropdown-menu">
				<a
                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                    href="<?php echo __SITE_URL . 'seller_news/popup_forms/add_comment_form/' . $news['id_news'];?>"
                    data-title="<?php echo translate('general_button_add_comment_text', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('page__company-news-detail__comments-dropdown-menu_add-btn'); ?>
                >
					<i class="ep-icon ep-icon_pencil"></i> <?php echo translate('seller_news_leave_comment_text'); ?>
				</a>
			</div>
		</div>
	<?php } ?>
</div>

<ul
    class="spersonal-news-comments"
    id="news_comments_list"
    <?php echo addQaUniqueIdentifier('page__company-news-detail__comments_list'); ?>
>
	<?php views()->display('new/user/seller/news/comments_news_view'); ?>
</ul>
