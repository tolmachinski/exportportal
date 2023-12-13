<?php if (!empty($followers)) { ?>
    <?php foreach ($followers as $follower) { ?>
        <div class="b2b-followers__item js-b2b-request-followers-item" <?php echo addQaUniqueIdentifier('page__b2b__followers-item'); ?>>
            <div class="b2b-followers__img" <?php echo addQaUniqueIdentifier('page__b2b__followers_user-image'); ?>>
                <img
                    class="image js-lazy"
                    data-src="<?php echo $follower['logoLink']; ?>"
                    src="<?php echo getLazyImage(80, 80); ?>"
                    width="80"
                    height="80"
                    alt="<?php echo cleanOutput($follower['username']); ?>"
                />
            </div>

            <div class="b2b-followers__detail">
                <a
                    class="b2b-followers__name"
                    href="<?php echo __SITE_URL . 'usr/' . strForURL($follower['username']) . '-' . $follower['id_user']; ?>"
                    title="<?php echo cleanOutput($follower['username']); ?>"
                    <?php echo addQaUniqueIdentifier('page__b2b__followers_user-name'); ?>
                >
                    <?php echo cleanOutput($follower['username']); ?>
                </a>

                <p class="b2b-followers__group <?php echo userGroupNameColor($follower['group_name']); ?>" <?php echo addQaUniqueIdentifier('page__b2b__followers_user-group'); ?>>
                    <?php echo $follower['group_name']; ?>
                </p>

                <div class="b2b-followers__footer">
                    <p class="b2b-followers__date" <?php echo addQaUniqueIdentifier('page__b2b__followers_date-follow'); ?>>
                        <?php echo getDateFormat($follower['date_follow'], 'Y-m-d H:i:s', 'd M Y H:i A'); ?>
                    </p>

                    <div class="dropdown">
                        <button
                            class="dropdown-toggle"
                            id="dropdownMenuButton"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_menu-circles"></i>
                        </button>

                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <?php if (logged_in()) { ?>
                                <?php echo !empty($follower['btnChat']) ? $follower['btnChat'] : ''; ?>

                                <?php if (is_privileged('user', $follower['id_user'], 'follow_this')) { ?>
                                    <button
                                        class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                        title="<?php echo translate('b2b_detail_actions_edit_follower_title', null, true); ?>"
                                        data-title="<?php echo translate('b2b_detail_actions_edit_follower_title', null, true); ?>"
                                        data-fancybox-href="<?php echo __SITE_URL; ?>follow/popup_forms/edit_follow_b2b_request/<?php echo $follower['id_follower']; ?>"
                                        type="button"
                                    >
                                        <i class="ep-icon ep-icon_pencil"></i><span class="txt"><?php echo translate('general_button_edit_text'); ?></span>
                                    </button>
                                <?php } ?>

                                <?php if (have_right('moderate_content') && !$follower['moderated']) { ?>
                                    <button
                                        class="dropdown-item confirm-dialog txt-red"
                                        data-follower="<?php echo $follower['id_follower']; ?>"
                                        data-js-action="b2b-requests:moderate-follower"
                                        data-message="<?php echo translate('b2b_detail_actions_moderate_followers_confirm_message', null, true); ?>"
                                        type="button"
                                    >
                                        <i class="ep-icon ep-icon_sheild-nok"></i><span class="txt"><?php echo translate('general_button_moderate_text'); ?></span>
                                    </button>
                                <?php } ?>
                            <?php } else { ?>
                                <button
                                    class="call-systmess dropdown-item"
                                    data-message="<?php echo translate('systmess_error_should_be_logged', null, true); ?>"
                                    data-type="error"
                                    title="<?php echo translate('general_button_contact_text', null, true); ?>"
                                    type="button"
                                >
                                    <i class="ep-icon ep-icon_envelope"></i><span class="txt"><?php echo translate('chat_button_generic_text'); ?></span>
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
<?php } ?>
