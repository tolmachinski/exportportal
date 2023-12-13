<div class="epl-resources-header">
    <div class="container-center epl-resources-header__inner">
        <h1 class="epl-resources-header__ttl"><?php echo translate('epl_ff_resources_header_title'); ?></h1>
        <p class="epl-resources-header__text"><?php echo translate('epl_ff_resources_header_subtitle'); ?></p>
    </div>

    <picture class="epl-resources-header__bg">
        <source media="(max-width: 575px)" srcset="<?php echo asset('public/build/images/epl/resources/header_mobile.jpg'); ?>">
        <source media="(max-width: 1200px)" srcset="<?php echo asset('public/build/images/epl/resources/header_tablet.jpg'); ?>">
        <img
            class="image"
            width="1920"
            height="630"
            src="<?php echo asset('public/build/images/epl/resources/header.jpg'); ?>"
            alt="<?php echo translate('epl_ff_resources_header_title', null, true); ?>"
        >
    </picture>
</div>
