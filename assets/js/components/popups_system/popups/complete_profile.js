import $ from "jquery";

import { SUBDOMAIN_URL } from "@src/common/constants";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { translate } from "@src/i18n";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

const openCompleteProfilePopup = async () => {
    try {
        showLoader($("html"), "default", "fixed", 99999);
        const { mess_type: messType, title, subTitle, titleImage, content } = await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/complete_profile`);
        if (messType === "success") {
            await import("@scss/components/popups/complete_profile/index.scss");
            const { openHeaderImageModal } = await import("@src/plugins/bootstrap-dialog/index");
            openHeaderImageModal({
                content,
                title,
                titleImage,
                subTitle,
                titleUppercase: true,
                isAjax: false,
                buttons: [
                    {
                        label: () => translate({ plug: "BootstrapDialog", text: "close" }),
                        cssClass: "btn btn-dark",
                        action: dialog => dialog.close(),
                    },
                ],
                classes: "bootstrap-dialog--results-image-with-button bootstrap-dialog--complete-profile",
            });
        }
    } catch (e) {
        handleRequestError(e);
    } finally {
        hideLoader($("html"));
    }
};

export default openCompleteProfilePopup;
