<?php if (!empty($partners)) { ?>
    <?php foreach ($partners as $partner) { ?>
        <div class="b2b-partners__item js-b2b-request-partners-item" <?php echo addQaUniqueIdentifier('page__b2b__partners-item'); ?>>
            <?php
                $partnerImgLink = getDisplayImageLink(
                    ['{ID}' => $partner['id_partner'], '{FILE_NAME}' => $partner['company']['logo_company']],
                    'companies.main',
                    [
                        'thumb_size'     => 0,
                        'no_image_group' => 'dynamic',
                        'image_size'     => ['w' => 88, 'h' => 88],
                    ]
                );
            ?>
            <div class="b2b-partners__img" <?php echo addQaUniqueIdentifier('page__b2b__partners_image'); ?>>
                <img
                    class="image js-lazy"
                    data-src="<?php echo $partnerImgLink; ?>"
                    src="<?php echo getLazyImage(88, 88); ?>"
                    width="88"
                    height="88"
                    alt="<?php echo cleanOutput($partner['company']['name_company']); ?>"
                />
            </div>

            <div class="b2b-partners__detail">
                <a
                    class="b2b-partners__name"
                    href="<?php echo getCompanyPartnerURL($partner['company']);?>"
                    title="<?php echo cleanOutput($partner['company']['name_company']); ?>"
                    <?php echo addQaUniqueIdentifier('page__b2b__partners_name'); ?>
                >
                    <?php echo cleanOutput($partner['company']['name_company']); ?>
                </a>

                <div class="b2b-partners__country">
                    <img
                        class="b2b-partners__country-img js-lazy"
                        src="<?php echo getLazyImage(24, 16); ?>"
                        data-src="<?php echo getCountryFlag($partner['company']['country']); ?>"
                        alt="<?php echo cleanOutput($partner['company']['country']); ?>"
                        title="<?php echo cleanOutput($partner['company']['country']); ?>"
                        width="24"
                        height="16"
                        <?php echo addQaUniqueIdentifier('page__b2b__country-image'); ?>
                    />
                    <span class="b2b-partners__country-name" <?php echo addQaUniqueIdentifier('page__b2b__country-name'); ?>>
                        <?php echo $partner['company']['country']; ?>
                    </span>
                </div>

                <div class="b2b-partners__footer">
                    <p class="b2b-partners__date" <?php echo addQaUniqueIdentifier('page__b2b__partners_date-partnership'); ?>>
                        <?php
                            echo translate('b2b_detail_partners_as_of', [
                                '{{DATE}}' => getDateFormat($partner['date_partnership'], 'Y-m-d H:i:s', 'd M Y'),
                            ]);
                        ?>
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
                                <?php echo !empty($partner['btnChat']) ? $partner['btnChat'] : ''; ?>
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
