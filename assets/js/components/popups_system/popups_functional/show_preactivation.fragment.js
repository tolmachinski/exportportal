import $ from "jquery";
import { closeAllDialogs } from "@src/plugins/bootstrap-dialog/index";
import EventHub from "@src/event-hub";
import sentPopupViewed from "@src/util/common/send-popup-viewed";

export default () => {
    $(() => {
        EventHub.off("modal:call-show-main-chat");
        EventHub.on("modal:call-show-main-chat", () => {
            $(".js-btn-call-main-chat").trigger("click");
        });

        EventHub.off("popup:confirm-show-preactivation");
        EventHub.on("popup:confirm-show-preactivation", () => {
            const checkbox = $(".js-what-next");
            if (checkbox.length) {
                if (checkbox.prop("checked")) {
                    sentPopupViewed("show_preactivation");
                }
            }

            closeAllDialogs();
        });
    });
};
