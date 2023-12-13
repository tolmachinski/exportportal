import $ from "jquery";
import hideMaxList from "@src/plugins/hide-max-list/index";
import EventHub from "@src/event-hub";
import initializePromoProductsSliders from "@src/components/items/promo_products_sliders/index";

import "@scss/user_pages/search/index.scss";

initializePromoProductsSliders();

$(() => {
    hideMaxList(".js-hide-max-list");

    EventHub.on("filters:multilist-toggle", async (_e, btn) => {
        const { default: multilistToggle } = await import("@src/components/filter/multilist-toggle");
        multilistToggle(btn);
    });

    EventHub.on("form:submit_form_search", async (e, btn) => {
        const { default: searchByItem } = await import("@src/components/search-forms/fragments/search-by-item");
        searchByItem(e, btn);
    });
});
