<div class="pr-15">
    <?php if(!empty($counter_category)){?>
        <h2 class="community-sidebar-title mb-5"><?php echo translate('community_by_category_text'); ?></h2>

        <ul class="sidebar-categories-list" id="js-categories-list">
            <?php
                foreach ($quest_cats as $id_category => $data_category) {
                    if (!isset($counter_category[$id_category])){
                        continue;
                    }
            ?>
            <li class="sidebar-categories-list__item">
                <a class="sidebar-categories-list__link"
                    title="<?php echo $data_category['title_cat'] ?>"
                    href="<?php echo replace_dynamic_uri($data_category['url'], $links_tpl[$questions_uri_components['category']], __COMMUNITY_ALL_URL); ?>">
                    <?php echo $data_category['title_cat'] ?>
                </a>
                <span class="sidebar-categories-list__counter"><?php echo $counter_category[$id_category];?></span>
            </li>
            <?php }?>
        </ul>
    <?php } ?>

    <?php if(!empty($counter_country)){?>
        <h2 class="community-sidebar-title mt-30 mb-5"><?php echo translate('community_by_country_text'); ?></h2>

        <ul class="sidebar-countries-list mb-30" id="js-countries-list">
            <?php foreach ($countries as $id_country => $data_country) {
                    if (!isset($counter_country[$id_country])) {
                        continue;
                    }
            ?>
            <li class="sidebar-countries-list__item">
                <span class="sidebar-countries-list__flag">
                    <a
                        href="<?php echo replace_dynamic_uri(strForURL($data_country['country'] . ' ' . $id_country), $links_tpl[$questions_uri_components['country']], __COMMUNITY_ALL_URL);?>"
                        title="<?php echo $data_country['country']; ?>">
                        <img
                            class="image js-lazy"
                            width="24"
                            height="24"
                            data-src="<?php echo getCountryFlag($data_country['country']);?>"
                            src="<?php echo getLazyImage(24, 24); ?>"
                            alt="<?php echo $data_country['country']; ?>"
                            title="<?php echo $data_country['country']; ?>" />
                    </a>
                    <a
                        class="sidebar-countries-list__flag-name"
                        href="<?php echo replace_dynamic_uri(strForURL($data_country['country'] . ' ' . $id_country), $links_tpl[$questions_uri_components['country']], __COMMUNITY_ALL_URL);?>"
                        title="<?php echo $data_country['country']; ?>">
                        <?php echo $data_country['country']; ?>
                    </a>
                </span>
                <span class="sidebar-countries-list__counter"><?php echo $counter_country[$id_country];?></span>
            </li>
            <?php }?>
        </ul>
    <?php } ?>
</div>
