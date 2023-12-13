<div class="ep-header-search js-ep-header-search">
    <form
        class="validengine ep-header-search__form js-search-autocomplete-form inputs-40"
        data-js-action="autocomplete:form.submit"
        data-type="items"
        data-suggestions-url="<?php echo __CURRENT_SUB_DOMAIN_URL . 'autocomplete/ajax_get_item_suggestions'; ?>"
        <?php echo addQaUniqueIdentifier('global__header__navbar-search-form'); ?>
    >
        <div class="dropdown">
            <button
                class="dropdown-toggle js-dropdown-toggle-btn"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
                type="button"
                <?php echo addQaUniqueIdentifier('global__header__navbar-search-form_dropdown-toggle-btn'); ?>
            >
                <span class="js-select-search-text">
                    <?php echo translate('header_search_form_tab_items'); ?>
                </span>
                <?php echo widgetGetSvgIcon('arrowDown', 9, 9, 'ep-header-search__dropdown-icon'); ?>
            </button>

            <div class="dropdown-menu mep-header-search-menu js-select-search-by">
                <button
                    class="dropdown-item call-action active"
                    data-type="item"
                    data-js-action="navbar-search-form:on-select-search-type"
                    type="button"
                    <?php echo addQaUniqueIdentifier('global__header__navbar-search-form_dropdown-menu-items-btn'); ?>
                >
                    <?php echo translate('header_search_form_tab_items'); ?>
                </button>
                <button
                    class="dropdown-item call-action"
                    data-type="category"
                    data-js-action="navbar-search-form:on-select-search-type"
                    type="button"
                    <?php echo addQaUniqueIdentifier('global__header__navbar-search-form_dropdown-menu-category-btn'); ?>
                >
                    <?php echo translate('header_search_form_tab_categories'); ?>
                </button>
                <?php if(logged_in()) { ?>
                    <button
                        class="dropdown-item call-action"
                        data-type="b2b"
                        data-js-action="navbar-search-form:on-select-search-type"
                        type="button"
                        <?php echo addQaUniqueIdentifier('global__header__navbar-search-form_dropdown-menu-b2b-btn'); ?>
                    >
                        <?php echo translate('header_search_form_tab_b2b'); ?>
                    </button>
                <?php } ?>
                <button
                    class="dropdown-item call-action"
                    data-type="events"
                    data-js-action="navbar-search-form:on-select-search-type"
                    type="button"
                    <?php echo addQaUniqueIdentifier('global__header__navbar-search-form_dropdown-menu-events-btn'); ?>
                >
                    <?php echo translate('header_search_form_tab_events'); ?>
                </button>
                <button
                    class="dropdown-item call-action"
                    data-type="help"
                    data-js-action="navbar-search-form:on-select-search-type"
                    type="button"
                    <?php echo addQaUniqueIdentifier('global__header__navbar-search-form_dropdown-menu-help-btn'); ?>
                >
                    <?php echo translate('header_search_form_tab_help'); ?>
                </button>
                <button
                    class="dropdown-item call-action"
                    data-type="blogs"
                    data-js-action="navbar-search-form:on-select-search-type"
                    type="button"
                    <?php echo addQaUniqueIdentifier('global__header__navbar-search-form_dropdown-menu-blogs-btn'); ?>
                >
                    <?php echo translate('header_search_form_tab_blogs'); ?>
                </button>
            </div>
        </div>
        <div class="autocomplete js-search-autocomplete-wrapper">
            <div class="autocomplete__form-group form-group">
                <input
                    class="autocomplete__input js-search-autocomplete-field call-action"
                    type="text"
                    name="keywords"
                    role="combobox"
                    spellcheck="false"
                    autocomplete="off"
                    autocapitalize="off"
                    autocorrect="off"
                    aria-expanded="true"
                    aria-autocomplete="both"
                    aria-haspopup="false"
                    data-js-action="navbar-search-form:init-autocomplete"
                    data-autocomplete-type="<?php echo \App\Common\Autocomplete\TYPE_ITEMS_TEXT; ?>"
                    data-no-autocomplete-prop-refresh="1"
                    placeholder="<?php echo translate('header_search_form_input_keyword_placeholder'); ?>"
                    aria-label="<?php echo translate('header_search_form_items_input_keywords_placeholder', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('global__header__navbar-search-form_keywords-input'); ?>
                >

                <button
                    class="autocomplete__reset-btn call-action js-search-autocomplete-reset-btn"
                    type="button"
                    data-js-action="autocomplete:reset-btn.click"
                >
                    <i class="ep-icon ep-icon_remove-stroke"></i>
                </button>
            </div>

            <div class="autocomplete__inner hide js-search-autocomplete-container">
                <div class="autocomplete-suggestion">
                    <ul class="autocomplete-suggestion-list js-search-autocomplete-suggestions-list" role="listbox"></ul>
                </div>

                <div class="autocomplete-recent-search">
                    <div class="autocomplete-recent-search__heading">
                        <p class="autocomplete-recent-search__heading-txt">Recent Search</p>
                    </div>
                    <ul class="autocomplete-recent-search-list js-search-autocomplete-recent-search-list" role="listbox"></ul>
                </div>
            </div>
        </div>

        <button
            class="btn btn-primary ep-header-search__btn"
            type="submit"
            <?php echo addQaUniqueIdentifier('global__header__navbar-search-form_submit-btn'); ?>
        >
            <?php echo widgetGetSvgIcon('magnifier', 16, 16, 'ep-header-search__btn-icon'); ?>
            <span class="ep-header-search__btn-txt"><?php echo translate('header_search_btn'); ?></span>
        </button>
    </form>
</div>

<?php widgetSearchAutocomplete(App\Common\Autocomplete\TYPE_ITEMS); ?>
