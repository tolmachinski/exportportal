<div class="mblog-header">
    <div class="mblog-header__description-wrpapper">
        <div class="mblog-header__description">
            <h1 class="mblog-header__ttl"><?php echo translate('blog_main_title_1');?></h1>
            <p class="mblog-header__ttl-desc"><?php echo translate('blog_main_title_description_1');?></p>
        </div>
    </div>

    <picture class="mblog-header__picture">
        <source
            media="(max-width: 430px)"
            srcset="<?php echo asset("public/build/images/blog/header-320.jpg");?> 1x, <?php echo asset("public/build/images/blog/header-320@2x.jpg"); ?> 2x">
        <source
            media="(max-width: 480px)"
            srcset="<?php echo asset("public/build/images/blog/header-360.jpg");?> 1x, <?php echo asset("public/build/images/blog/header-360@2x.jpg"); ?> 2x">
        <source
            media="(max-width: 768px)"
            srcset="<?php echo asset("public/build/images/blog/header-768.jpg");?> 1x, <?php echo asset("public/build/images/blog/header-768@2x.jpg"); ?> 2x">
        <img
            class="mblog-header__image"
            width="1420"
            height="400"
            src="<?php echo asset("public/build/images/blog/header-1420.jpg");?>"
            srcset="<?php echo asset("public/build/images/blog/header-1420.jpg");?> 1x, <?php echo asset("public/build/images/blog/header-1420@2x.jpg"); ?> 2x"
            alt="<?php echo translate('blog_header_ttl', null, true); ?>"
        >
    </picture>
</div>
