<li
    id="li-feed-reply-<?php echo $feedback['id_feedback']?>"
    class="product-comments__item"
    <?php echo addQaUniqueIdentifier('global__company-feedbacks-reply__item'); ?>
>
	<div class="flex-card">
		<div class="product-comments__detail flex-card__float">
			<div class="product-comments__ttl">
				<span class="product-comments__reply-ttl">
					<?php echo translate('user_feedback_replied_by', array(
                        '{{USER_GROUP}}' => '<a
                                                class="product-comments__name-link"
                                                href="' . __SITE_URL . 'usr/' . strForURL($feedback['user']['fname'] . ' ' . $feedback['user']['lname']) . '-' . $feedback['user']['idu'] . '"
                                                target="_blank"'
                                                . addQaUniqueIdentifier('global__company-feedbacks-reply__item-user-name') .
                                            '>' . ($feedback['poster_group'] == 'Buyer' ? translate('user_group_seller') : translate('user_group_buyer')) . '</a>'));?>
				</span>

				<span
                    class="product-comments__date"
                    <?php echo addQaUniqueIdentifier('global__company-feedbacks-reply__item-date'); ?>
                ><?php echo getDateFormat($feedback['reply_date'], null, 'M d, Y');?></span>
			</div>

			<div
                class="product-comments__text"
                <?php echo addQaUniqueIdentifier('global__company-feedbacks-reply__item-text'); ?>
            ><?php echo $feedback['reply_text'];?></div>

			<div class="product-comments__actions">
				<div class="did-help <?php echo isset($helpful_feedbacks[$feedback['id_feedback']]) ? 'rate-didhelp' : '';?>">
					<div class="did-help__txt"><?php echo translate('community_questions_answer_help_text');?></div>
					<?php
						$disabled_class = $feedback['id_user'] == id_session() ? ' disabled' : '';
						$isset_my_helpful_feedback = isset($helpful_feedbacks[$feedback['id_feedback']]);

						$btn_count_plus_class = ($isset_my_helpful_feedback && $helpful_feedbacks[$feedback['id_feedback']] == 1) ? ' txt-blue2' : '';
						$btn_count_minus_class = ($isset_my_helpful_feedback && $helpful_feedbacks[$feedback['id_feedback']] == 0) ? ' txt-blue2' : '';
					?>
					<span
						class="i-up didhelp-btn <?php echo logged_in() ? 'js-didhelp-btn' . $disabled_class : 'js-require-logged-systmess';?>"
						data-item="<?php echo $feedback['id_feedback']?>"
						data-page="feedbacks"
						data-type="feedback"
						data-action="y"
                        <?php echo addQaUniqueIdentifier('global__company-feedbacks-reply__item-help-btn-yes'); ?>
                    >
						<span
                            class="counter-b js-counter-plus"
                            <?php echo addQaUniqueIdentifier('global__company-feedbacks-reply__item-help-counter'); ?>
                        ><?php echo $feedback['count_plus']?></span>
						<span class="ep-icon ep-icon_arrow-line-up js-arrow-up<?php echo $btn_count_plus_class;?>"></span>
					</span>
					<span
						class="i-down didhelp-btn <?php echo logged_in() ? 'js-didhelp-btn' . $disabled_class : 'js-require-logged-systmess';?>"
						data-item="<?php echo $feedback['id_feedback']?>"
						data-page="feedbacks"
						data-type="feedback"
						data-action="n"
                        <?php echo addQaUniqueIdentifier('global__company-feedbacks-reply__item-help-btn-no'); ?>
                    >
						<span
                            class="counter-b js-counter-minus"
                            <?php echo addQaUniqueIdentifier('global__company-feedbacks-reply__item-help-counter'); ?>
                        ><?php echo $feedback['count_minus']?></span>
						<span class="ep-icon ep-icon_arrow-line-down js-arrow-down<?php echo $btn_count_minus_class?>"></span>
					</span>
				</div>

				<?php if (is_privileged('user', $feedback['id_user'], 'leave_feedback') && $feedback['status']=='new'){?>
					<div class="dropdown">
						<a
                            class="dropdown-toggle"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                            href="#"
                            <?php echo addQaUniqueIdentifier('global__company-feedbacks-reply__item_dropdown-btn'); ?>
                        >
							<i class="ep-icon ep-icon_menu-circles"></i>
						</a>
						<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
							<a
                                id="btnreplyfeed-<?php echo $feedback['id_feedback']?>-edit"
                                class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                data-title="<?php echo translate('user_feedback_edit_reply_btn_tag_title', null, true);?>"
                                title="<?php echo translate('user_feedback_edit_reply_btn_tag_title', null, true);?>"
                                href="<?php echo __SITE_URL . 'feedbacks/popup_forms/edit_reply/' . $feedback['id_feedback']?>"
                                <?php echo addQaUniqueIdentifier('global__company-feedbacks-reply__item_dropdown-menu_edit-btn'); ?>
                            ><i class="ep-icon ep-icon_pencil"></i><?php echo translate('user_feedback_edit_reply_btn');?></a>
						</div>
					</div>
				<?php }?>
			</div>
		</div>
	</div>
</li>
