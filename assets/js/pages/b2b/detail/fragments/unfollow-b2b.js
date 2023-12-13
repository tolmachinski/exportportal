import { SITE_URL } from "@src/common/constants";
import { systemMessages } from "@src/util/system-messages/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

/**
 * Send handler.
 *
 * @param {JQuery} btn
 */
const unfollowB2b = async btn => {
    const request = btn.data("request");

    btn.addClass("disabled");

    try {
        const { mess_type: messageType, message } = await postRequest(`${SITE_URL}follow/ajax_operation/delete_follow_b2b`, { id: request });

        systemMessages(message, messageType);

        if (messageType === "success") {
            btn.addClass("fancybox.ajax fancyboxValidateModal")
                .removeClass("call-action")
                .data({ title: "Follow this", fancyboxHref: `follow/popup_forms/follow_b2b_request/${request}` })
                .html('<i class="ep-icon ep-icon_follow"></i> Follow B2B request')
                .removeAttr("data-request data-js-action");
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        btn.removeClass("disabled");
    }
};

export default unfollowB2b;
