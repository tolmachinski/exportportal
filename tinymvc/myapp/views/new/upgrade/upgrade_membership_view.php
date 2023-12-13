<?php
    $membershipBenefitsList = [
        [
            'img' => 'socials.svg',
            'title' => translate('upgrade_membership_benefits_1_title'),
            'text' => translate('upgrade_membership_benefits_1_text'),
        ],
        [
            'img' => 'link.svg',
            'title' => translate('upgrade_membership_benefits_2_title'),
            'text' => translate('upgrade_membership_benefits_2_text'),
        ],
        [
            'img' => 'user-info.svg',
            'title' => translate('upgrade_membership_benefits_3_title'),
            'text' => translate('upgrade_membership_benefits_3_text'),
        ],
        [
            'img' => 'partners.svg',
            'title' => translate('upgrade_membership_benefits_4_title'),
            'text' => translate('upgrade_membership_benefits_4_text'),
        ],
        [
            'img' => 'certificate.svg',
            'title' => translate('upgrade_membership_benefits_5_title'),
            'text' => translate('upgrade_membership_benefits_5_text'),
        ],
        [
            'img' => 'exima_all.svg',
            'title' => translate('upgrade_membership_benefits_6_title'),
            'text' => translate('upgrade_membership_benefits_6_text'),
        ],
        [
            'img' => 'partners.svg',
            'title' => translate('upgrade_membership_benefits_7_title'),
            'text' => translate('upgrade_membership_benefits_7_text'),
        ],

        [
            'img' => 'assistance.svg',
            'title' => translate('upgrade_membership_benefits_8_title'),
            'text' => translate('upgrade_membership_benefits_8_text'),
        ],

    ];
?>
<div class="upgrade-membership">
    <?php foreach($membershipBenefitsList as $membershipBenefitsListItem){?>
        <div class="upgrade-membership__column">
            <div class="upgrade-membership__icon">
                <img
                    class="image"
                    src="<?php echo __IMG_URL; ?>/public/img/upgrade_page/svg/new/<?php echo $membershipBenefitsListItem['img'];?>"
                    alt="<?php echo $membershipBenefitsListItem['title'];?>"
                >
            </div>
            <div class="upgrade-membership__info">
                <div class="upgrade-membership__title"><?php echo $membershipBenefitsListItem['title'];?></div>
                <div class="upgrade-membership__text">
                    <p><?php echo $membershipBenefitsListItem['text'];?></p>
                </div>
            </div>
        </div>
    <?php }?>
</div>
