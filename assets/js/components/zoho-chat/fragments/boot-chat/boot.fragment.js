import $ from "jquery";
import { BACKSTOP_TEST_MODE } from "@src/common/constants";

import { Chat } from "@src/components/zoho-chat/fragments/boot-chat/chat";
import EventHub from "@src/event-hub";
import { isMobile } from "@src/util/platform";

/**
 * @param {boolean} connectGoogleTranslations
 * @param {Array<string>} domainLangs
 * @param {Array<string>} googleLangs
 * @param {Array<string>} availableLangs
 */
export default (code, domain, userName, userEmail, widgetUrl) => {
    const zohoChat = new Chat($(".js-btn-call-main-chat"), widgetUrl, code, domain, userName, userEmail);

    EventHub.off("zoho-chat:show");
    EventHub.on("zoho-chat:show", (e, button) => zohoChat.onShowMainChat(button));
    if (!BACKSTOP_TEST_MODE) {
        setTimeout(() => {
            if (!isMobile()) {
                zohoChat.onInitMainChat();
            }
        }, 9000);
    }
    Object.defineProperty(globalThis, "$zoho", { writable: false, value: zohoChat.zoho });
};
