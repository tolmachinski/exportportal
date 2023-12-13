<?php
    $count_countries = 0;
    $max_countries = 10;
?>
<?php if(!empty($counter_country)){?>
    <h2 class="community-sidebar-title"><?php echo translate('community_by_country_text'); ?></h2>

    <ul class="sidebar-countries-list" id="js-countries-list">
        <?php foreach ($countries as $id_country => $data_country) {
                if (!isset($counter_country[$id_country])) {
                    continue;
                }
                $count_countries++;
        ?>
        <li class="sidebar-countries-list__item <?php echo $count_countries > $max_countries ? " display-n_i" : ""?>" <?php echo $count_countries > $max_countries ? "data-minMax" : ""?>>
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
                        title="<?php echo $data_country['country']; ?>"
                        <?php echo addQaUniqueIdentifier('global__question-country-flag'); ?>
                    />
                </a>
                <a
                    class="sidebar-countries-list__flag-name"
                    href="<?php echo replace_dynamic_uri(strForURL($data_country['country'] . ' ' . $id_country), $links_tpl[$questions_uri_components['country']], __COMMUNITY_ALL_URL);?>"
                    title="<?php echo $data_country['country']; ?>"
                    <?php echo addQaUniqueIdentifier('global__question-country-name'); ?>
                >
                    <?php echo $data_country['country']; ?>
                </a>
            </span>
            <span class="sidebar-countries-list__counter" <?php echo addQaUniqueIdentifier('global__question-counter'); ?>>
                <?php echo $counter_country[$id_country];?>
            </span>
        </li>
        <?php }?>
    </ul>
    <?php if($count_countries > $max_countries) {?>
    <div class="maxlist-more">
        <button class="btn btn-light btn--50 btn-block call-action" <?php echo addQaUniqueIdentifier('global__sidebar__view-more-btn'); ?> data-js-action="minMax:toggle" data-target="js-countries-list" data-text="View more" data-text-toggled="View less">View more</button>
    </div>
<?php
    }
}?>
