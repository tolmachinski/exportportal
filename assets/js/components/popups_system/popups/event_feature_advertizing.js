import loadBootstrapDialog, { openHeaderImageModal } from "@src/plugins/bootstrap-dialog/index";
import sentPopupViewed from "@src/util/common/send-popup-viewed";
import postRequest from "@src/util/http/post-request";

import { DEBUG, SUBDOMAIN_URL } from "@src/common/constants";

const openEventFeatureAdvertizingPopup = async () => {
    try {
        const { mess_type: messageType = "error", title, subTitle, titleImage, content, buttonText } = await postRequest(
            `${SUBDOMAIN_URL}popups/ajax_operations/event_feature_advertizing`
        );
        if (messageType !== "success") {
            return;
        }

        await import("@scss/components/popups/event_feature_advertizing/index.scss");
        await loadBootstrapDialog();
        openHeaderImageModal({
            classes: "bootstrap-dialog-black-icon",
            title,
            content,
            subTitle,
            titleImage,
            titleUppercase: true,
            isAjax: false,
            buttons: [
                {
                    label: buttonText,
                    cssClass: "btn btn-dark",
                    action: dialog => {
                        dialog.close();
                    },
                },
            ],
            openCallBack: () => {
                sentPopupViewed("event_feature_advertizing");
            },
        });
    } catch (e) {
        if (DEBUG) {
            // eslint-disable-next-line no-console
            console.error(e);
        }
    }
};
export default openEventFeatureAdvertizingPopup;
