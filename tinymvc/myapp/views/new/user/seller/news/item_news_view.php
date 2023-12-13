<?php foreach ($news as $item) {?>
	<li
        class="spersonal-news__item"
        id="news-<?php echo $item['id_news'];?>"
        <?php echo addQaUniqueIdentifier('page__company-news__list-item'); ?>
    >
		<div class="spersonal-news__top">
			<div class="spersonal-news__date" <?php echo addQaUniqueIdentifier('page__company-news__list_date'); ?>>
				<?php echo formatDate($item['date_news'], 'F j, Y');?>
			</div>

			<div class="dropdown">
				<a
                    class="dropdown-toggle"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                    href="#"
                    <?php echo addQaUniqueIdentifier('page__company-news__list_actions-dropdown-btn'); ?>
                >
					<i class="ep-icon ep-icon_menu-circles"></i>
				</a>
				<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <?php if (logged_in() && $seller_view && have_right('have_news')) {?>
                        <button
                            class="dropdown-item fancybox.ajax fancyboxValidateModal"
                            data-fancybox-href="seller_news/popup_forms/edit_news_form/<?php echo $item['id_news'];?>"
                            data-title="<?php echo translate('general_button_edit_text', null, true);?>"
                            title="<?php echo translate('general_button_edit_text', null, true);?>"
                            type="button"
                            <?php echo addQaUniqueIdentifier('page__company-news__list_actions-dropdown-menu_edit-btn'); ?>
                        >
                            <i class="ep-icon ep-icon_pencil"></i> <?php echo translate('general_button_edit_text');?>
                        </button>
                    <?php }?>

                    <button
                        class="dropdown-item <?php echo logged_in() ? 'fancyboxValidateModal fancybox.ajax' : 'js-require-logged-systmess';?>"
                        <?php echo logged_in() ? "data-fancybox-href=\"seller_news/popup_forms/share/{$item['id_news']}\"" : '';?>
                        <?php echo logged_in() ? 'data-title="' . translate('seller_updates_share_with_followers_text', null, true) . '"' : 'title="' . translate('seller_news_share_word', null, true) . '"';?>
                        type="button"
                        <?php echo addQaUniqueIdentifier('page__company-news__list_actions-dropdown-menu_share-btn'); ?>
                    >
                        <i class="ep-icon ep-icon_share-stroke"></i> <?php echo translate('seller_news_share_word');?>
                    </button>

                    <button
                        class="dropdown-item <?php echo logged_in() ? 'fancyboxValidateModal fancybox.ajax' : 'js-require-logged-systmess';?>"
                        <?php echo logged_in() ? "data-fancybox-href=\"seller_news/popup_forms/email/{$item['id_news']}\"" : '';?>
                        <?php echo logged_in() ? 'data-title="Email this"' : 'title="' . translate('seller_news_email_word', null, true) . '"';?>
                        data-title="<?php echo translate('seller_pictures_email_this_message', null, true);?>"
                        type="button"
                        <?php echo addQaUniqueIdentifier('page__company-news__list_actions-dropdown-menu_email-btn'); ?>
                    >
                        <i class="ep-icon ep-icon_envelope-send"></i> <span><?php echo translate('seller_news_email_word');?></span>
                    </button>

                    <a
                        class="dropdown-item"
                        href="<?php echo $base_company_url . '/view_news/' . strForURL($item['title_news']) . '-' . $item['id_news'] . '#news_comments';?>"
                    >
                        <i class="ep-icon ep-icon_comment "></i> <?php echo translate('general_comments_word');?> (<span <?php echo addQaUniqueIdentifier('page__company-news__list_actions-dropdown-menu_comments-btn'); ?>><?php echo $item['comments_count'];?></span>)
                    </a>

                    <a
                        class="dropdown-item"
                        href="<?php echo $base_company_url . '/view_news/' . strForURL($item['title_news']) . '-' . $item['id_news'];?>"
                        <?php echo addQaUniqueIdentifier('page__company-news__list_actions-dropdown-menu_detail-btn'); ?>
                    >
                        <i class="ep-icon ep-icon_info-stroke"></i>
                        <span><?php echo translate('general_detail_word');?></span>
                    </a>

                    <button
                        class="dropdown-item fancyboxValidateModal fancybox.ajax"
                        href="<?php echo __SITE_URL; ?>complains/popup_forms/add_complain/company_news/<?php echo $item['id_news']; ?>/<?php echo $item['id_seller']; ?>/<?php echo $item['id_company']; ?>"
                        data-title="<?php echo translate('general_button_report_news', null, true); ?>"
                        type="button"
                        <?php echo addQaUniqueIdentifier('page__company-news__list_actions-dropdown-menu_report-btn'); ?>
                    >
                        <i class="ep-icon ep-icon_warning-circle-stroke"></i><span class="txt"><?php echo translate('seller_news_report_word'); ?></span>
                    </button>
				</div>
			</div>
		</div>

		<div class="spersonal-news__txt flex-card ep-tinymce-text">
			<?php if ($item['image_news'] != '') {?>
				<div class="flex-card__fixed spersonal-news__img image-card3">
					<a
                        class="link"
                        href="<?php echo $base_company_url . '/view_news/' . strForURL($item['title_news']) . '-' . $item['id_news'];?>"
                    >
						<img
                            class="image"
                            src="<?php echo $item['imageThumbLink'];?>"
                            alt="<?php echo $item['title_news'];?>"
                            <?php echo addQaUniqueIdentifier('page__company-news__list_image'); ?>
                        />
					</a>
				</div>
			<?php }?>

			<div class="flex-card__float">
				<h3 class="spersonal-news__ttl">
					<a
                        class="link"
                        href="<?php echo $base_company_url . '/view_news/' . strForURL($item['title_news']) . '-' . $item['id_news'];?>"
                        <?php echo addQaUniqueIdentifier('page__company-news__list_title'); ?>
                    >
                        <?php echo $item['title_news'];?>
                    </a>
				</h3>
				<div class="spersonal-news__desc" <?php echo addQaUniqueIdentifier('page__company-news__list_description'); ?>>
					<?php echo strLimit(strip_tags($item['text_news']), 250);?>
				</div>
			</div>
		</div>
	</li>
<?php }?>
