import $ from "jquery";
import hideMaxList from "@src/plugins/hide-max-list/index";
import EventHub from "@src/event-hub";

import "@scss/user_pages/featured_items/index.scss";

$(() => {
    hideMaxList(".js-hide-max-list");

    EventHub.on("filters:multilist-toggle", async (e, btn) => {
        const { default: multilistToggle } = await import("@src/components/filter/multilist-toggle");
        multilistToggle(btn);
    });

    EventHub.on("form:submit_form_search", async (e, btn) => {
        const { default: searchByItem } = await import("@src/components/search-forms/fragments/search-by-item");
        searchByItem(e, btn);
    });
});
