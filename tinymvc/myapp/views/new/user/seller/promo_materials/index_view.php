<div class="container-center-sm container-center-sm--padding-7 dashboard-container inputs-40">
    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl dashboard-line__ttl--simple promo-materials__title">
            <?php echo translate('promo_materials_title_text'); ?>
        </h1>
        <div class="dashboard-line__ttl-sub2 promo-materials__subtitle"><?php echo translate(is_buyer() || is_shipper() ? 'promo_materials_subtitle_text_for_buyer_shipper' : 'promo_materials_subtitle_text_for_seller_manufacturer'); ?></div>
    </div>

    <div class="promo-materials footer-connect">
        <div class="promo-materials__row <?php echo (is_buyer() || is_shipper()) ? 'shipper_buyer_seller' : (is_certified() ? 'certified' : 'verified'); ?>">
            <div class="promo-materials__col promo-materials__col--700">
                <?php if (have_right('have_promo_materials_certificate')) { ?>
                    <div id="js-certificate-template-wrapper" class="promo-materials__item">
                        <div class="promo-materials-title">
                            <h2 class="promo-materials-title__txt"><?php echo translate('promo_materials_title_certificate'); ?></h2>
                            <div class="promo-materials-title__actions">
                                <a class="btn btn-light" href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'promo_materials/certificate/pdf'; ?>" target="_blank">
                                    <i class="ep-icon ep-icon_download-stroke"></i>
                                </a>
                            </div>
                        </div>
                        <iframe id="js-certificate-template" height="990" src="<?php echo __CURRENT_SUB_DOMAIN_URL . 'promo_materials/certificate'; ?>"></iframe>
                    </div>
                <?php } ?>

                <?php if (is_certified()) { ?>
                    <div class="promo-materials__item promo-materials__item--badges">
                        <div class="promo-materials-title">
                            <h2 class="promo-materials-title__txt">
                                <?php echo translate('promo_materials_title_set_of_badges'); ?>
                                <a class="info-dialog ep-icon ep-icon_info ml-5" data-message="<?php echo translate('promo_materials_title_set_of_badges_info_dialog_message', null, true); ?>" data-title="<?php echo translate('promo_materials_title_set_of_badges'); ?>" href="#"></a>
                            </h2>
                            <div class="promo-materials-title__actions">
                                <a class="btn btn-light call-function" data-callback="downloadBadges" href="#" target="_blank">
                                    <i class="ep-icon ep-icon_download-stroke"></i>
                                </a>
                            </div>
                        </div>
                        <div class="set-of-badges">
                            <picture class="set-of-badges__background">
                                <source media="(max-width: 330px)" srcset="<?php echo asset("public/build/images/promo_materials/set_of_badges/set-of-badges-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/promo_materials/set_of_badges/set-of-badges-mobile@2x.jpg"); ?> 2x">
                                <source media="(min-width: 331px) and (max-width: 991px)" srcset="<?php echo asset("public/build/images/promo_materials/set_of_badges/set-of-badges-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/promo_materials/set_of_badges/set-of-badges-tablet@2x.jpg"); ?> 2x">
                                <img class="image" src="<?php echo asset("public/build/images/promo_materials/set_of_badges/set-of-badges.jpg"); ?>" srcset="<?php echo asset("public/build/images/promo_materials/set_of_badges/set-of-badges.jpg"); ?> 1x, <?php echo asset("public/build/images/promo_materials/set_of_badges/set-of-badges@2x.jpg"); ?> 2x" alt="Set of badges">
                            </picture>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="promo-materials__col promo-materials__col--440">
                <?php if (have_right('have_promo_materials_id_card')) { ?>
                    <div id="js-id-card-template-wrapper" class="promo-materials__item promo-materials__item--id-card">
                        <div class="promo-materials-title">
                            <h2 class="promo-materials-title__txt"><?php echo translate('promo_materials_title_idcard'); ?></h2>
                            <div class="promo-materials-title__actions">
                                <a class="btn btn-light" href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'promo_materials/id_card/pdf'; ?>" target="_blank">
                                    <i class="ep-icon ep-icon_download-stroke"></i>
                                </a>
                            </div>
                        </div>
                        <iframe id="js-id-card-template" height="649" src="<?php echo __CURRENT_SUB_DOMAIN_URL . 'promo_materials/id_card'; ?>"></iframe>
                    </div>
                    <div class="promo-materials__tablet-wrapper">
                        <div id="js-business-card-template-wrapper" class="promo-materials__item promo-materials__item--bussiness-card">
                            <div class="promo-materials-title">
                                <h2 class="promo-materials-title__txt"><?php echo translate('promo_materials_title_business_card'); ?></h2>
                                <div class="promo-materials-title__actions">
                                    <a class="btn btn-light" href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'promo_materials/business_card/pdf'; ?>" target="_blank">
                                        <i class="ep-icon ep-icon_download-stroke"></i>
                                    </a>
                                </div>
                            </div>
                            <iframe id="js-business-card-template" height="251" src="<?php echo __CURRENT_SUB_DOMAIN_URL . 'promo_materials/business_card'; ?>"></iframe>
                        </div>
                    <?php } ?>
                    <?php if (have_right('have_promo_materials_passport')) { ?>
                        <div class="passport-verified">
                            <?php views('new/user/seller/promo_materials/passport'); ?>
                        </div>
                    <?php } ?>
                    </div>
            </div>
        </div>
    </div>
</div>
<?php views()->display('new/download_script'); ?>
<script>
    $(function() {
        var loadSetInterval = {
            countCallCertificate: 0,
            countCallIdCard: 0,
            countCallBusinessCard: 0,
            countCallPassport: 0,
        };

        initUpdateIframe(0);

        waitILoadIframe();

        jQuery(window).on('resizestop', function() {
            initUpdateIframe(0);
        });

        function initUpdateIframe(interval) {
            <?php if (have_right('have_promo_materials_certificate')) { ?>
                loadSetInterval['certificate'] = setInterval(function() {
                    var iframe = $('#js-certificate-template');
                    var iframeInner = iframe.contents().find('body > div');

                    iframe.attr("height", 990);

                    callUpdateIframe({
                        nameSetInterval: 'certificate',
                        countCall: 'countCallCertificate',
                        iframe: iframe,
                        iframeWrapper: $('#js-certificate-template-wrapper'),
                        iframeInner: iframeInner,
                    });
                }, interval);
            <?php } ?>

            <?php if (have_right('have_promo_materials_id_card')) { ?>
                loadSetInterval['idCard'] = setInterval(function() {
                    var iframe = $('#js-id-card-template');
                    var iframeInner = iframe.contents().find('body > div');

                    iframe.attr("height", 600);

                    callUpdateIframe({
                        nameSetInterval: 'idCard',
                        countCall: 'countCallIdCard',
                        iframe: iframe,
                        iframeWrapper: $('#js-id-card-template-wrapper'),
                        iframeInner: iframeInner,
                    });
                }, interval);
            <?php } ?>

            <?php if (have_right('have_promo_materials_business_card')) { ?>
                loadSetInterval['business'] = setInterval(function() {
                    var iframe = $('#js-business-card-template');
                    var iframeInner = iframe.contents().find('body > div');

                    iframe.attr("height", 250);

                    callUpdateIframe({
                        nameSetInterval: 'business',
                        countCall: 'countCallBusinessCard',
                        iframe: iframe,
                        iframeWrapper: $('#js-business-card-template-wrapper'),
                        iframeInner: iframeInner,
                    });
                }, interval);
            <?php } ?>

            <?php if (have_right('have_promo_materials_passport')) { ?>
                loadSetInterval['passport'] = setInterval(function() {
                    var iframe = $('#js-passport-template');
                    var iframeInner = iframe.contents().find('body > div');

                    iframe.attr("height", 250);

                    callUpdateIframe({
                        nameSetInterval: 'passport',
                        countCall: 'countCallPassport',
                        iframe: iframe,
                        iframeWrapper: $('#js-passport-template-wrapper'),
                        iframeInner: iframeInner,
                    });
                }, interval);
            <?php } ?>
        }

        function callUpdateIframe(params) {
            var $iframe = params.iframe,
                $iframeWrapper = params.iframeWrapper,
                $iframeInner = params.iframeInner,
                countCall = params.countCall,
                nameSetInterval = params.nameSetInterval;

            if (loadSetInterval[countCall] == 0) {
                // showLoader($iframeWrapper, 'Loading...');
                loadSetInterval[countCall]++;
            }

            if ($iframeInner.length) {
                clearInterval(loadSetInterval[nameSetInterval]);
                updateTemplateScaleSize({
                    element: $iframe,
                    elementInner: $iframeInner,
                    wrapper: $iframeWrapper,
                });
            }
        }

        function waitILoadIframe() {
            const promoWrapper = document.querySelector('.promo-materials');
            const promoItems = document.querySelectorAll('.promo-materials__item');

            showLoader(promoWrapper, 'Loading...');

            window.addEventListener("load", () => {
                promoWrapper.classList.add('active');

                promoItems.forEach(el => {
                    el.classList.add('active');
                });

                initUpdateIframe(0);
                hideLoader(promoWrapper)
            });
        }

    });

    var downloadBadges = function() {
        getRequest(__group_site_url + 'download/promo_materials/' + '<?php echo config('download_promo_materials_token'); ?>')
            .then(function(response) {
                if ('success' === response.mess_type) {
                    downloadFile(response.file, response.name);
                } else {
                    systemMessages(response.message, response.mess_type);
                }
            });
    }
</script>
