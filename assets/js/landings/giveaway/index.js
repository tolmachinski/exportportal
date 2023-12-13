import $ from "jquery";
import "jquery-countdown";
import EventHub from "@src/event-hub";

import "@scss/landings/giveaway/critical.scss";
import "@scss/landings/giveaway/index.scss"; // content

const startCountDown = endDate => {
    $("#js-giveaway-countdown")
        .countdown(endDate, event => {
            const daysLeft = event.strftime("%D");

            if (daysLeft === "01") {
                $("#js-giveaway-countdown-days-txt").text("Day");
            }

            $("#js-giveaway-countdown-days-left").html(daysLeft);
            $("#js-giveaway-countdown-hours-left").html(event.strftime("%H"));
            $("#js-giveaway-countdown-minutes-left").html(event.strftime("%M"));
            $("#js-giveaway-countdown-seconds-left").html(event.strftime("%S"));
        })
        .on("finish.countdown", () => {
            globalThis.location.reload();
        });
};

$(() => {
    const countDownBlock = $("#js-giveaway-countdown");

    if (countDownBlock.data("giveawayEndDate")) {
        startCountDown(new Date(countDownBlock.data("giveawayEndDate")));
    }

    EventHub.on("giveaway:scroll-to", async (e, button) => {
        const { default: navScrollTo } = await import("@src/landings/giveaway/scroll-to");
        navScrollTo(button);
    });
});
