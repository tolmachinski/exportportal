<!-- Section Header -->
<section class="page-main-heading container-1420">
    <div class="page-main-heading__container">
        <h1 class="page-main-heading__title"><?php echo translate('b2b_all_header_text'); ?></h1>

        <?php if (logged_in() && i_have_company() && have_right('manage_b2b_requests')) {?>
            <a class="page-main-heading__btn btn btn-new16 btn-primary" href="<?php echo __SITE_URL . 'b2b/reg';?>">
                <?php echo translate('b2b_all_add_request_btn'); ?>
            </a>
        <?php }?>

        <picture class="page-main-heading__picture">
            <source
                media="(max-width: 575px)"
                srcset="<?php echo asset('public/build/images/b2b/b2b-list-header-mobile.jpg'); ?>"
                data-srcset="<?php echo asset('public/build/images/b2b/b2b-list-header-mobile.jpg'); ?> 1x,
                <?php echo asset('public/build/images/b2b/b2b-list-header-mobile@2x.jpg'); ?> 2x"
            >
            <source
                media="(max-width: 991px)"
                srcset="<?php echo asset('public/build/images/b2b/b2b-list-header-tablet.jpg'); ?>"
                data-srcset="<?php echo asset('public/build/images/b2b/b2b-list-header-tablet.jpg'); ?> 1x,
                <?php echo asset('public/build/images/b2b/b2b-list-header-tablet@2x.jpg'); ?> 2x"
            >
            <img
                class="page-main-heading__image"
                src="<?php echo asset('public/build/images/b2b/b2b-list-header.jpg'); ?>"
                srcset="<?php echo asset('public/build/images/b2b/b2b-list-header.jpg'); ?> 1x,
                <?php echo asset('public/build/images/b2b/b2b-list-header@2x.jpg'); ?> 2x"
                alt="B2B requests header"
            >
        </picture>
    </div>
</section>
<!-- End Section Header -->
