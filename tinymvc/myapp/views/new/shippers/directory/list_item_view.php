<div class="companies__item flex-card" <?php echo addQaUniqueIdentifier('global__item__company-card')?>>
    <div class="companies__img-wr flex-card__fixed">
        <div class="companies__img image-card3">
            <a class="link" href="<?php echo __SITE_URL . 'shipper/' . strForUrl($shipper['co_name']) . '-' . $shipper['id']; ?>" target="_blank">
                <img
                    class="image"
                    itemprop="logo"
                    <?php echo addQaUniqueIdentifier('global__item__company-card_image')?>
                    src="<?php echo getDisplayImageLink(array('{ID}' => $shipper['id'], '{FILE_NAME}' => $shipper['logo']), 'shippers.main', array( 'thumb_size' => 1 ));?>"
                    alt="<?php echo $shipper['co_name']; ?>"/>
            </a>
        </div>
    </div>
    <div class="companies__detail flex-card__float">
        <div class="companies__ttl" title="<?php echo $shipper['co_name']; ?>">
            <a class="link" itemprop="url" <?php echo addQaUniqueIdentifier('global__item__company-card_title')?> href="<?php echo __SITE_URL . 'shipper/' . strForUrl($shipper['co_name']) . '-' . $shipper['id']; ?>">
                <span itemprop="name"><?php echo $shipper['co_name']; ?></span>
            </a>
        </div>

        <div
            class="companies__date"
            title="<?php echo formatDate($shipper['create_date'], 'M Y');?>"
            <?php echo addQaUniqueIdentifier('global__item__company-card_member-from')?>
        >
            <?php echo translate('text_member_from_date', array('[[DATE]]' => getDateFormat($shipper['create_date'], 'Y-m-d H:i:s', 'M Y')));?>
        </div>

        <div class="companies__actions">
            <div class="companies__country">
                <img
                    class="image"
                    width="24"
                    height="24"
                    <?php echo addQaUniqueIdentifier('global__item__company-card_country-flag')?>
                    src="<?php echo getCountryFlag($shipper['country']);?>"
                    alt="<?php echo $shipper['country']; ?>"
                    title="<?php echo $shipper['country']; ?>"
                />
                <span class="text" <?php echo addQaUniqueIdentifier('global__item__company-card_country-name')?>><?php echo $shipper['country']; ?></span>
            </div>

            <div class="dropdown">
                <a class="dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ep-icon ep-icon_menu-circles"></i>
                </a>

                <div class="dropdown-menu">
                    <?php if (logged_in()) { ?>
                        <?php echo !empty($shipper['btnChat']) ? $shipper['btnChat'] : ''; ?>

                        <button
                            class="dropdown-item fancybox.ajax fancyboxValidateModal"
                            data-title="<?php echo translate('freight_forwarder_card_actions_share_btn_tag_title', null, true);?>"
                            data-fancybox-href="<?php echo __SITE_URL . 'shipper/popup_forms/share_company/' . $shipper['id']; ?>"
                            title="<?php echo translate('freight_forwarder_card_actions_share_btn_tag_title', null, true);?>"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_share-stroke"></i> <span><?php echo translate('freight_forwarder_card_actions_share_btn');?></span>
                        </button>
                        <button
                            class="dropdown-item fancybox.ajax fancyboxValidateModal"
                            data-title="<?php echo translate('freight_forwarder_card_actions_email_btn_tag_title', null, true);?>"
                            data-fancybox-href="<?php echo __SITE_URL . 'shipper/popup_forms/email_company/' . $shipper['id'];?>"
                            title="<?php echo translate('freight_forwarder_card_actions_email_btn_tag_title', null, true);?>"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_envelope-send"></i> <span><?php echo translate('freight_forwarder_card_actions_email_btn');?></span>
                        </button>
                    <?php } else { ?>
                        <button
                            class="dropdown-item js-require-logged-systmess"
                            title="<?php echo translate('freight_forwarder_card_actions_contact_btn_tag_title', null, true);?>"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_envelope"></i> <span><?php echo translate('freight_forwarder_card_actions_contact_btn');?></span>
                        </button>
                        <button
                            class="dropdown-item js-require-logged-systmess"
                            title="<?php echo translate('freight_forwarder_card_actions_share_btn_tag_title', null, true);?>"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_share-stroke"></i> <span><?php echo translate('freight_forwarder_card_actions_share_btn');?></span>
                        </button>
                        <button
                            class="dropdown-item js-require-logged-systmess"
                            title="<?php echo translate('freight_forwarder_card_actions_email_btn_tag_title', null, true);?>"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_envelope-send"></i> <span><?php echo translate('freight_forwarder_card_actions_email_btn');?></span>
                        </button>
                    <?php } ?>

                    <?php if (have_right('sell_item')) { ?>
                        <?php if (!$seller_dashboard) { ?>
                            <?php if (in_array($shipper['id_user'], $id_partners_list)) { ?>
                                <span class="dropdown-item txt-green"><i class="ep-icon ep-icon_ok-circle"></i><?php echo translate('freight_forwarder_card_actions_is_partner_label');?></span>
                            <?php } else if (in_array($shipper['id_user'], $id_partners_requests_list)) { ?>
                                <span class="dropdown-item txt-gray-light"><i class="ep-icon ep-icon_hourglass-processing"></i><?php echo translate('freight_forwarder_card_actions_partner_request_is_sent_label');?></span>
                            <?php } else { ?>
                                <button
                                    class="dropdown-item call-function"
                                    data-callback="makeShipperPartner"
                                    data-shipper="<?php echo $shipper['id_user']; ?>"
                                    type="button"
                                >
                                    <i class="ep-icon ep-icon_plus-circle"></i><?php echo translate('freight_forwarder_card_actions_become_a_partner_btn');?>
                                </button>
                            <?php } ?>
                        <?php } elseif ($company['id_user'] === id_session()) { ?>
                            <button
                                class="dropdown-item txt-red confirm-dialog"
                                data-callback="removePartner"
                                data-partner="<?php echo $shipper['id_partner']; ?>"
                                data-shipper="<?php echo $shipper['id_user']; ?>"
                                data-message="<?php echo translate('freight_forwarder_card_actions_remove_a_partner_btn_confirm_msg', null, true);?>"
                                type="button"
                            >
                                <i class="ep-icon ep-icon_trash-stroke"></i><?php echo translate('freight_forwarder_card_actions_remove_a_partner_btn');?>
                            </button>
                        <?php } ?>
                    <?php } ?>

                    <?php if (logged_in()) { ?>
                        <?php if (in_session('shippers_saved', $shipper['id'])) { ?>
                            <button
                                class="dropdown-item call-function"
                                data-callback="remove_shipper_company"
                                data-company="<?php echo $shipper['id']; ?>"
                                type="button"
                            >
                                <i class="ep-icon ep-icon_favorite"></i> <span><?php echo translate('freight_forwarder_card_actions_unsave_btn');?></span>
                            </button>
                        <?php } else { ?>
                            <button
                                class="dropdown-item call-function"
                                data-callback="add_shipper_company"
                                data-company="<?php echo $shipper['id']; ?>"
                                type="button"
                            >
                                <i class="ep-icon ep-icon_favorite-empty"></i> <span><?php echo translate('freight_forwarder_card_actions_save_btn');?></span>
                            </button>
                        <?php } ?>
                    <?php } else { ?>
                        <button
                            class="dropdown-item js-require-logged-systmess"
                            title="Save"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_favorite-empty"></i> <span><?php echo translate('freight_forwarder_card_actions_save_btn');?></span>
                        </button>
                    <?php } ?>
                    <a
                        class="dropdown-item"
                        href="<?php echo __SITE_URL . 'shipper/' . strForUrl($shipper['co_name']) . '-' . $shipper['id']; ?>"
                    >
                        <i class="ep-icon ep-icon_user-search"></i> <span><?php echo translate('freight_forwarder_card_actions_view_profile_btn');?></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
