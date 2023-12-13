import { SUBDOMAIN_URL, SHIPPER_PAGE } from "@src/common/constants";
import { addPopupBanner } from "@src/components/popups_system/popup_util";
import postRequest from "@src/util/http/post-request";

const openCookiesAcceptPopup = async () => {
    await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/cookies_accept`)
        .then(async response => {
            if (response.mess_type === "success") {
                if (SHIPPER_PAGE) {
                    await import("@scss/components/popups/cookies_accept/index_epl.scss");
                } else {
                    await import("@scss/components/popups/cookies_accept/index.scss");
                }
                addPopupBanner(response.content);
            }
        })
        .catch(() => {});
};

export default openCookiesAcceptPopup;
