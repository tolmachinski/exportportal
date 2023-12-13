import $ from "jquery";
import EventHub from "@src/event-hub";

/* css */
import "@scss/user_pages/events_page/ep_events_styles.scss";

$(() => {
    EventHub.off("form:submit_form_search");
    EventHub.on("form:submit_form_search", async (e, btn) => {
        const { default: searchByItem } = await import("@src/components/search-forms/fragments/search-by-item");
        searchByItem(e, btn);
    });

    EventHub.off("notification:event");
    EventHub.on("notification:event", async (e, btn) => {
        const { default: notificationEvent } = await import("@src/pages/ep_events/fragments/notification-btn");
        notificationEvent(e, btn);
    });

    EventHub.off("notification:event-login");
    EventHub.on("notification:event-login", async (e, btn) => {
        const { default: notificationEventLogin } = await import("@src/pages/ep_events/fragments/notification-login");
        notificationEventLogin(e, btn);
    });

    EventHub.off("share:event");
    EventHub.on("share:event", async (e, btn) => {
        const { default: shareEvent } = await import("@src/pages/ep_events/fragments/share-btn");
        shareEvent(e, btn);
    });
});
