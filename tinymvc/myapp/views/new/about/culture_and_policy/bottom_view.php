<div class="cp-laws">
    <div class="container-center-sm">
        <div class="cp-laws__main-title-block">
            <h2 class="cp-laws__main-title"><?php echo translate('about_us_culture_and_policy_laws_and_rules_block_title');?></h2>
            <div class="cp-laws__main-subtext"><?php echo translate('about_us_culture_and_policy_laws_and_rules_block_subtitle');?></div>
        </div>

        <div class="row pt-70 pb-40">

            <div class="col-12 col-md-4">
                <div class="cp-laws__image-block">
                    <img class="image" src="<?php echo __IMG_URL . 'public/img/about/culture_and_policy/handshake.jpg';?>" alt="Rules and Regulations">
                    <h3 class="cp-laws__image-title"><?php echo translate('about_us_culture_and_policy_rules_and_regulations_block_title');?></h3>
                </div>
                <div class="cp-laws__text ep-middle-text">
                    <?php echo translate('about_us_culture_and_policy_rules_and_regulations_block_text');?>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="cp-laws__image-block">
                    <img class="image" src="<?php echo __IMG_URL . 'public/img/about/culture_and_policy/women-smiling.jpg';?>" alt="Integrity and Compliance">
                    <h3 class="cp-laws__image-title"><?php echo translate('about_us_culture_and_policy_integrity_and_compliances_block_title');?></h3>
                </div>
                <div class="cp-laws__text ep-middle-text">
                    <?php echo translate('about_us_culture_and_policy_integrity_and_compliances_block_text');?>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="cp-laws__image-block">
                    <img class="image" src="<?php echo __IMG_URL . 'public/img/about/culture_and_policy/personal-info.jpg';?>" alt="Laws and Rules">
                    <h3 class="cp-laws__image-title"><?php echo translate('about_us_culture_and_policy_personal_information_block_title');?></h3>
                </div>
                <div class="cp-laws__text ep-middle-text">
                    <?php echo translate('about_us_culture_and_policy_personal_information_block_text');?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php app()->view->display('new/about/bottom_need_help_view');?>