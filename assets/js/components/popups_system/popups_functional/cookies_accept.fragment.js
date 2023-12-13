import EventHub from "@src/event-hub";
import { removePopupBanner } from "@src/components/popups_system/popup_util";
import sentPopupViewed from "@src/util/common/send-popup-viewed";

const submitcookieBannerPopup = $this => {
    removePopupBanner($this);

    sentPopupViewed("cookies_accept");
};

export default () => {
    EventHub.off("popup:submit-cookie-banner");
    EventHub.on("popup:submit-cookie-banner", (e, button) => submitcookieBannerPopup(button));
};
