<div class="mobile-links">
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#about-flex-card__fixed-left">
        <i class="ep-icon ep-icon_items"></i>
        <?php echo translate('about_us_nav_menu_btn');?>
    </a>
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Sidebar" href="#main-flex-card__fixed-right" <?php echo addQaUniqueIdentifier("page__updates__sidebar_search-btn")?>>
        <i class="ep-icon ep-icon_items"></i>
        <?php echo translate('mobile_screen_sidebar_btn');?>
    </a>
</div>

<div class="news pt-90">
    <?php if (!empty($ep_updates)) {?>
        <div class="row row-eq-height">
            <?php views()->display('new/ep_updates/list_view');?>
        </div>
        <div class="col-12">
            <div class="pt-10 flex-display flex-jc--sb flex-ai--c">
                <?php views()->display("new/paginator_view"); ?>
            </div>
        </div>
    <?php } else {?>
        <?php if (empty($keywords) && $page == 1) {?>
            <?php echo translate('no_updates_at_the_moment');?>
        <?php } else {?>
            <?php if (empty($keywords)) {?>
                <?php echo translate('no_updates_on_the_page');?>
            <?php } else {?>
                <?php views()->display('new/search/cheerup_view');?>
            <?php }?>
        <?php }?>
    <?php } ?>
</div>
