import $ from "jquery";
import hideMaxList from "@src/plugins/hide-max-list/index";
import EventHub, { removeListeners } from "@src/event-hub";
import selectSubcategory from "@src/pages/b2b/all-requests/fragments/b2b-select-subcategory";
import onSelectGoldenCategory from "@src/pages/b2b/all-requests/fragments/on-select-golden-category";

import "@scss/user_pages/b2b/b2b-requests-all.scss";

onSelectGoldenCategory();
selectSubcategory();

$(() => {
    hideMaxList(".js-hide-max-list-form-elements", {
        max: 1,
    });
    hideMaxList(".js-hide-max-list", {
        max: 5,
    });

    const industrySelect = $("#js-search-b2b-industry-select");

    $("#js-search-b2b-golden-categories-select").on("change", () => {
        industrySelect.val("");
        $("#js-search-b2b-category-select").val("");
        onSelectGoldenCategory();
    });

    industrySelect.on("change", () => {
        selectSubcategory();
    });

    EventHub.on("lazy-loading:b2b-search-form-validation", async () => {
        await import("@src/plugins/validation-engine/index").then(async ({ enableFormValidation }) => {
            await enableFormValidation($("#js-b2b-search-form"), {
                promptPosition: "topLeft",
                autoPositionUpdate: true,
                showArrow: false,
                addFailureCssClassToField: "validengine-border",
            });

            removeListeners("lazy-loading:b2b-search-form-validation");
        });
    });
    EventHub.on("b2b-search-form:submit", async () => {
        const { default: searchB2b } = await import("@src/pages/b2b/all-requests/fragments/search-b2b");
        searchB2b();
    });
});
