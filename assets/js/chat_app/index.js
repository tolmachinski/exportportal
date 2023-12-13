import $ from "jquery";

import "@src/boot/jquery-hooks";
import "@src/boot/http-api";

import openContactPopup from "@src/chat_app/iframe-contact/index";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import lockBody from "@src/util/dom/lock-body";
import Platform from "@src/chat_app/platform";
import EPLPlatform from "@src/epl/platform";
import EventHub from "@src/event-hub";
import delay from "@src/util/async/delay";

// @ts-ignore
import("@scss/chat_app/general_styles.scss");

/**
 * Handles the event of attaching the files to the room.
 *
 * @param {Array<any>} files
 * @param {string} roomId
 */
const onAttachFilesToTheRoom = (files, roomId) => {
    const iframe = Platform.iframePage || Platform.iframeRooms;
    if (iframe) {
        iframe[1].emit("uploadAttachFiles", { files, roomId });
    }
};

$(() => {
    const body = $(document.getElementsByTagName("body"));
    // Add missing listeners if main entrypoint is not loaded
    if (!globalThis.ENCORE_MODE) {
        // @ts-ignore
        globalThis.addEventListener("chat-app:external:attach-files", e => onAttachFilesToTheRoom(e?.detail?.files ?? [], e?.detail?.roomId ?? null));
    }

    import(/* webpackChunkName: "fancybox-util-chunk" */ "@src/plugins/fancybox/v2/util").then(
        ({ closeFancyboxPopup, closeFancyBoxConfirm, onChangePopupContent }) => {
            // Add missing listeners if main entrypoint is not loaded
            if (!globalThis.ENCORE_MODE) {
                EventHub.on("fancy-box:close", () => closeFancyBoxConfirm());
                body.on("change", ".fancybox-inner form :input", () => onChangePopupContent());
            }

            globalThis.addEventListener("sample-order:order-assgined", () => closeFancyboxPopup());
            globalThis.addEventListener("sample-order:order-created", () => closeFancyboxPopup());
        }
    );

    const openChat = async btn => {
        btn.toggleClass("chat-active");
        Platform.iframeRooms[1].emit("toggleChatFrame");

        const targetRoom = "chat-app-room";
        if (document.getElementById(targetRoom) && !document.getElementById(targetRoom).classList.contains("display-n")) {
            Platform.iframeRooms[1].emit("closeRoom");
            document.getElementById(targetRoom).classList.add("display-n");
        }
    };

    onResizeCallback(() => lockBody(globalThis.matchMedia("(max-width: 767px)").matches ? !$(".js-open-chat").hasClass("chat-active") : true), globalThis);

    EventHub.on("chat:open-chat-popup", async (e, btn) => {
        // Close another menu if open chat
        if (!btn.hasClass("chat-active")) {
            if (EPLPlatform.eplPage) {
                if ($("#js-epl-header-line.active").length) {
                    const { default: closeDashboardMobileMenu } = await import("@src/epl/components/navigation/fragments/close-dashboard-mobile-menu");
                    closeDashboardMobileMenu();
                }

                if ($("#js-epuser-subline:visible").length) {
                    const { default: hideDashboardMenu } = await import("@src/epl/components/dashboard/fragments/hide-dashboard-menu");
                    hideDashboardMenu();
                }
            } else {
                if ($("#js-epuser-subline:visible").length) {
                    const { default: hideDashboardMenu } = await import("@src/components/dashboard/fragments/hide-dashboard-menu");
                    hideDashboardMenu();
                }

                if ($("#js-mep-header-dashboard:visible").length) {
                    const { default: simpleHideHeaderBottom } = await import("@src/components/navigation/fragments/simple-hide-header-bottom");
                    simpleHideHeaderBottom();
                }

                if ($("#js-ep-header-top:visible").length) {
                    const { default: hideSideNav } = await import("@src/components/navigation/fragments/hide-side-nav");
                    hideSideNav();
                }

                if ($("#js-epuser-subline:visible, #js-mep-header-dashboard:visible, #js-ep-header-top:visible").length) {
                    await delay(500);
                }
            }
        }
        // Open chat
        openChat(btn);
    });
    EventHub.on("chat:open-contact-popup", (e, button) => openContactPopup(button));
    EventHub.on("room-message:upload-files", (e, { files, roomId }) => onAttachFilesToTheRoom(files, roomId));

    EventHub.on("chat:close-chat-popup", () => {
        const button = $(".js-open-chat");
        if (!button.hasClass("chat-active")) {
            return false;
        }

        openChat(button);

        return true;
    });
});
