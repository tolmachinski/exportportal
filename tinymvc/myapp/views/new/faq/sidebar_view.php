<script src="<?php echo fileModificationTime('public/plug/hidemaxlistitem-1-3-4/hideMaxListItem-min.js'); ?>"></script>
<script>
    $(document).ready(function() {
        $('.hide-max-list').hideMaxListItems();
    });
</script>

<?php if(!empty($search_params)){?>
    <h3 class="minfo-sidebar-ttl">
        <span class="minfo-sidebar-ttl__txt">Active Filters</span>
    </h3>

    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <ul class="minfo-sidebar-params">
                <?php foreach($search_params as $item){?>
                    <li class="minfo-sidebar-params__item">
                        <div class="minfo-sidebar-params__ttl">
                            <div class="minfo-sidebar-params__name"><?php echo $item['param']?>:</div>
                        </div>

                        <ul class="minfo-sidebar-params__sub">
                            <li class="minfo-sidebar-params__sub-item">
                                <div class="minfo-sidebar-params__sub-ttl"><?php echo $item['title']?></div>
                                <a class="minfo-sidebar-params__sub-close ep-icon ep-icon_remove-stroke" href="<?php echo $item['link']; if($item['param'] != 'Keywords') echo $get_params;?>"></a>
                            </li>
                        </ul>
                    </li>
                <?php } ?>

                <li>
                    <a class="btn btn-light btn-block txt-blue2" href="faq">Clear all</a>
                </li>
            </ul>
        </div>
    </div>
<?php } ?>

<?php if (!empty($faq_other_tags_list)) { ?>
    <h3 class="minfo-sidebar-ttl">
        <span class="minfo-sidebar-ttl__txt">OTHER TAGS</span>
    </h3>

    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <ul class="hide-max-list minfo-sidebar-box__list">

                <?php foreach($faq_other_tags_list as $id_tag => $tag){?>
                    <li class="minfo-sidebar-box__list-item">
                        <a
                            class="minfo-sidebar-box__list-link w-160"
                            href="<?php echo replace_dynamic_uri($tag['slug'], $tag_link, __SITE_URL . 'faq/all'); ?>"
                            <?php echo addQaUniqueIdentifier('page__faq__other-tage_name'); ?>
                        >
                            <?php echo cleanOutput($tag['name']); ?>
                        </a>
                        <span
                            class="minfo-sidebar-box__list-counter"
                            <?php echo addQaUniqueIdentifier('page__faq__other-tage_count'); ?>
                        ><?php echo cleanOutput($faq_tags_counters[$id_tag]['counter'] ?? 0); ?></span>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
<?php } ?>

<h3 class="minfo-sidebar-ttl">
    <span class="minfo-sidebar-ttl__txt">Nothing found?</span>
</h3>

<a href="<?php echo __SITE_URL; ?>contact" class="btn btn-outline-dark btn-block">Contact us</a>

<?php tmvc::instance()->controller->view->display('new/who_we_are_view');?>

<div class="dn-md_i" <?php echo addQaUniqueIdentifier('faq__banner-demo'); ?>>
    <?php echo widgetShowBanner('faq_sidebar', 'promo-banner-wr--faq'); ?>
</div>

<?php views()->display('new/subscribe/subscribe_view'); ?>
