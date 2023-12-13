<?php if(!empty($comments)){?>
	<?php foreach ($comments as $item) {?>
		<li
            class="spersonal-pic-comments__item"
            id="comment-<?php echo $item['id_comment']?>"
        >
			<div class="flex-card" <?php echo addQaUniqueIdentifier('global__comment'); ?>>
				<div class="spersonal-pic-comments__img image-card2 flex-card__fixed">
					<span class="link">
						<img
                            class="image"
                            src="<?php echo getDisplayImageLink(array('{ID}' => $item['id_user'], '{FILE_NAME}' => $item['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $item['user_group'] ));?>"
                            alt="<?php echo $item['fname'];?> <?php echo $item['lname'];?>"
                            <?php echo addQaUniqueIdentifier('global__comment-image'); ?>
                        />
					</span>
				</div>

				<div class="spersonal-pic-comments__detail flex-card__float">
					<div class="spersonal-pic-comments__name">
						<a
                            class="link"
                            href="<?php echo __SITE_URL . 'usr/' . $item['fname'] . '-' . $item['id_user'];?>"
                            target="_blank"
                            <?php echo addQaUniqueIdentifier('global__comment-name'); ?>
                        >
                            <?php echo $item['fname'] . ' ' . $item['lname'];?>
                        </a>
					</div>

					<div class="spersonal-pic-comments__text" <?php echo addQaUniqueIdentifier('global__comment-text'); ?>>
                        <?php echo $item['text_comment'];?>
                    </div>

					<div class="spersonal-pic-comments__bottom">
						<div class="spersonal-pic-comments__date" <?php echo addQaUniqueIdentifier('global__comment-date'); ?>>
                            <?php echo formatDate($item['date_comment']);?>
                        </div>
						<?php
							$_can_edit_news_comment = is_privileged('user', $item['id_user'], true);
							$_can_delete_news_comment = $_can_edit_news_comment || have_right('moderate_content');
							$_can_moderate_news_comment = have_right('moderate_content') && $item['moderated'] == 0;
							$_can_report_news_comment = !$_can_edit_news_comment;
						?>
						<?php if($_can_edit_news_comment || $_can_moderate_news_comment || $_can_report_news_comment){?>
							<div class="dropdown">
								<a
                                    class="dropdown-toggle"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    href="#"
                                    <?php echo addQaUniqueIdentifier('page__company-news-detail__comments-actions_dropdown-btn'); ?>
                                >
									<i class="ep-icon ep-icon_menu-circles"></i>
								</a>

								<div class="dropdown-menu">
									<?php if($_can_edit_news_comment){?>
										<a
                                            class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                            data-title="<?php echo translate('general_button_edit_text', null, true);?>"
                                            href="<?php echo __SITE_URL?>seller_news/popup_forms/edit_comment_form/<?php echo $item['id_comment']?>"
                                            <?php echo addQaUniqueIdentifier('page__company-news-detail__comments-actions_dropdown-menu_edit-btn'); ?>
                                        >
											<i class="ep-icon ep-icon_pencil"></i><span class="txt"><?php echo translate('general_button_edit_text');?></span>
										</a>
									<?php } ?>

									<?php if($_can_delete_news_comment){?>
										<a
                                            class="dropdown-item confirm-dialog"
                                            data-callback="remove_comment_seller_news"
                                            data-message="<?php echo translate('seller_news_delete_comment_question', null, true);?>"
                                            data-comment="<?php echo $item['id_comment'];?>"
                                            href="#"
                                            title="<?php echo translate('general_button_delete_text', null, true);?>"
                                            <?php echo addQaUniqueIdentifier('page__company-news-detail__comments-actions_dropdown-menu_delete-btn'); ?>
                                        >
											<i class="ep-icon ep-icon_trash-stroke"></i><span class="txt"><?php echo translate('general_button_delete_text');?></span>
										</a>
									<?php }?>

									<?php if($_can_moderate_news_comment){?>
										<a
                                            class="dropdown-item confirm-dialog"
                                            data-callback="moderate_comment_seller_news"
                                            data-message="<?php echo translate('seller_news_moderate_comment_question', null, true);?>"
                                            data-comment="<?php echo $item['id_comment'];?>"
                                            href="#"
                                            title="<?php echo translate('seller_news_moderate_comment_text', null, true);?>"
                                            <?php echo addQaUniqueIdentifier('page__company-news-detail__comments-actions_dropdown-menu_moderate-btn'); ?>
                                        >
											<i class="ep-icon ep-icon_sheild-ok"></i><span class="txt"><?php echo translate('general_button_moderate_text');?></span>
										</a>
									<?php }?>

									<?php if($_can_report_news_comment){?>
										<a
                                            class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                            href="<?php echo __SITE_URL; ?>complains/popup_forms/add_complain/seller_news_comment/<?php echo $item['id_comment']; ?>/<?php echo $item['id_user'];?>"
                                            data-title="<?php echo translate('general_button_report_comment', null, true);?>"
                                            <?php echo addQaUniqueIdentifier('page__company-news-detail__comments-actions_dropdown-menu_report-btn'); ?>
                                        >
											<span class="ep-icon ep-icon_warning-circle-stroke"></span><span class="txt"><?php echo translate('seller_news_report_word');?></span>
										</a>
									<?php }?>
								</div>
							</div>
						<?php }?>
					</div>
				</div>
			</div>
		</li>
	<?php } ?>
<?php }else{?>
	<li class="default-alert-b">
		<i class="ep-icon ep-icon_remove-circle"></i> <span><?php echo translate('seller_news_add_comment_text');?></span>
	</li>
<?php } ?>
