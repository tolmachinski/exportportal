<?php foreach($questions as $question_item) {?>
    <li class="product-comments__item" id="question-<?php echo $question_item['id_q']?>" <?php echo addQaUniqueIdentifier("global__question")?>>
        <div class="product-comments__object" <?php echo addQaUniqueIdentifier("global__question-title")?>><?php echo $question_item['title_question'];?></div>
        <div class="flex-card">
            <div class="product-comments__img flex-card__fixed image-card3">
                <span class="link">
                <?php if ($questions_user_info) {?>
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(75, 75);?>"
                        data-src="<?php echo getDisplayImageLink(array('{ID}' => $question_item['id_questioner'], '{FILE_NAME}' => $question_item['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $question_item['idgroup'] )); ?>"
                        alt="<?php echo $question_item['questionername'];?>"
                        <?php echo addQaUniqueIdentifier("global__question-image")?>
                    />
                <?php } else {?>
                    <?php
                        $item_img_link = getDisplayImageLink(array('{ID}' => $question_item['id_item'], '{FILE_NAME}' => $question_item['photo_name']), 'items.main', array( 'thumb_size' => 1 ));
                    ?>
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(56, 56);?>"
                        data-src="<?php echo $item_img_link; ?>"
                        alt="<?php echo $question_item['title'];?>"
                        <?php echo addQaUniqueIdentifier("global__question-image")?>
                    />
                <?php }?>
                </span>
            </div>
            <div class="product-comments__detail flex-card__float">
                <div class="product-comments__ttl">
                    <div class="w-100pr">
                        <div class="flex-display flex-jc--sb">
                            <div class="product-comments__name pt-20">
                                <a class="link" href="<?php echo __SITE_URL . 'usr/' . strForURL($question_item['questionername']) . '-' . $question_item['id_questioner'];?>" <?php echo $profile_in_blank ? 'target="_blank"' : '';?> <?php echo addQaUniqueIdentifier("global__question-name")?>><?php echo $question_item['questionername'];?></a>
                            </div>

                            <span class="product-comments__date pt-20" <?php echo addQaUniqueIdentifier("global__question-date")?>><?php echo getDateFormat($question_item['question_date'], null, 'M d, Y');?></span>
                        </div>

                        <div class="product-comments__type pt-10" <?php echo addQaUniqueIdentifier("global__question-type")?>><?php echo $question_item['name_category']?></div>
                    </div>
                </div>

                <?php if ((isset($about) && !$about) || (!isset($about))) {?>
                    <div class="flex-display flex-jc--sb pt-20">
                        <div class="">
                            <span class="txt-gray"><?php echo translate('seller_questions_about_item_label');?></span>
                            <a class="product-comments__name-link"
                                <?php echo addQaUniqueIdentifier("global__question-name")?>
                                href="<?php echo __SITE_URL . 'item/' . strForURL($question_item['title']) . '-' . $question_item['id_item'];?>"
                            >
                                <?php echo $question_item['title'];?>
                            </a>
                        </div>
                    </div>
                <?php } ?>

                <div class="product-comments__text" <?php echo addQaUniqueIdentifier("global__question-text")?>><?php echo $question_item['question'];?></div>

                    <div class="product-comments__actions">

                        <span class="product-comments__left"></span>
                        <?php if (logged_in()) {?>
                            <?php $is_not_moderated = $question_item['status'] != 'moderated'; ?>
                            <?php $can_moderate = $is_not_moderated && have_right('moderate_content'); ?>
                            <?php $can_edit = $is_not_moderated && is_my($question_item['id_questioner']) && empty($question_item['reply']);?>
                            <?php $can_delete = $can_edit || have_right('moderate_content');?>
                            <?php $can_reply = is_privileged('user', $question_item['id_seller'], 'reply_questions') && empty($question_item['reply']);?>
                            <?php $can_report = !is_my($question_item['id_questioner']); ?>

                            <?php if ($can_moderate || $can_delete || $can_edit || $can_reply || $can_report) {?>
                                <div class="dropdown">
                                    <a class="dropdown-toggle" <?php echo addQaUniqueIdentifier("items_questions-my__details_replied_dropdown-btn")?> data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                                        <i class="ep-icon ep-icon_menu-circles"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                        <?php if ($can_edit) {?>
                                            <a class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                                data-fancybox-href="<?php echo __SITE_URL . 'items_questions/popup_forms/edit_question/' . $question_item['id_q'];?>"
                                                data-title="<?php echo translate('seller_questions_actions_edit_btn_tag_title', null, true);?>"
                                                title="<?php echo translate('seller_questions_actions_edit_btn_tag_title', null, true);?>"
                                                target="_blank">
                                                <i class="ep-icon ep-icon_pencil"></i><?php echo translate('seller_questions_actions_edit_btn');?>
                                            </a>
                                        <?php }?>

                                        <?php if ($can_delete) {?>
                                            <a class="dropdown-item confirm-dialog"
                                                data-question="<?php echo $question_item['id_q']?>"
                                                data-callback="deleteQuestion"
                                                data-message="<?php echo translate('seller_questions_actions_delete_btn_confirm_msg', null, true);?>"
                                                data-href="<?php echo __SITE_URL . 'items_questions/ajax_question_operation/delete';?>"
                                                title="<?php echo translate('seller_questions_actions_delete_btn_tag_title', null, true);?>"
                                                target="_blank">
                                                <i class="ep-icon ep-icon_trash-stroke"></i><?php echo translate('seller_questions_actions_delete_btn');?>
                                            </a>
                                        <?php }?>

                                        <?php if ($can_moderate) {?>
                                            <a class="dropdown-item confirm-dialog"
                                                data-question="<?php echo $question_item['id_q']?>"
                                                data-message="<?php echo translate('seller_questions_actions_moderate_btn_confirm_msg', null, true);?>"
                                                data-callback="moderateQuestion"
                                                title="<?php echo translate('seller_questions_actions_moderate_btn_tag_title', null, true);?>">
                                                <i class="ep-icon ep-icon_sheild-ok"></i><?php echo translate('seller_questions_actions_moderate_btn');?>
                                            </a>
                                        <?php }?>

                                        <?php if ($can_reply) {?>
                                            <a class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                                data-fancybox-href="<?php echo __SITE_URL . 'items_questions/popup_forms/leave_reply_to_item_question/' . $question_item['id_q'];?>"
                                                data-title="<?php echo translate('seller_questions_actions_reply_btn_tag_title', null, true);?>"
                                                title="<?php echo translate('seller_questions_actions_reply_btn_tag_title', null, true);?>">
                                                <i class="ep-icon ep-icon_reply-left-empty"></i><?php echo translate('seller_questions_actions_reply_btn');?>
                                            </a>
                                        <?php }?>

                                        <?php if ($can_report) {?>
                                            <a class="dropdown-item fancyboxValidateModal fancybox.ajax"
                                                <?php echo addQaUniqueIdentifier("items_questions-my__details_replied_dropdown_report-btn")?>
                                                data-fancybox-href="<?php echo __SITE_URL . 'complains/popup_forms/add_complain/item_questions/' . $question_item['id_q'] . '/' . $question_item['id_questioner'];?>"
                                                data-title="<?php echo translate('seller_questions_actions_report_btn_tag_title', null, true);?>"
                                                title="<?php echo translate('seller_questions_actions_report_btn_tag_title', null, true);?>">
                                                <i class="ep-icon ep-icon_warning-circle-stroke"></i><?php echo translate('seller_questions_actions_report_btn');?>
                                            </a>
                                        <?php }?>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>

                <ul class="product-comments product-comments--reply" id="question-<?php echo $question_item['id_q']?>-reply-block">
                    <?php if (!empty($question_item['reply'])) {
                        views()->display('new/items_questions/item_question_reply_view', array('question_reply' => $question_item, 'helpful' => $helpful));
                    }?>
                </ul>
            </div>
        </div>

    </li>
<?php }?>
