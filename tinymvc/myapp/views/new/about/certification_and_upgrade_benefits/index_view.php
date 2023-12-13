<div class="mobile-links">
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#about-flex-card__fixed-left">
        <i class="ep-icon ep-icon_items"></i> <?php echo translate('about_us_nav_menu_btn');?>
    </a>
</div>

<div class="vrf-message">
    <div class="vrf-message__column">
        <div class="vrf-message__title"><?php echo translate('about_us_certification_and_upgrade_benefits_block_1_title');?></div>
        <div class="ep-large-text">
            <p><?php echo translate('about_us_certification_and_upgrade_benefits_block_1_left_column_text');?></p>
        </div>
    </div>
    <div class="vrf-message__column">
        <div class="ep-large-text">
            <p><?php echo translate('about_us_certification_and_upgrade_benefits_block_1_right_column_text_1', array('{{HTML_BR_1}}' => '<br>', '{{START_HTML_1}}' => '<strong>', '{{END_HTML_1}}' => '</strong>', '{{HTML_BR_2}}' => '<br>', '{{START_HTML_2}}' => '<strong>', '{{END_HTML_2}}' => '</strong>'));?></p>
            <p><?php echo translate('about_us_certification_and_upgrade_benefits_block_1_right_column_text_2', array('{{START_HTML_1}}' => '<strong>', '{{END_HTML_1}}' => '</strong>', '{{START_HTML_2}}' => '<strong>', '{{END_HTML_2}}' => '</strong>', '{{START_HTML_3}}' => '<strong>', '{{END_HTML_3}}' => '</strong>'));?></p>
        </div>
    </div>
</div>

<div class="vrf-table">
    <div class="vrf-table__title"><?php echo translate('about_us_certification_and_upgrade_benefits_block_2_title', array('{{START_HTML_1}}' => '<span>', '{{END_HTML_1}}' => '</span>'));?></div>
    <div class="vrf-table__text ep-large-text">
        <p><?php echo translate('about_us_certification_and_upgrade_benefits_block_2_text');?></p>
    </div>
</div>

<div id="dtCertificationInfo_wrapper" class="dataTables_wrapper no-footer">
    <table class="main-data-table verification-data-table<?php echo $isLogged ? "" : " not-loginned"?> dataTable no-footer" id="dtCertificationInfo">
        <thead>
            <tr>
                <th class="dt_feature vam"><?php echo translate('about_us_certification_and_upgrade_benefits_table_th_verification_feature');?></th>
                <th class="dt_verified_seller sorting_disabled vam"><?php echo translate('general_user_groups_verified_seller');?></th>
                <th class="dt_verified_manufacturer sorting_disabled vam"><?php echo translate('general_user_groups_verified_manufacturer');?></th>
                <th class="dt_certified_seller sorting_disabled vam"><?php echo translate('general_user_groups_certified_seller');?></th>
                <th class="dt_certified_manufacturer sorting_disabled vam"><?php echo translate('general_user_groups_certified_manufacturer');?></th>
            </tr>
        </thead>
        <tbody class="tabMessage">
            <?php foreach($dataTable as $i => $tr){ ?>
                <tr role="row" class="<?php echo $i % 2 === 0 ? "odd" : "even"; ?><?php echo $i === count($dataTable) - 1 && !$isLogged ? " last" : ""; ?>">
                    <td data-title="Verification Feature"><?php echo $tr["dt_feature"]; ?></td>
                    <td class=" vam" data-title="Verified Seller"><?php echo $tr["dt_verified_seller"]; ?></td>
                    <td class=" vam" data-title="Verified Manufacturer"><?php echo $tr["dt_verified_manufacturer"]; ?></td>
                    <td class=" vam" data-title="Certified Seller"><?php echo $tr["dt_certified_seller"]; ?></td>
                    <td class=" vam" data-title="Certified Manufacturer"><?php echo $tr["dt_certified_manufacturer"]; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="vrf-benefits">
    <div class="vrf-benefits__container">
        <div class="vrf-benefits__image">
            <img class="image" src="<?php echo __IMG_URL . 'public/img/about/verification_feature/meeting.jpg';?>" alt="EP Certification">
        </div>
        <div class="vrf-benefits__info">
            <div class="vrf-benefits__title"><?php echo translate('about_us_certification_and_upgrade_benefits_block_3_title', array('{{START_HTML_1}}' => '<span>', '{{END_HTML_1}}' => '</span>'));?></div>
            <div class="vrf-benefits__top-line"><?php echo translate('about_us_certification_and_upgrade_benefits_block_3_label_verified');?></div>
            <div class="vrf-benefits__bottom-line"><?php echo translate('about_us_certification_and_upgrade_benefits_block_3_label_certified');?></div>
            <ul class="vrf-benefits__list">
                <li class="vrf-benefits__list-item">
                    <span class="about-us__icon mt-2"><i class="ep-icon ep-icon_ok"></i></span>
                    <?php echo translate('about_us_certification_and_upgrade_benefits_block_3_li_1');?>
                </li>
                <li class="vrf-benefits__list-item">
                    <span class="about-us__icon mt-2"><i class="ep-icon ep-icon_ok"></i></span>
                    <?php echo translate('about_us_certification_and_upgrade_benefits_block_3_li_2');?>
                </li>
                <li class="vrf-benefits__list-item">
                    <span class="about-us__icon mt-2"><i class="ep-icon ep-icon_ok"></i></span>
                    <?php echo translate('about_us_certification_and_upgrade_benefits_block_3_li_3');?>
                </li>
                <li class="vrf-benefits__list-item">
                    <span class="about-us__icon mt-2"><i class="ep-icon ep-icon_ok"></i></span>
                    <?php echo translate('about_us_certification_and_upgrade_benefits_block_3_li_4');?>
                </li>
                <li class="vrf-benefits__list-item">
                    <span class="about-us__icon mt-2"><i class="ep-icon ep-icon_ok"></i></span>
                    <?php echo translate('about_us_certification_and_upgrade_benefits_block_3_li_5');?>
                </li>
                <?php if(!$isLogged){?>
                    <a class="btn btn-primary" href="<?php echo __SITE_URL;?>register"><?php echo translate('about_us_certification_and_upgrade_benefits_block_3_become_certified_btn');?></a>
                <?php } elseif(have_right('upgrade_group')){?>
                    <a class="btn btn-primary" href="<?php echo __SITE_URL;?>upgrade"><?php echo translate('about_us_certification_and_upgrade_benefits_block_3_become_certified_btn');?></a>
                <?php }?>
            </ul>
        </div>
    </div>
</div>

<script>
    mobileDataTable($("#dtCertificationInfo"));
</script>
