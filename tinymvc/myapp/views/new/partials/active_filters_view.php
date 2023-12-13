<div class="filter-block">
    <h3 class="filter-block__ttl"><?php echo translate('sidebar_active_filters_title'); ?></h3>

    <ul class="active-filters-params">
        <?php foreach ($searchParams as $item) {?>
            <li class="active-filters-params__item">
                <p class="active-filters-params__name"><?php echo $item['title']; ?>:</p>

                <?php if (!empty($item['subParams'])) {?>
                    <ul class="active-filters-params__sub">
                        <?php foreach ($item['subParams'] as $searchSubParam) {?>
                        <li class="active-filters-params__sub-item">
                            <p class="active-filters-params__sub-name"><?php echo $searchSubParam['title']; ?></p>
                            <a class="active-filters-params__sub-remove-btn" href="<?php echo $searchSubParam['link']; ?>">
                                <?php echo widgetGetSvgIcon('plus', 11, 11); ?>
                            </a>
                        </li>
                        <?php }?>
                    </ul>
                <?php }?>
            </li>
        <?php } ?>

        <li>
            <a
                class="active-filters-params__clear-btn btn btn-light btn-block btn-new16"
                href="<?php echo get_dynamic_url($searchParamsLinksTpl['filter']); ?>"
            >
                <?php echo translate('clear_all_filters_btn'); ?>
            </a>
        </li>
    </ul>
</div>
