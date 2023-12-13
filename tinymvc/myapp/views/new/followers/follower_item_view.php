<?php
$user = 'id_user';
$text = translate('seller_followers_following_action_label');

if ($type == 'followers') {
	$user = 'id_user_follower';
	$text = translate('seller_followers_following_action_label');
}

$i_follow = i_follow();
?>

<?php foreach ($followers as $key => $follower) { ?>
	<li class="ppersonal-followers__item flex-card" <?php echo addQaUniqueIdentifier('global__item__followers-block') ?>>
		<div class="ppersonal-followers__img image-card2 flex-card__fixed">
			<span class="link">
				<img class="image"
                <?php echo addQaUniqueIdentifier('global__item__followers-block_image') ?>
                src="<?php echo getDisplayImageLink(array('{ID}' => $follower[$user], '{FILE_NAME}' => $follower['user_photo']), 'users.main', array('thumb_size' => 0, 'no_image_group' => $follower['user_group'])); ?>" alt="<?php echo $follower['user_name'] ?>" />
			</span>
		</div>
		<div class="ppersonal-followers__detail flex-card__float">
			<div class="ppersonal-followers__name">
				<a class="link"
                <?php echo addQaUniqueIdentifier('global__item__followers-block_name') ?>
                 href="<?php echo __SITE_URL . 'usr/' . strForURL($follower['fname'] . ' ' . $follower['lname'] . ' ' . $follower[$user]); ?>"><?php echo $follower['user_name'] ?></a>
			</div>

			<div class="ppersonal-followers__group<?php echo userGroupNameColor($follower['gr_name']); ?>" <?php echo addQaUniqueIdentifier('global__item__followers-block_group') ?>>
				<?php echo $follower['gr_name'] ?>
			</div>

			<div class="ppersonal-followers__bottom">
				<div class="ppersonal-followers__date" <?php echo addQaUniqueIdentifier('global__item__followers-block_date') ?>><?php echo  $text . ' ' . getDateFormat($follower['date_follow'], null, 'j M, Y'); ?></div>

				<div class="dropdown">
					<a class="dropdown-toggle" <?php echo addQaUniqueIdentifier('global__item__followers-block_dropdown-btn') ?> href="#" data-toggle="dropdown">
						<i class="ep-icon ep-icon_menu-circles"></i>
					</a>
					<div class="dropdown-menu dropdown-menu-right">
						<?php if (logged_in()) { ?>
							<?php if (in_array($follower[$user], $i_follow)) { ?>
								<button
                                    class="dropdown-item call-function"
                                    <?php echo addQaUniqueIdentifier('global__item__followers-block_dropdown_unfollow-btn') ?>
                                    data-user="<?php echo $follower[$user]; ?>"
                                    data-callback="unfollow_user"
                                    title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_unfollow_user', null, true); ?>"
                                    type="button"
                                >
									<i class="ep-icon ep-icon_reply-left-empty"></i>
									<span class="txt"><?php echo translate('seller_home_page_sidebar_menu_dropdown_unfollow_user'); ?></span>
								</button>
							<?php } else { ?>
								<button
                                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                    <?php echo addQaUniqueIdentifier('global__item__followers-block_dropdown_follow-btn') ?>
                                    data-fancybox-href="<?php echo __SITE_URL . 'followers/popup_followers/follow_user/' . $follower[$user]; ?>"
                                    data-title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_follow_user', null, true); ?>"
                                    data-user="<?php echo $follower[$user]; ?>"
                                    title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_follow_user', null, true); ?>"
                                    type="button"
                                >
									<i class="ep-icon ep-icon_reply-right-empty"></i>
									<span class="txt"><?php echo translate('seller_home_page_sidebar_menu_dropdown_follow_user'); ?></span>
								</button>
							<?php } ?>

							<?php if (have_right('email_this')) { ?>
								<button
                                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                    <?php echo addQaUniqueIdentifier('global__item__followers-block_dropdown_email-btn') ?>
                                    data-title="<?php echo translate('email_user_btn_tag_title', null, true); ?>"
                                    data-fancybox-href="<?php echo __SITE_URL . 'user/popup_forms/email_user/' . $follower[$user]; ?>"
                                    title="<?php echo translate('email_user_btn_tag_title', null, true); ?>"
                                    type="button"
                                >
									<i class="ep-icon ep-icon_envelope-send"></i>
									<span class="txt"><?php echo translate('email_user_btn'); ?></span>
								</button>
							<?php } ?>

							<?php if (have_right('share_users')) { ?>
								<button
                                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                    <?php echo addQaUniqueIdentifier('global__item__followers-block_dropdown_share-btn') ?>
                                    data-title="<?php echo translate('share_user_btn_tag_title', null, true); ?>"
                                    data-fancybox-href="<?php echo __SITE_URL . 'user/popup_forms/share_user/' . $follower[$user]; ?>"
                                    title="<?php echo translate('share_user_btn_tag_title', null, true); ?>"
                                    type="button"
                                >
									<i class="ep-icon ep-icon_share-stroke"></i>
									<span class="txt"><?php echo translate('share_user_btn'); ?></span>
								</button>
							<?php } ?>

							<?php echo !empty($follower['btnChat']) ? $follower['btnChat'] : ''; ?>

						<?php } else { ?>
							<button
                                class="dropdown-item js-require-logged-systmess"
                                title="<?php echo translate('email_user_btn_tag_title', null, true); ?>"
                                type="button"
                            >
								<i class="ep-icon ep-icon_envelope"></i>
								<span class="txt"><?php echo translate('email_user_btn'); ?></span>
							</button>
							<button
                                class="dropdown-item js-require-logged-systmess"
                                title="<?php echo translate('share_user_btn_tag_title', null, true); ?>"
                                type="button"
                            >
								<i class="ep-icon ep-icon_share-stroke"></i>
								<span class="txt"><?php echo translate('share_user_btn'); ?></span>
							</button>
							<button
                                class="dropdown-item js-require-logged-systmess"
                                title="<?php echo translate('contact_user_btn_tag_title', null, true); ?>"
                                type="button"
                            >
								<i class="ep-icon ep-icon_envelope"></i>
								<span class="txt"><?php echo translate('contact_user_btn'); ?></span>
							</button>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</li>
<?php } ?>
