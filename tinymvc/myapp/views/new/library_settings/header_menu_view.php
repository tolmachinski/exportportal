<ul class="public-heading-nav" id="main-flex-card__fixed-left">
    <li class="public-heading-nav__item ">
        <a class="public-heading-nav__link <?php echo equals($library_page, 'trade_news', 'active');?>" href="<?php echo __SITE_URL;?>trade_news">Trade News</a>
    </li>
    <?php foreach ($configs_library as $lib_item) { ?>
        <li class="public-heading-nav__item">
            <a class="public-heading-nav__link <?php echo equals($library_page, $lib_item['link_public'], 'active');?>" href="<?php echo __SITE_URL.$lib_item['link_public'];?>">
                <?php echo $lib_item['lib_title'];?>
            </a>
        </li>
    <?php } ?>
    <!-- <li class="public-heading-nav__item ">
        <a class="public-heading-nav__link <?php echo equals($library_page, 'library_country_statistic', 'active');?>" href="<?php echo __SITE_URL;?>library_country_statistic">Export Import Statistic</a>
    </li> -->
    <!-- <li class="public-heading-nav__item">
        <a class="public-heading-nav__link <?php echo equals($library_page, 'library_customs', 'active');?>" href="<?php echo __SITE_URL;?>library_customs">Customs Performance</a>
    </li> -->
</ul>
