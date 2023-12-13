import EventHub from "@src/event-hub";
import { SITE_URL } from "@src/common/constants";
import { removePopupBanner } from "@src/components/popups_system/popup_util";
import sentPopupViewed from "@src/util/common/send-popup-viewed";

const submitAddItemPopup = () => {
    sentPopupViewed("add_item");
    globalThis.location.href = `${SITE_URL}items/my?popup_add=open`;
};

export default () => {
    EventHub.off("popup:close-add-item-popup");
    EventHub.on("popup:close-add-item-popup", (e, button) => {
        sentPopupViewed("add_item");
        removePopupBanner(button);
    });
    EventHub.off("popup:submit-add-item-popup");
    EventHub.on("popup:submit-add-item-popup", () => submitAddItemPopup());
};
