import { SUBDOMAIN_URL } from "@src/common/constants";
import { addPopupBanner } from "@src/components/popups_system/popup_util";
import postRequest from "@src/util/http/post-request";

const openFeedbackRegistrationPopup = async () => {
    if (globalThis.triggerFeedbackRegistration === undefined || !globalThis.triggerFeedbackRegistration.getStatus()) {
        if (globalThis.triggerFeedbackRegistration !== undefined) {
            globalThis.triggerFeedbackRegistration.clear();
        }

        await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/feedback_registration`)
            .then(async response => {
                if (response.mess_type === "success") {
                    await import("@scss/components/popups/feedback_registration/index.scss");
                    addPopupBanner(response.content);
                }
            })
            .catch(() => {});
    }
};

export default openFeedbackRegistrationPopup;
