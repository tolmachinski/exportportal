import $ from "jquery";

import { enableFormValidation } from "@src/plugins/validation-engine/index";
import { BACKSTOP_TEST_MODE } from "@src/common/constants";

import EventHub from "@src/event-hub";
import delay from "@src/util/async/delay";
import loadingValidationEngine from "@src/plugins/validation-engine/lazy";

const hideSuggestions = function () {
    $(".js-search-autocomplete-container").css("display", "none");
};

export default params => {
    const searchBlock = $("#js-ep-header-content-search");
    const searchWrap = $(".js-ep-header-search");
    searchBlock.on("click focusout", ".validengine input", loadingValidationEngine);
    searchBlock.on("submit", ".validengine", loadingValidationEngine);

    EventHub.on("navbar-search-form:init-autocomplete", async (_e, input) => {
        const { default: initAutocomplete } = await import("@src/plugins/autocomplete/index");
        await enableFormValidation(input.closest("form"));

        input.removeClass("call-action");

        await initAutocomplete(params);
        await delay(50); // For IOS

        input.focus();
    });

    EventHub.on("navbar-search-form:on-select-search-type", async (_e, dropdownItem) => {
        const { default: onClickDropdownItem } = await import("@src/components/navbar-search-form/navbar-search-form-dropdown");
        onClickDropdownItem(dropdownItem);
    });

    EventHub.on("autocomplete:form.submit", async (e, form) => {
        const { default: searchByItem } = await import("@src/components/search-forms/fragments/search-by-item");
        searchByItem(e, form);
    });

    if (!BACKSTOP_TEST_MODE) {
        $(window).on("resize", () => {
            if ($(window).width() > 991) {
                $(document).on("click", function onClick(e) {
                    // @ts-ignore
                    if (!searchWrap.is(e.target) && searchWrap.has(e.target).length === 0) {
                        hideSuggestions();
                    }
                });

                $(document).on("scroll", hideSuggestions);

                $(".js-dropdown-toggle-btn").on("click", hideSuggestions);
            }
        });
    }
};
