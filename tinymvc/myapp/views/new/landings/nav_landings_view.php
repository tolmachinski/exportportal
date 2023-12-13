<script>
    document.addEventListener("DOMContentLoaded", function() {
        switchNavigationColorOnScroll();
    });

    document.addEventListener("scroll", function() {
        switchNavigationColorOnScroll();
    });

    function switchNavigationColorOnScroll() {
        var navigation = document.getElementsByClassName("amb-navigation")[0],
            navigation_height = navigation.offsetHeight,
            scroll_position = window.pageYOffset || document.documentElement.scrollTop;

        scroll_position > navigation_height ? navigation.classList.add('js-active') : navigation.classList.remove('js-active');
    }
</script>

<?php if ($currentPage && 'logistics_ambassador' === $currentPage) { ?>
    <div class="amb-navigation ambassador__navigation">
        <div class="amb-navigation__container ambassador__navigation-container">
            <a class="amb-navigation__logo ambassador__navigation-logo" href="<?php echo __SITE_URL; ?>" target="_blank">
                <span class="amb-navigation__logo-image ambassador__navigation-logo_image">
                    <img src="<?php echo __IMG_URL . 'public/img/ep-logo/ep-logo.png'; ?>" alt="Export Portal Logo">
                </span>
                <span class="amb-navigation__logo-text">Export Portal</span>
            </a>
            <div class="amb-navigation__row ambassador__navigation-row">
                <?php echo translate('landing_content_ambassador_menu_register_for_ep'); ?>

                <ul class="amb-navigation__list ambassador__navigation-list">
                    <li class="amb-navigation__list-item ambassador__navigation-list-item">
                        <a class="amb-navigation__link ambassador__navigation-link" href="<?php echo __SITE_URL . 'register/buyer'; ?>"><?php echo translate('landing_content_ambassador_menu_buyer'); ?></a>
                    </li>
                    <li class="amb-navigation__list-item ambassador__navigation-list-item">
                        <a class="amb-navigation__link ambassador__navigation-link" href="<?php echo __SITE_URL . 'register/seller'; ?>"><?php echo translate('landing_content_ambassador_menu_seller'); ?></a>
                    </li>
                    <li class="amb-navigation__list-item ambassador__navigation-list-item">
                        <a class="amb-navigation__link ambassador__navigation-link" href="<?php echo __SITE_URL . 'register/manufacturer'; ?>"><?php echo translate('landing_content_ambassador_menu_manufacturer'); ?></a>
                    </li>
                    <li class="amb-navigation__list-item ambassador__navigation-list-item">
                        <a class="amb-navigation__link ambassador__navigation-link" href="<?php echo __SHIPPER_URL . 'register/ff'; ?>"><?php echo translate('landing_content_ambassador_menu_shipper'); ?></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
<?php } else { ?>
    <div class="amb-navigation">
        <div class="amb-navigation__container">
            <a class="amb-navigation__logo" href="<?php echo __SITE_URL; ?>" target="_blank">
                <span class="amb-navigation__logo-image">
                    <img src="<?php echo __IMG_URL . 'public/img/ep-logo/ep-logo.png'; ?>" alt="Export Portal Logo">
                </span>
                <span class="amb-navigation__logo-text">Export Portal</span>
            </a>
            <div class="amb-navigation__row">
                <?php echo translate('landing_content_ambassador_menu_register_for_ep'); ?>

                <ul class="amb-navigation__list">
                    <li class="amb-navigation__list-item">
                        <a class="amb-navigation__link" href="<?php echo __SITE_URL . 'register/buyer'; ?>"><?php echo translate('landing_content_ambassador_menu_buyer'); ?></a>
                    </li>
                    <li class="amb-navigation__list-item">
                        <a class="amb-navigation__link" href="<?php echo __SITE_URL . 'register/seller'; ?>"><?php echo translate('landing_content_ambassador_menu_seller'); ?></a>
                    </li>
                    <li class="amb-navigation__list-item">
                        <a class="amb-navigation__link" href="<?php echo __SITE_URL . 'register/manufacturer'; ?>"><?php echo translate('landing_content_ambassador_menu_manufacturer'); ?></a>
                    </li>
                    <li class="amb-navigation__list-item">
                        <a class="amb-navigation__link" href="<?php echo __SHIPPER_URL . 'register/ff'; ?>"><?php echo translate('landing_content_ambassador_menu_shipper'); ?></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
<?php } ?>
