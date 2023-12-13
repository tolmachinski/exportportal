<?php if (!empty($advices)) { ?>
    <?php foreach ($advices as $advice) {?>
        <div class="b2b-advices__item js-b2b-request-advices-item" id="js-advice-<?php echo $advice['id_advice']; ?>">
            <div class="b2b-advices__heading">
                <div class="b2b-advices__user-img" <?php echo addQaUniqueIdentifier('page__b2b__advices_user-img'); ?>>
                    <img
                        class="image js-lazy"
                        width="40"
                        height="40"
                        src="<?php echo getLazyImage(40, 40); ?>"
                        data-src="<?php echo $advice['logoLink']; ?>"
                        alt="<?php echo cleanOutput($advice['username']); ?>"
                    >
                </div>
                <div>
                    <a
                        class="b2b-advices__user-name"
                        href="<?php echo __SITE_URL . 'usr/' . strForURL($advice['username']) . '-' . $advice['id_user']; ?>"
                        <?php echo addQaUniqueIdentifier('page__b2b__advices_user-name'); ?>
                    >
                        <?php echo cleanOutput($advice['username']); ?>
                    </a>

                    <p class="b2b-advices__date" <?php echo addQaUniqueIdentifier('page__b2b__advices_date'); ?>>
                        <?php echo getDateFormat($advice['date_advice'], 'Y-m-d H:i:s', 'd M Y H:i A'); ?>
                    </p>
                </div>
            </div>

            <div class="b2b-advices__text js-advice-text" <?php echo addQaUniqueIdentifier('page__b2b__advices_text'); ?>>
                <?php echo cleanOutput($advice['message_advice']); ?>
            </div>

            <div class="b2b-advices__footer">
                <div class="did-help <?php if (isset($helpful[$advice['id_advice']])) {?>rate-didhelp<?php }?>">
                    <div class="did-help__txt">Did it help?</div>
                    <?php
                        $disabledClass = $advice['id_user'] == id_session() ? ' disabled' : '';
                        $eventListenerClass = logged_in() ? 'js-didhelp-btn call-action' : 'js-require-logged-systmess';
                        $issetMyHelpfulAdvice = isset($helpful[$advice['id_advice']]);

                        $btnCountPlusClass = ($issetMyHelpfulAdvice && $helpful[$advice['id_advice']]) ? ' txt-blue2' : '';
                        $btnCountMinusClass = ($issetMyHelpfulAdvice && !$helpful[$advice['id_advice']]) ? ' txt-blue2' : '';
                    ?>
                    <button
                        class="i-up didhelp-btn <?php echo $eventListenerClass . $disabledClass; ?>"
                        data-item="<?php echo $advice['id_advice']; ?>"
                        data-page="b2b"
                        data-type="advice"
                        data-action="y"
                        data-js-action="did-help:click"
                        type="button"
                    >
                        <span class="counter-b js-counter-plus" <?php echo addQaUniqueIdentifier('page__b2b__counter'); ?>><?php echo $advice['count_plus']; ?></span>
                        <span class="ep-icon ep-icon_arrow-line-up js-arrow-up<?php echo $btnCountPlusClass; ?>"></span>
                    </button>
                    <button
                        class="i-down didhelp-btn <?php echo $eventListenerClass . $disabledClass; ?>"
                        data-item="<?php echo $advice['id_advice']; ?>"
                        data-page="b2b"
                        data-type="advice"
                        data-action="n"
                        data-js-action="did-help:click"
                        type="button"
                    >
                        <span class="counter-b js-counter-minus" <?php echo addQaUniqueIdentifier('page__b2b__counter'); ?>><?php echo $advice['count_minus']; ?></span>
                        <span class="ep-icon ep-icon_arrow-line-down js-arrow-down<?php echo $btnCountMinusClass; ?>"></span>
                    </button>
                </div>

                <div class="dropdown">
                    <button
                        id="dropdownMenuButton"
                        class="dropdown-toggle"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        <i class="ep-icon ep-icon_menu-circles"></i>
                    </button>

                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <?php if (logged_in()) { ?>
                            <?php echo !empty($advice['btnChat']) ? $advice['btnChat'] : ''; ?>

                            <?php if (is_privileged('user', $advice['id_user'], 'manage_b2b_requests')) { ?>
                                <button
                                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                    data-title="<?php echo translate('b2b_detail_actions_edit_advice_title', null, true); ?>"
                                    title="<?php echo translate('b2b_detail_actions_edit_advice_title', null, true); ?>"
                                    data-fancybox-href="<?php echo __SITE_URL . 'b2b/popup_forms/edit_advice/' . $advice['id_advice']; ?>"
                                    type="button"
                                >
                                    <i class="ep-icon ep-icon_pencil"></i><span class="txt"><?php echo translate('general_button_edit_text'); ?></span>
                                </button>
                            <?php } ?>
                            <?php if (have_right('moderate_content') && !$advice['moderated']) { ?>
                                <button
                                    class="dropdown-item confirm-dialog"
                                    data-js-action="b2b-requests:moderate-advice"
                                    data-advice="<?php echo $advice['id_advice']; ?>"
                                    data-message="<?php echo translate('b2b_detail_actions_moderate_advice_confirm_message', null, true); ?>"
                                    type="button"
                                >
                                    <i class="ep-icon ep-icon_sheild-ok"></i><span class="txt"><?php echo translate('general_button_moderate_text'); ?></span>
                                </button>
                            <?php } ?>
                        <?php } else { ?>
                            <button
                                class="dropdown-item call-systmess"
                                data-message="<?php echo translate('systmess_error_should_be_logged', null, true); ?>"
                                data-type="error"
                                title="<?php echo translate('general_button_contact_text', null, true); ?>"
                                type="button"
                            >
                                <i class="ep-icon ep-icon_envelope"></i><span class="txt"><?php echo translate('general_button_contact_text'); ?></span>
                            </button>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    <?php }?>
<?php } ?>
