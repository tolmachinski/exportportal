<ul class="public-heading-nav" id="main-flex-card__fixed-left">
    <li class="public-heading-nav__item">
        <a class="public-heading-nav__link <?php echo equals("faq", $current_page, "active");?>" <?php echo addQaUniqueIdentifier('global__public-heading-nav__link') ?> href="<?php echo __SITE_URL;?>faq">
            <?php echo translate('help_nav_header_faq');?>
        </a>
    </li>
    <li class="public-heading-nav__item">
        <a class="public-heading-nav__link <?php echo equals("topics", $current_page, "active");?>" <?php echo addQaUniqueIdentifier('global__public-heading-nav__link') ?> href="<?php echo __SITE_URL;?>topics/help">
            <?php echo translate('help_nav_header_topics');?>
        </a>
    </li>
    <li class="public-heading-nav__item">
        <a class="public-heading-nav__link <?php echo equals("user_guide", $current_page, "active");?>" <?php echo addQaUniqueIdentifier('global__public-heading-nav__link') ?> href="<?php echo __SITE_URL;?>user_guide">
            <?php echo translate('help_user_guides');?>
        </a>
    </li>
    <!-- <li class="public-heading-nav__item">
        <a class="public-heading-nav__link <?php //echo equals("user_guide", $current_page, "active");?>" href="<?php //echo __SITE_URL;?>user_guide">
            <?php //echo translate('help_nav_header_documentation');?>
        </a>
    </li> -->
    <li class="public-heading-nav__item">
        <a class="public-heading-nav__link <?php echo equals("questions", $current_page, "active");?>" <?php echo addQaUniqueIdentifier('global__public-heading-nav__link') ?> href="<?php echo __COMMUNITY_URL;?>">
            <?php echo translate('help_nav_header_comunity_help');?>
        </a>
    </li>
    <li class="public-heading-nav__item ">
        <a class="public-heading-nav__link <?php echo equals("contact", $current_page, "active");?>" <?php echo addQaUniqueIdentifier('global__public-heading-nav__link') ?> href="<?php echo __SITE_URL;?>contact">
            <?php echo translate('help_contact_us');?>
        </a>
    </li>
</ul>
