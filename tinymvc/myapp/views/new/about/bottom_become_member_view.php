<div class="info-block footer-connect">

    <div class="info-block__info">
        <div class="info-block__title"><?php echo translate('about_us_certification_and_upgrade_benefits_become_a_member_block_title');?></div>
        <p class="info-block__text">
            <?php echo translate('about_us_certification_and_upgrade_benefits_become_a_member_block_text');?>
        </p>
        <?php if(!logged_in()){?>
            <a class="btn btn-dark" <?php echo addQaUniqueIdentifier('page__topics__become-a-member_btn') ?> href="<?php echo __SITE_URL . 'register';?>"><?php echo translate('about_us_certification_and_upgrade_benefits_become_a_member_block_register_btn');?></a>
        <?php }?>
    </div>

    <div class="info-block__image">
        <img class="image" src="<?php echo __IMG_URL . 'public/img/footers-info-pages/become_member.jpg';?>" alt="International trade">
    </div>

</div>
