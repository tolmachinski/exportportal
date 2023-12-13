<div class="filter-block">
    <h2 class="filter-block__ttl"><?php echo translate('blog_sidebar_filters_header'); ?></h2>

    <ul class="active-filters-params">
        <?php foreach ($search_params as $item) {?>
            <li class="active-filters-params__item">
                <p class="active-filters-params__name"><?php echo $item['param']; ?>:</p>

                <ul class="active-filters-params__sub">
                    <li class="active-filters-params__sub-item">
                        <p class="active-filters-params__sub-name"><?php echo $item['title']; ?></p>
                        <a class="active-filters-params__sub-remove-btn" href="<?php echo $item['link']; ?>">
                            <?php echo widgetGetSvgIcon('plus', 11, 11); ?>
                        </a>
                    </li>
                </ul>
            </li>
        <?php } ?>

        <li>
            <a
                class="active-filters-params__clear-btn btn btn-light btn-block btn-new16"
                href="<?php echo __BLOG_URL; ?>"
            >
                <?php echo translate('blog_sidebar_filters_button_clear'); ?>
            </a>
        </li>
    </ul>
</div>
