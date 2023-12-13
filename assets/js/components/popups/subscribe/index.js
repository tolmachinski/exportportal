import { translate } from "@src/i18n";
import openSubscribtionPopup from "@src/components/popups/subscribe/open-subcription-popup";
import removeCookie from "@src/util/cookies/remove-cookie";
import existCookie from "@src/util/cookies/exist-cookie";
import getCookie from "@src/util/cookies/get-cookie";

const showConfirmSubscribtionPopup = ({ popupTitle, message }) => {
    return openSubscribtionPopup(popupTitle, message, "info");
};

const showSuccessSubscribtionPopup = message => {
    return openSubscribtionPopup(translate({ plug: "general_i18n", text: "subscribe_popup_success_txt" }), message, "success");
};

const showSuccessSubscribtionPopupIfNeeded = async () => {
    const isSubscribed = getCookie("_ep_subscriber_confirmed");

    if (isSubscribed) {
        let message = translate({ plug: "general_i18n", text: "js_subscribe_successfully_subscribed_message" });

        if (existCookie("_ep_success_subscribe_dm_message_key")) {
            message = translate({ plug: "general_i18n", text: getCookie("_ep_success_subscribe_dm_message_key") });
            removeCookie("_ep_success_subscribe_dm_message_key");
        }

        await showSuccessSubscribtionPopup(message);
        removeCookie("_ep_subscriber_confirmed");
    }
};

export { showConfirmSubscribtionPopup, showSuccessSubscribtionPopup };
export default showSuccessSubscribtionPopupIfNeeded;
