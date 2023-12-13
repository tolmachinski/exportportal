import $ from "jquery";

import EventHub from "@src/event-hub";
import sentPopupViewed from "@src/util/common/send-popup-viewed";

export default () => {
    let clickButton = true;
    const checkbox = $("#js-giveaway-contests-actions .js-giveaway-contests");

    EventHub.off("popup:close-giveaway-contests");
    EventHub.on("popup:close-giveaway-contests", (e, button) => {
        if (!clickButton) {
            return true;
        }
        clickButton = false;
        if (checkbox.prop("checked")) {
            sentPopupViewed("giveaway_contest");
        }

        setTimeout(() => {
            globalThis.location.href = button.attr("href");
        }, 1000);

        return true;
    });
};
