import { SITE_URL } from "@src/common/constants";
import { systemMessages } from "@src/util/system-messages/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

/**
 * Send handler.
 *
 * @param {JQuery} btn
 */
const moderateFollower = async btn => {
    btn.addClass("disabled");

    try {
        const { mess_type: messageType, message } = await postRequest(`${SITE_URL}b2b/ajax_make_follower_moderated`, { follower: btn.data("follower") });

        systemMessages(message, messageType);

        if (messageType === "success") {
            btn.remove();
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        btn.removeClass("disabled");
    }
};

export default moderateFollower;
