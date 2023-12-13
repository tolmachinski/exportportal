<?php
    $benefits = [
        [
            'icon'        => asset('public/build/images/index/benefits/icons/members-benefits-one.svg'),
            'title'       => translate('home_members_benefits_first_title'),
            'description' => translate('home_members_benefits_first_desc'),
        ],
        [
            'icon'        => asset('public/build/images/index/benefits/icons/members-benefits-two.svg'),
            'title'       => translate('home_members_benefits_second_title'),
            'description' => translate('home_members_benefits_second_desc'),
        ],
        [
            'icon'        => asset('public/build/images/index/benefits/icons/members-benefits-three.svg'),
            'title'       => translate('home_members_benefits_third_title'),
            'description' => translate('home_members_benefits_third_desc'),
        ],
        [
            'icon'        => asset('public/build/images/index/benefits/icons/members-benefits-four.svg'),
            'title'       => translate('home_members_benefits_fourth_title'),
            'description' => translate('home_members_benefits_fourth_desc'),
        ],
        [
            'icon'        => asset('public/build/images/index/benefits/icons/members-benefits-five.svg'),
            'title'       => translate('home_members_benefits_fifth_title'),
            'description' => translate('home_members_benefits_fifth_desc'),
        ],
        [
            'icon'        => asset('public/build/images/index/benefits/icons/members-benefits-six.svg'),
            'title'       => translate('home_members_benefits_sixth_title'),
            'description' => translate('home_members_benefits_sixth_desc'),
        ],
    ];
?>

<section class="home-section members-benefits container-1420">
    <div class="section-header">
        <h2 class="section-header__title">
            <?php echo translate('home_members_benefits_header_title') ?>
        </h2>
    </div>
    <div class="members-benefits__content">
        <?php foreach ($benefits as $benefit) { ?>
            <div class="members-benefits__item">
                <img
                    class="members-benefits__icon js-lazy"
                    src="<?php echo getLazyImage(50, 50); ?>"
                    data-src="<?php echo $benefit['icon']; ?>"
                    alt="<?php echo $benefit['title']; ?>"
                >
                <h3 class="members-benefits__title"><?php echo $benefit['title']; ?></h3>
                <p class="members-benefits__description"><?php echo $benefit['description']; ?></p>
            </div>
        <?php } ?>
    </div>
</section>
