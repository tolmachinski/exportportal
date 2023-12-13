<section class="home-section latest-items container-1420">
    <div class="section-header">
        <h2 class="section-header__title"><?php echo translate('home_latest_items_title'); ?></h2>
        <a
            class="section-header__link"
            href="<?php echo __SITE_URL . 'items/latest'; ?>"
            <?php echo addQaUniqueIdentifier('home__latest-items_view-all-link'); ?>
        >
            <?php echo translate('home_latest_items_title_link'); echo widgetGetSvgIcon('arrowRight', 15, 15); ?>
        </a>
    </div>
    <div class="latest-items__flex-wrap js-latest-items-wrapper">
        <div class="add-items-card">
            <div class="add-items-card__icon">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="54"
                    height="65"
                    viewBox="0 0 54 65">
                    <g transform="translate(-32)">
                        <g transform="translate(32 0)">
                            <path
                                d="M39.463,1.592V16.5H54.822V13.9L42.256,1.592Z"
                                transform="translate(-9.37 -0.345)"
                                fill="#fff"/>
                                <path d="M69.484,15.469v7.469H84.226V60.285a2.481,2.481,0,0,1-2.457,2.49H42.457A2.481,2.481,0,0,1,40,60.285V10.49A2.481,2.481,0,0,1,42.457,8H69.484Z"
                                transform="translate(-38.772 -6.753)"
                                fill="#d2e6fe"
                            />
                            <path
                                d="M74.585,57.269a3.7,3.7,0,0,0,3.65-3.735V13.695a1.257,1.257,0,0,0-.356-.88L65.711.365A1.2,1.2,0,0,0,64.851,0H35.65A3.7,3.7,0,0,0,32,3.735v49.8a3.7,3.7,0,0,0,3.65,3.735ZM63.634,2.49h.713L75.8,14.21v.73H63.634Zm-29.2,51.044V3.735A1.233,1.233,0,0,1,35.65,2.49H61.2V16.185a1.232,1.232,0,0,0,1.217,1.245H75.8v36.1a1.233,1.233,0,0,1-1.217,1.245H35.65A1.233,1.233,0,0,1,34.433,53.534Z"
                                transform="translate(-32 0)"
                                fill="#2181f8"
                            />
                            <ellipse
                                cx="11.957"
                                cy="12.139"
                                rx="11.957"
                                ry="12.139"
                                transform="translate(28.624 39.088)"
                                fill="#fff"
                            />
                            <path
                                d="M109.514,171.437A13.72,13.72,0,1,0,96,157.719,13.633,13.633,0,0,0,109.514,171.437Zm0-24.943a11.226,11.226,0,1,1-11.057,11.224A11.155,11.155,0,0,1,109.514,146.494Z"
                                transform="translate(-69.027 -106.437)"
                                fill="#007aff"
                            />
                            <path
                                d="M145.229,199.483h3.686v3.741a1.229,1.229,0,1,0,2.457,0v-3.741h3.686a1.247,1.247,0,0,0,0-2.494h-3.686v-3.741a1.229,1.229,0,1,0-2.457,0v3.741h-3.686a1.247,1.247,0,0,0,0,2.494Z"
                                transform="translate(-109.656 -146.955)"
                                fill="#007aff"
                            />
                        </g>
                    </g>
                </svg>
            </div>
            <h3 class="add-items-card__title"><?php echo translate('home_latest_items_add_item_card_title'); ?></h3>
            <p class="add-items-card__desc"><?php echo translate('home_latest_items_add_item_card_desc'); ?></p>
            <a
                class="btn btn-primary btn-block btn-new18"
                href="<?php echo get_static_url('items/choose_category'); ?>"
                <?php echo addQaUniqueIdentifier('home__latest-items_add-item-card_ulpoad-btn'); ?>
            >
                <?php echo translate('home_latest_items_add_item_card_upload_btn'); ?>
            </a>
        </div>
        <div class="latest-items__content">
            <div
                class="products products--slider-full js-logged-latest-items loading"
                data-lazy-name="latest-items-logged"
                <?php echo addQaUniqueIdentifier('home__latest-items-slider'); ?>
            >
            </div>
            <a
                class="latest-items__btn btn btn-primary btn-block btn-new18"
                href="<?php echo __SITE_URL . 'items/latest'; ?>"
                <?php echo addQaUniqueIdentifier('home__latest-view-all-items-btn'); ?>
            >
                <?php echo translate('home_latest_items_view_all_items_btn'); ?>
            </a>
        </div>
    </div>
    <?php views('new/partials/ajax_loader_view'); ?>
</section>
