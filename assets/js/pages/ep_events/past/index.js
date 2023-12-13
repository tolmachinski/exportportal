import $ from "jquery";
import EventHub from "@src/event-hub";

/* css */
import "@scss/user_pages/events_page/ep_events_past_styles.scss";

$(() => {
    EventHub.on("form:submit_form_search", async (e, btn) => {
        const { default: searchByItem } = await import("@src/components/search-forms/fragments/search-by-item");
        searchByItem(e, btn);
    });
});
