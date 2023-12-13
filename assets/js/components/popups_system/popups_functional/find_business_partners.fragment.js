import $ from "jquery";

import EventHub from "@src/event-hub";
import sentPopupViewed from "@src/util/common/send-popup-viewed";
import { closeBootstrapDialog } from "@src/plugins/bootstrap-dialog/index";

export default () => {
    let clickButton = true;
    const form = $("#js-find-business-partners-actions");
    const checkbox = $(".js-find-business-partners");

    EventHub.off("popup:close-find-business-partners");
    EventHub.on("popup:close-find-business-partners", (e, button) => {
        if (!clickButton) {
            return true;
        }
        clickButton = false;

        if (button[0].tagName === "BUTTON") {
            closeBootstrapDialog(form);
        } else {
            if (checkbox.prop("checked")) {
                sentPopupViewed("find_business_partners", "cancel");
            }
            setTimeout(() => {
                globalThis.location.href = button.attr("href");
            }, 1000);
        }

        return true;
    });
};
