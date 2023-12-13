import loadBootstrapDialog, { openHeaderImageModal } from "@src/plugins/bootstrap-dialog/index";
import sentPopupViewed from "@src/util/common/send-popup-viewed";
import postRequest from "@src/util/http/post-request";

import { SUBDOMAIN_URL, DEBUG } from "@src/common/constants";

const openEventPromotionPopup = async () => {
    try {
        const { mess_type: messageType = "error", title, subTitle, titleImage, buttonUrl, buttonText } = await postRequest(
            `${SUBDOMAIN_URL}popups/ajax_operations/event_promotion`
        );
        if (messageType !== "success") {
            return;
        }

        await loadBootstrapDialog();
        openHeaderImageModal({
            classes: "bootstrap-dialog-black-icon",
            title,
            subTitle,
            titleImage,
            titleUppercase: true,
            isAjax: false,
            buttons: [
                {
                    label: buttonText,
                    cssClass: "btn btn-primary mnw-150",
                    action: () => {
                        globalThis.location.href = buttonUrl;
                    },
                },
            ],
            openCallBack: () => {
                sentPopupViewed("event_promotion");
            },
        });
    } catch (e) {
        if (DEBUG) {
            // eslint-disable-next-line no-console
            console.error(e);
        }
    }
};
export default openEventPromotionPopup;
