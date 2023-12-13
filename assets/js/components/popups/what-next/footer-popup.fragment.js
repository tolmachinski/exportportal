import $ from "jquery";
import setCookie from "@src/util/cookies/set-cookie";
import removeCookie from "@src/util/cookies/remove-cookie";
import EventHub from "@src/event-hub";

export default (cookieName = "") => {
    const checkbox = $(".js-what-next");

    checkbox.on("change", () => {
        // eslint-disable-next-line no-unused-expressions
        checkbox.prop("checked") ? setCookie(cookieName, 1, { expires: 7 }) : removeCookie(cookieName);
    });

    EventHub.off("modal:call-show-main-chat");
    EventHub.on("modal:call-show-main-chat", () => {
        $(".js-btn-call-main-chat").trigger("click");
    });

    EventHub.off("modal:call-close-modal");
    EventHub.on("modal:call-close-modal", async () => {
        const { closeAllDialogs } = await import("@src/plugins/bootstrap-dialog/index");
        closeAllDialogs();

        globalThis.bootstrapDialogCloseAll?.();
    });
};
