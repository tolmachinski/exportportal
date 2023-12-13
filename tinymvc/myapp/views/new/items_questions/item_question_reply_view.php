<li class="product-comments__item" id="li-question-reply-<?php echo $question_reply['id_q']?>">
	<div class="flex-card">
		<div class="product-comments__detail flex-card__float">
			<div class="product-comments__ttl">
				<span class="product-comments__reply-ttl">
                    <?php echo translate('item_question_replied_by', array('{{USER_GROUP}}' => '<a class="product-comments__name-link" ' . addQaUniqueIdentifier("global__question-name") . ' href="' . __SITE_URL . 'usr/' . strForURL($question_reply['sell_fullname']) . '-' . $question_reply['id_seller'] . '" target="_blank">' . translate('user_group_seller', null, true) . '</a>'));?>
				</span>

				<span class="product-comments__date" <?php echo addQaUniqueIdentifier("global__question-date")?>><?php echo getDateFormat($question_reply['reply_date'], null, 'M d, Y');?></span>
			</div>
			<div class="product-comments__text" <?php echo addQaUniqueIdentifier("global__question-text")?>><?php echo $question_reply['reply'];?></div>

			<div class="product-comments__actions">
                <div class="did-help <?php echo isset($helpful[$question_reply['id_q']]) ? 'rate-didhelp' : '';?>">
                    <div class="did-help__txt"><?php echo translate('seller_item_question_reply_did_help_text');?></div>
                    <?php
                        $disabled_class = $question_reply['id_seller'] == id_session() ? ' disabled' : '';
                        $event_listener_class = logged_in() ? 'js-didhelp-btn call-action' : 'js-require-logged-systmess';
                        $isset_my_helpful_question_reply = isset($helpful[$question_reply['id_q']]);

                        $btn_count_plus_class = ($isset_my_helpful_question_reply && $helpful[$question_reply['id_q']] == 1) ? ' txt-blue2' : '';
                        $btn_count_minus_class = ($isset_my_helpful_question_reply && $helpful[$question_reply['id_q']] == 0) ? ' txt-blue2' : '';
                    ?>

                    <span class="i-up didhelp-btn <?php echo $event_listener_class . $disabled_class;?>"
                        data-item="<?php echo $question_reply['id_q']?>"
                        data-page="items_questions"
                        data-type="question"
                        data-action="y"
                        data-js-action="did-help:click"
                    >
                        <span class="counter-b js-counter-plus" <?php echo addQaUniqueIdentifier("global__question-counter")?>><?php echo $question_reply['count_plus']?></span>
                        <span class="ep-icon ep-icon_arrow-line-up js-arrow-up<?php echo $btn_count_plus_class;?>"></span>
                    </span>
                    <span class="i-down didhelp-btn <?php echo $event_listener_class . $disabled_class;?>"
                        data-item="<?php echo $question_reply['id_q']?>"
                        data-page="items_questions"
                        data-type="question"
                        data-action="n"
                        data-js-action="did-help:click"
                    >
                        <span class="counter-b js-counter-minus" <?php echo addQaUniqueIdentifier("global__question-counter")?>><?php echo $question_reply['count_minus']?></span>
                        <span class="ep-icon ep-icon_arrow-line-down js-arrow-down<?php echo $btn_count_minus_class?>"></span>
                    </span>
                </div>

                <?php $can_edit = (is_privileged('user', $question_reply['id_seller'], 'reply_questions')) && $question_reply['status'] != 'moderated'; ?>
                <?php $can_report = !is_privileged('user', $question_reply['id_seller'], 'reply_questions'); ?>
				<?php if (logged_in() && ($can_edit || $can_report)) {?>
                    <div class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                            <i class="ep-icon ep-icon_menu-circles"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                            <?php if ($can_edit) {?>
                                <a class="dropdown-item fancyboxValidateModal fancybox.ajax"
                                    data-fancybox-href="<?php echo __SITE_URL . 'items_questions/popup_forms/leave_reply_to_item_question/' . $question_reply['id_q'];?>"
                                    data-title="<?php echo translate('seller_item_question_reply_actions_edit_btn_tag_title', null, true);?>"
                                    title="<?php echo translate('seller_item_question_reply_actions_edit_btn_tag_title', null, true);?>">
                                    <i class="ep-icon ep-icon_pencil"></i><?php echo translate('seller_item_question_reply_actions_edit_btn');?>
                                </a>
                            <?php }?>

                            <?php if ($can_report) {?>
                                <a class="dropdown-item fancyboxValidateModal fancybox.ajax"
                                    data-fancybox-href="<?php echo __SITE_URL . 'complains/popup_forms/add_complain/item_questions/' . $question_reply['id_q'] . '/' . $question_reply['id_seller'];?>"
                                    data-title="<?php echo translate('seller_item_question_reply_actions_report_btn_tag_title', null, true);?>">
                                    <i class="ep-icon ep-icon_warning-circle-stroke"></i><?php echo translate('seller_item_question_reply_actions_report_btn');?>
                                </a>
                            <?php }?>
                        </div>
                    </div>
				<?php }?>
			</div>
		</div>
	</div>
</li>
