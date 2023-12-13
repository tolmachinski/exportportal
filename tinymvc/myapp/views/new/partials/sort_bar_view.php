<!-- Filter btn for sidebar -->
<?php views('new/partials/filter_btn_view'); ?>

<div class="sort-bar">
    <div class="sort-bar__item">
        <button
            class="<?php echo logged_in() ? '' : 'js-require-logged-systmess '; ?>sort-bar__btn fancybox.ajax fancyboxValidateModal"
            data-title="Save Search"
            data-fancybox-href="<?php echo __SITE_URL . 'save_search/popup_save_search/category/?curr_link=' . urlencode(__CURRENT_URL); ?>"
            title="Save Search"
            type="button"
        >
            Save Search
        </button>
    </div>

    <?php if (count($items)) { ?>
        <div class="sort-bar__group">

            <div class="sort-bar__item">
                <span class="sort-bar__text sort-bar__text--gray sort-bar__text--hide-sm">Sort by</span>

                <div class="sort-bar__dropdown-sort-by dropdown dropdown--select">
                    <button
                        id="categorySortByLinks"
                        class="dropdown-toggle dropdown-toggle--center"
                        data-toggle="dropdown"
                        data-display="static"
                        aria-haspopup="true"
                        aria-expanded="false"
                        type="button"
                    >
                        <?php echo $sortByLinks['items'][$sortByLinks['selected']]; ?>
                        <?php echo getEpIconSvg('arrow-down', [12, 10]); ?>
                    </button>

                    <div class="dropdown-menu" aria-labelledby="categorySortByLinks">
                        <?php foreach ($sortByLinks['items'] as $sortByLinkKey => $sortByLink) {?>
                            <?php $linkKey = (isset($sortByLinks['default']) && $sortByLinks['default'] === $sortByLinkKey) ? '' : $sortByLinkKey; ?>
                            <a class="dropdown-item" href="<?php echo replace_dynamic_uri($linkKey, $linksTpl['sort_by']); ?>">
                                <?php echo $sortByLink; ?>
                            </a>
                        <?php }?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
