<div class="mobile-links">
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#about-flex-card__fixed-left">
        <i class="ep-icon ep-icon_items"></i>
        Menu
    </a>
</div>

<div class="news news--newsletter-archive">
    <?php if (!empty($newsletter_archive)) { ?>
        <div class="news__headline">
            <h1>
                <?php echo !empty($selected_year) ? "Newsletters released in {$selected_year}" : "Last Uploaded Newsletters" ?>
            </h1>

            <div class="flex-display flex-ai--c">
                <span class="minfo-save-search__ttl">Year</span>
                <div class="dropdown show dropdown--select">
                    <a class="dropdown-toggle"
                       href="#"
                       role="button"
                       id="ambasadorSortByLinks"
                       data-toggle="dropdown"
                       aria-haspopup="true"
                       aria-expanded="false">
                        <?php echo !empty($selected_year) ? $selected_year : "All" ?>
                        <i class="ep-icon ep-icon_arrow-down"></i>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="ambasadorSortByLinks">
                        <?php foreach($selector_links as $year => $link) { ?>
                            <a class="dropdown-item" href="<?php echo $link ?>"><?php echo $year ?></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-eq-height">
            <?php tmvc::instance()->controller->view->display('new/newsletter/list_view');?>
        </div>
        <div class="col-12">
            <div class="pt-10 flex-display flex-jc--sb flex-ai--c">
                <?php tmvc::instance()->controller->view->display("new/paginator_view"); ?>
            </div>
        </div>
    <?php } else { ?>
        There are no newsletter archive at the moment.
    <?php } ?>
</div>
