import $ from "jquery";
import "@scss/user_pages/events_page/ep_events_detail_styles.scss";
import "jquery-countdown";
import EventHub from "@src/event-hub";

$(() => {
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

    const sidebar = $(".js-detail-sidebar");

    if ($(window).scrollTop() > 140) {
        sidebar.addClass("events-detail-sidebar--active");
    }

    $(window).on("scroll", () => {
        if ($(window).scrollTop() > 140) {
            sidebar.addClass("events-detail-sidebar--active");

        } else {
            sidebar.removeClass("events-detail-sidebar--active");
        }
    });

    let startDate = $("[data-start-date]").data("startDate");

    if (startDate !== null) {
        startDate = new Date(startDate);

        $("#js-event-countdown")
            .countdown(startDate, event => {
                const daysLeft = event.strftime("%D");

                if (daysLeft === "01") {
                    $("#js-event-countdown-days-txt").text("Day");
                }

                $("#js-event-countdown-days-left").html(daysLeft);
                $("#js-event-countdown-hours-left").html(event.strftime("%H"));
                $("#js-event-countdown-minutes-left").html(event.strftime("%M"));
                $("#js-event-countdown-seconds-left").html(event.strftime("%S"));
            })
            .on("finish.countdown", () => {
                globalThis.location.reload();
            });
    }
});
