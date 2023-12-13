<?php use App\Common\Contracts\B2B\B2bRequestLocationType;

?>
<!-- B2B card -->
<div class="js-b2b-card b2b-card<?php echo $classList ?? ''; ?>" id="<?php echo 'b2b_' . $request['id_request']; ?>" <?php echo addQaUniqueIdentifier('page__b2b__request-card'); ?>>
    <span class="b2b-card__status">
        active
    </span>
    <a
        <?php $isLoggedIn = logged_in();
        if (!$isLoggedIn) { ?>
            class="fancybox.ajax fancyboxValidateModal call-action"
            data-mw="400"
            data-title="Login"
            data-js-action="lazy-loading:login"
            href="<?php echo __SITE_URL . 'login'; ?>"
        <?php } else { ?>
            href="<?php echo __SITE_URL . 'b2b/detail/' . strForURL($request['b2b_title']) . '-' . $request['id_request']; ?>"
        <?php } ?>
    >
        <img
            <?php if (!$removeLazyImg) { ?>
                class="b2b-card__image js-lazy"
                data-src="<?php echo $request['mainImageLink']; ?>"
                src="<?php echo getLazyImage(213, 160); ?>"
            <?php } else { ?>
                class="b2b-card__image"
                src="<?php echo $request['mainImageLink']; ?>"
            <?php } ?>
            width="213"
            height="160"
            alt="<?php echo cleanOutput($request['company']['name_company']); ?>"
            <?php echo addQaUniqueIdentifier('page__b2b__request-image'); ?>
        />
    </a>

    <div class="b2b-card__info">
        <div class="b2b-card__heading">
            <a
                class="b2b-card__title<?php echo !$isLoggedIn ? ' fancybox.ajax fancyboxValidateModal call-action' : ''; ?>"
                <?php if (!$isLoggedIn) { ?>
                    data-mw="400"
                    data-title="Login"
                    data-js-action="lazy-loading:login"
                    href="<?php echo __SITE_URL . 'login'; ?>"
                <?php } else { ?>
                    href="<?php echo __SITE_URL . 'b2b/detail/' . strForURL($request['b2b_title']) . '-' . $request['id_request']; ?>"
                <?php } ?>
                <?php echo addQaUniqueIdentifier('page__b2b__request-title'); ?>
            >
                <?php echo $request['b2b_title']; ?>
            </a>

            <div class="dropdown">
                <button
                    class="dropdown-toggle"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                    type="button"
                >
                    <i class="ep-icon ep-icon_menu-circles"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                    <button
                        class="dropdown-item fancybox.ajax fancyboxValidateModal"
                        data-title="<?php echo !$isLoggedIn ? 'Login' : 'Send info about this company to your contacts by email'; ?>"
                        data-fancybox-href="<?php echo !$isLoggedIn ? __SITE_URL . 'login' : __SITE_URL . 'b2b/popup_forms/email/' . $request['id_request']; ?>"
                        title="Email this"
                        type="button"
                        <?php if (!$isLoggedIn) { ?>
                            data-mw="400"
                            data-js-action="lazy-loading:login"
                        <?php } ?>
                    >
                        <i class="ep-icon ep-icon_envelope-send"></i>
                        <span>Email this</span>
                    </button>

                    <button
                        class="dropdown-item fancybox.ajax fancyboxValidateModal"
                        data-title="<?php echo !$isLoggedIn ? 'Login' : 'Share this request with your followers'; ?>"
                        data-fancybox-href="<?php echo !$isLoggedIn ? __SITE_URL . 'login' : __SITE_URL . 'b2b/popup_forms/share/' . $request['id_request']; ?>"
                        title="Share this"
                        type="button"
                        <?php if (!$isLoggedIn) { ?>
                            data-mw="400"
                            data-js-action="lazy-loading:login"
                        <?php } ?>
                    >
                        <i class="ep-icon ep-icon_share-stroke"></i>
                        <span>Share this</span>
                    </button>

                    <?php if ($isLoggedIn) { ?>
                        <?php echo !empty($request['btnChat']) ? $request['btnChat'] : ''; ?>

                        <a
                            class="dropdown-item"
                            href="<?php echo __SITE_URL . 'b2b/detail/' . strForURL($request['b2b_title']) . '-' . $request['id_request'] . '#advices_section'; ?>"
                        >
                            <i class="ep-icon ep-icon_comment-stroke"></i> Advice
                        </a>
                    <?php } ?>

                    <?php if (!$isLoggedIn) {?>
                        <button
                            class="dropdown-item fancybox.ajax fancyboxValidateModal"
                            data-title="Login"
                            data-fancybox-href="<?php echo __SITE_URL . 'login'; ?>"
                            data-mw="400"
                            data-js-action="lazy-loading:login"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_comment-stroke"></i> Advice
                        </button>

                        <button
                            class="dropdown-item fancybox.ajax fancyboxValidateModal"
                            data-title="Login"
                            data-fancybox-href="<?php echo __SITE_URL . 'login'; ?>"
                            title="Chat with seller"
                            data-mw="400"
                            data-js-action="lazy-loading:login"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_envelope-stroke"></i> Contact author
                        </button>
                    <?php }?>
                </div>
            </div>
        </div>

        <div class="b2b-card__location">
            <div class="b2b-card__label">Country from:</div>
            <div class="b2b-card__address">
                <img
                    class="b2b-card__country-img js-lazy"
                    src="<?php echo getLazyImage(20, 13); ?>"
                    data-src="<?php echo getCountryFlag($request['company']['country']); ?>"
                    alt="<?php echo cleanOutput($request['company']['country']); ?>"
                    title="<?php echo cleanOutput($request['company']['country']); ?>"
                    <?php echo addQaUniqueIdentifier('page__b2b__country-image'); ?>
                />
                <?php $regionBlock = $request['company']['customAddress'] ?? implode(', ', array_filter([
                        $request['company']['state_name'] ?: null,
                        $request['company']['city'] ?: null,
                ]));?>
                <span class="b2b-card__country-name b2b-card__country-name--no-flex" <?php echo addQaUniqueIdentifier('page__b2b__country-name'); ?>>
                    <?php echo $request['company']['country'] . (empty($regionBlock) ? '' : ','); ?>
                </span>
                <span class="b2b-card__city" <?php echo addQaUniqueIdentifier('page__b2b__company-city'); ?>>
                    <?php echo $regionBlock; ?>
                </span>
            </div>
        </div>

        <div class="b2b-card__location">
            <div class="b2b-card__label">Search in:</div>
            <div class="b2b-card__address" <?php echo addQaUniqueIdentifier('page__b2b__search-in'); ?>>
                <?php if (B2bRequestLocationType::COUNTRY === $request['type_location']->value) { ?>
                    <img
                        class="b2b-card__country-img js-lazy"
                        src="<?php echo getLazyImage(20, 13); ?>"
                        data-src="<?php echo getCountryFlag(reset($request['countries'])['country']); ?>"
                        alt="<?php echo cleanOutput(reset($request['countries'])['country']); ?>"
                        <?php echo addQaUniqueIdentifier('page__b2b__country-image'); ?>
                    />
                    <span class="b2b-card__country-name" <?php echo addQaUniqueIdentifier('page__b2b__country-name'); ?>>
                        <?php echo reset($request['countries'])['country']; ?>
                    </span>

                    <?php if (1 < $nrCountries = count($request['countries'])) {?>
                        <span class="b2b-card__country-more">
                            +<span><?php echo $nrCountries - 1; ?></span> <span class="b2b-card__country-more-text">more</span>
                        </span>
                    <?php } ?>

                <?php } elseif (B2bRequestLocationType::RADIUS === $request['type_location']->value) {?>
                    <span <?php echo addQaUniqueIdentifier('page__b2b__request-radius'); ?>><?php echo $request['radius'] ?? $request['b2b_radius']; ?> km</span>
                <?php } else {?>
                    <span>Globally</span>
                <?php }?>
            </div>
        </div>
    </div>
</div>
<!-- End B2B card -->
