import $ from "jquery";
import EventHub from "@src/event-hub";

const closeAutoLogoutModal = () => {
    EventHub.trigger("idleWorker:closeWarning", ["closeByBtn", $(".js-no-more-show").is(":checked")]);
};

export default () => {
    EventHub.off("close-autologout-modal:event", closeAutoLogoutModal);
    EventHub.on("close-autologout-modal:event", closeAutoLogoutModal);
};
