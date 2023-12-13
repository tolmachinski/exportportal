<div class="ep-benefits">
    <div class="ep-benefits__row">
        <div class="ep-benefits__col">
            <ul class="ep-benefits-list">
                <li class="ep-benefits-list__item">
                    <div class="ep-benefits-list__icon"><?php echo $icons['globe']; ?></div>
                    <div class="ep-benefits-list__ttl"><?php echo translate('why_ep_reach_millions_ttl'); ?></div>
                    <div class="ep-benefits-list__desc"><?php echo translate('why_ep_reach_millions_desc'); ?></div>
                    <a class="ep-benefits-list__link" href="<?php echo __SITE_URL . 'export_import'; ?>" <?php echo addQaUniqueIdentifier("why-ep__reach-millions-around-the-world-link")?>>
                        <?php echo translate('why_ep_reach_millions_learn_more'); ?>
                        <i class="ep-icon ep-icon_arrow-line-right"></i>
                    </a>
                </li>

                <li class="ep-benefits-list__item">
                    <div class="ep-benefits-list__icon"><?php echo $icons['protection']; ?></div>
                    <div class="ep-benefits-list__ttl"><?php echo translate('why_ep_impoort_export_protection_ttl'); ?></div>
                    <div class="ep-benefits-list__desc"><?php echo translate('why_ep_import_export_protection_desc'); ?></div>
                    <a class="ep-benefits-list__link" href="<?php echo __SITE_URL . 'about'; ?>" <?php echo addQaUniqueIdentifier("why-ep__import-export-protection-link")?>>
                        <?php echo translate('why_ep_export_protection_learn_more'); ?>
                        <i class="ep-icon ep-icon_arrow-line-right"></i>
                    </a>
                </li>

                <li class="ep-benefits-list__item">
                    <div class="ep-benefits-list__icon"><?php echo $icons['certificate']; ?></div>
                    <div class="ep-benefits-list__ttl"><?php echo translate('why_ep_certification_program_ttl'); ?></div>
                    <div class="ep-benefits-list__desc"><?php echo translate('why_ep_certification_program_desc'); ?></div>
                    <a class="ep-benefits-list__link" href="<?php echo __SITE_URL . 'about/certification_and_upgrade_benefits'; ?>" <?php echo addQaUniqueIdentifier("why-ep__certification-program-link")?>>
                        <?php echo translate('why_ep_certification_program_learn_more'); ?>
                        <i class="ep-icon ep-icon_arrow-line-right"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="ep-benefits__col">
            <div class="ep-benefits__bg">
                <picture>
                    <source
                        media="(max-width: 425px)"
                        srcset="<?php echo getLazyImage(425, 266); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/why_ep/clients_mobile.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 767px)"
                        srcset="<?php echo getLazyImage(767, 480); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/why_ep/clients_tablet.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 1200px)"
                        srcset="<?php echo getLazyImage(600, 750); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/why_ep/clients_1200.jpg'); ?>"
                    >
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(960, 730); ?>"
                        data-src="<?php echo asset('public/build/images/about/why_ep/clients.jpg'); ?>"
                        width="950"
                        height="710"
                        alt="<?php echo translate('why_ep_certification_program_clients_img', null, true); ?>"
                    >
                </picture>
            </div>
        </div>
    </div>
    <div class="ep-benefits__row ep-benefits__row--reverse">
        <div class="ep-benefits__col">
            <div class="ep-benefits__bg">
                <picture>
                    <source
                        media="(max-width: 425px)"
                        srcset="<?php echo getLazyImage(425, 266); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/why_ep/workers_mobile.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 767px)"
                        srcset="<?php echo getLazyImage(767, 480); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/why_ep/workers_tablet.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 1200px)"
                        srcset="<?php echo getLazyImage(600, 750); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/why_ep/workers_1200.jpg'); ?>"
                    >
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(960, 755); ?>"
                        data-src="<?php echo asset('public/build/images/about/why_ep/workers.jpg'); ?>"
                        width="950"
                        height="734"
                        alt="<?php echo translate('why_ep_certification_program_workers_img', null, true); ?>"
                    >
                </picture>
            </div>
        </div>

        <div class="ep-benefits__col">
            <ul class="ep-benefits-list ep-benefits-list--left">
                <li class="ep-benefits-list__item">
                    <div class="ep-benefits-list__icon"><?php echo $icons['support']; ?></div>
                    <div class="ep-benefits-list__ttl"><?php echo translate('why_ep_assistance_ttl'); ?></div>
                    <div class="ep-benefits-list__desc"><?php echo translate('why_ep_assistance_desc'); ?></div>
                    <a class="ep-benefits-list__link" href="<?php echo __SITE_URL . 'help'; ?>" <?php echo addQaUniqueIdentifier("why-ep__assistance-link")?>>
                        <?php echo translate('why_ep_assistance_learn_more'); ?>
                        <i class="ep-icon ep-icon_arrow-line-right"></i>
                    </a>
                </li>

                <li class="ep-benefits-list__item">
                    <div class="ep-benefits-list__icon ep-benefits-list__icon--security"><?php echo $icons['security']; ?></div>
                    <div class="ep-benefits-list__ttl"><?php echo translate('why_ep_total_security_ttl'); ?></div>
                    <div class="ep-benefits-list__desc"><?php echo translate('why_ep_total_security_desc'); ?></div>
                    <a class="ep-benefits-list__link" href="<?php echo __SITE_URL . 'security'; ?>" <?php echo addQaUniqueIdentifier("why-ep__total-security-link")?>>
                        <?php echo translate('why_ep_total_security_learn_more'); ?>
                        <i class="ep-icon ep-icon_arrow-line-right"></i>
                    </a>
                </li>

                <li class="ep-benefits-list__item">
                    <div class="ep-benefits-list__icon"><?php echo $icons['professional_expertise']; ?></div>
                    <div class="ep-benefits-list__ttl"><?php echo translate('why_ep_professional_expertise_ttl'); ?></div>
                    <div class="ep-benefits-list__desc"><?php echo translate('why_ep_professional_expertise_desc'); ?></div>
                    <a class="ep-benefits-list__link" href="<?php echo __SITE_URL . 'landing/ep_plus'; ?>" <?php echo addQaUniqueIdentifier("why-ep__professional-expertise-link")?>>
                        <?php echo translate('why_ep_professional_expertise_learn_more'); ?>
                        <i class="ep-icon ep-icon_arrow-line-right"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
