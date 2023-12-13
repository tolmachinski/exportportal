import loadBootstrapDialog, { openHeaderImageModal } from "@src/plugins/bootstrap-dialog/index";
import { translate } from "@src/i18n";
import sentPopupViewed from "@src/util/common/send-popup-viewed";
import postRequest from "@src/util/http/post-request";

import { DEBUG, SUBDOMAIN_URL } from "@src/common/constants";

const openTermsOfUseUpdatedPopup = async () => {
    try {
        const { mess_type: messageType = "error", title, subTitle } = await postRequest(
            `${SUBDOMAIN_URL}popups/ajax_operations/terms_of_use_updated`
        );

        if (messageType !== "success") {
            return;
        }

        await loadBootstrapDialog();
        openHeaderImageModal({
            type: "warning",
            title,
            subTitle,
            classes: " inputs-40",
            isAjax: false,
            closable: true,
            buttons: [
                {
                    label: translate({ plug: "BootstrapDialog", text: "close" }),
                    cssClass: "btn btn-light",
                    action: dialog => {
                        dialog.close();
                    },
                },
            ],
            openCallBack: () => {
                sentPopupViewed("terms_of_use_updated");
            },
        });
    } catch (e) {
        if (DEBUG) {
            // eslint-disable-next-line no-console
            console.error(e);
        }
    }
};
export default openTermsOfUseUpdatedPopup;
