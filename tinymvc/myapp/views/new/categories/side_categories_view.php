<div id="js-sidebar-categories" class="popup-categories">
    <div id="js-sidebar-categories-wr" class="popup-categories__wr">
        <div id="js-side-categories-group-mep" class="popup-categories-mep display-n_i">
            <ul class="popup-categories-group display-n_i" data-step="1"></ul>
            <div class="popup-categories-selected">
                <div class="js-wr-category-group popup-categories-selected__col display-n_i" data-step="2"></div>
                <div class="js-wr-category-group popup-categories-selected__col display-n_i" data-step="3"></div>
                <div class="js-wr-category-group popup-categories-selected__col display-n_i" data-step="4"></div>
            </div>

            <div class="popup-categories-group__item">
                <a class="popup-categories-group__name" href="<?php echo __SITE_URL . 'categories'; ?>">
                    <span class="popup-categories-group__icon popup-categories-group__icon--left">
                        <i class="ep-icon ep-icon_arrow-left"></i>
                    </span>
                    <?php echo translate("side_categories_all_categories_btn"); ?>
                    <span class="popup-categories-group__icon popup-categories-group__icon--right">
                        <i class="ep-icon ep-icon_arrow-right"></i>
                    </span>
                </a>
            </div>
        </div>

        <ul
            id="js-sidebar-categories-group"
            class="js-wr-category-group popup-categories-group"
            data-step="1"
            <?php echo addQaUniqueIdentifier('global__side-categories__first-step-list'); ?>
        >
            <?php foreach($categoriesGroup as $categoriesGroupItem) { ?>
                <li
                    class="popup-categories-group__item call-action"
                    data-js-action="side-categories:group-main-select"
                    data-category="<?php echo $categoriesGroupItem['id_group'];?>"
                >
                    <a class="popup-categories-group__name" href="#" data-group="<?php echo $categoriesGroupItem['id_group'];?>">
                        <span class="popup-categories-group__icon popup-categories-group__icon--left">
                            <i class="ep-icon ep-icon_arrow-left"></i>
                        </span>
                        <?php echo $categoriesGroupItem['title'];?>
                        <span class="popup-categories-group__icon popup-categories-group__icon--right">
                            <i class="ep-icon ep-icon_arrow-right"></i>
                        </span>
                    </a>
                </li>
            <?php } ?>

            <li class="popup-categories-group__item">
                <a class="popup-categories-group__name" href="<?php echo __SITE_URL . 'categories'; ?>">
                    <span class="popup-categories-group__icon popup-categories-group__icon--left">
                        <i class="ep-icon ep-icon_arrow-left"></i>
                    </span>
                    <?php echo translate("side_categories_all_categories_btn"); ?>
                    <span class="popup-categories-group__icon popup-categories-group__icon--right">
                        <i class="ep-icon ep-icon_arrow-right"></i>
                    </span>
                </a>
            </li>
        </ul>

        <div id="js-sidebar-categories-selected" class="popup-categories-selected">
            <div class="popup-categories-selected__row">
                <div id="js-first-side-category-list" class="js-wr-category-group popup-categories-selected__col display-n_i" data-step="2"></div>
                <div id="js-center-side-category-list" class="js-wr-category-group popup-categories-selected__col display-n_i" data-step="3"></div>
                <div id="js-last-side-category-list" class="js-wr-category-group popup-categories-selected__col display-n_i" data-step="4"></div>
            </div>
        </div>
    </div>

    <script type="text/template" id="js-template-side-categories-group-list">
        <ul class="popup-categories-list js-sidebar-categories-list" data-list-category="{{ID}}">
            {{ITEM}}
        </ul>
    </script>

    <script type="text/template" id="js-template-side-categories-group-list-item">
        <li class="popup-categories-list__item">
            {{LINK}}
        </li>
    </script>

    <script type="text/template" id="js-template-side-categories-group-list-item-toggle">
        <li class="popup-categories-list__item">
            {{LINK}}
            {{LIST}}
        </li>
    </script>

    <?php if(!isset($webpackData)) { ?>
        <script src="<?php echo fileModificationTime('public/plug/js/categories/open-age-verification.js'); ?>"></script>
    <?php } ?>
</div>

