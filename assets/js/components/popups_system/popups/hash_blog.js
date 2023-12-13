import { SUBDOMAIN_URL } from "@src/common/constants";
import { addPopupBanner } from "@src/components/popups_system/popup_util";
import postRequest from "@src/util/http/post-request";

const openBlogFeedbackPopup = async () => {
    await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/hash_blog`)
        .then(async response => {
            if (response.mess_type === "success") {
                await import("@scss/components/popups/hash_blog/index.scss");
                addPopupBanner(response.content);
            }
        })
        .catch(() => {});
};

export default openBlogFeedbackPopup;
