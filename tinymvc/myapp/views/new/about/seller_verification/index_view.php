<div class="mobile-links">
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#about-flex-card__fixed-left">
        <i class="ep-icon ep-icon_items"></i> <?php echo translate('about_us_nav_menu_btn');?>
    </a>
</div>

<h2 class="verification-docs__title"><?php echo translate('about_us_seller_verification_title');?></h2>

<div class="row">
    <div class="col-md-6">
        <div class="ep-large-text">
            <?php echo translate('about_us_seller_verification_block_1');?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="ep-large-text">
            <?php echo translate('about_us_seller_verification_block_2');?>
        </div>
    </div>
</div>

<div id="dtCertificationInfo_wrapper" class="dataTables_wrapper no-footer">
    <table class="main-data-table mt-70 verification-data-table<?php echo $isLogged ? "" : " not-loginned"?> dataTable no-footer" id="dtCertificationInfo">
        <thead>
            <tr>
                <th class="dt_documents vam sorting_disabled"><?php echo translate('about_us_seller_verification_table_th_documents');?></th>
                <th class="dt_verified_seller vam sorting_disabled"><?php echo translate('general_user_groups_verified_seller');?></th>
                <th class="dt_certified_seller vam sorting_disabled"><?php echo translate('general_user_groups_certified_seller');?></th>
                <th class="dt_verified_manufacturer vam sorting_disabled"><?php echo translate('general_user_groups_verified_manufacturer');?></th>
                <th class="dt_certified_manufacturer vam sorting_disabled"><?php echo translate('general_user_groups_certified_manufacturer');?></th>
            </tr>
        </thead>

        <tbody class="tabMessage">
            <?php foreach($dataTable as $i => $tr){ ?>
                <tr role="row" class="<?php echo $i % 2 === 0 ? "odd" : "even"; ?><?php echo $i === count($dataTable) - 1 && !$isLogged ? " last" : ""; ?>">
                    <td data-title="Documents"><?php echo $tr["dt_documents"]; ?></td>
                    <td class=" vam" data-title="Verified Seller"><?php echo $tr["dt_verified_seller"]; ?></td>
                    <td class=" vam" data-title="Certified Seller"><?php echo $tr["dt_certified_seller"]; ?></td>
                    <td class=" vam" data-title="Verified Manufacturer"><?php echo $tr["dt_verified_manufacturer"]; ?></td>
                    <td class=" vam" data-title="Certified Manufacturer"><?php echo $tr["dt_certified_manufacturer"]; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<h2 class="verification-docs__title"><?php echo translate('about_us_seller_verification_block_3');?></h2>

<div class="row">

    <div class="col-md-6">
        <div class="verification-docs__info-row ep-middle-text"><?php echo translate('about_us_seller_verification_block_3_list_1');?></div>
        <div class="verification-docs__info-row ep-middle-text"><?php echo translate('about_us_seller_verification_block_3_list_2');?></div>
        <div class="verification-docs__info-row ep-middle-text"><?php echo translate('about_us_seller_verification_block_3_list_3');?></div>
        <div class="verification-docs__info-row ep-middle-text"><?php echo translate('about_us_seller_verification_block_3_list_4');?></div>
        <div class="verification-docs__info-row ep-middle-text"><?php echo translate('about_us_seller_verification_block_3_list_5');?></div>
        <div class="verification-docs__info-row ep-middle-text"><?php echo translate('about_us_seller_verification_block_3_list_6');?></div>
    </div>

    <div class="col-md-6">
        <div class="verification-docs__info-row ep-middle-text"><?php echo translate('about_us_seller_verification_block_3_list_7');?></div>
        <div class="verification-docs__info-row ep-middle-text"><?php echo translate('about_us_seller_verification_block_3_list_8');?></div>
        <div class="verification-docs__info-row ep-middle-text"><?php echo translate('about_us_seller_verification_block_3_list_9');?></div>
        <div class="verification-docs__info-row ep-middle-text"><?php echo translate('about_us_seller_verification_block_3_list_10');?></div>
        <div class="verification-docs__info-row ep-middle-text"><?php echo translate('about_us_seller_verification_block_3_list_11');?></div>
        <div class="verification-docs__info-row ep-middle-text"><?php echo translate('about_us_seller_verification_block_3_list_12');?></div>
    </div>
</div>

<script>
    mobileDataTable($("#dtCertificationInfo"));
</script>
