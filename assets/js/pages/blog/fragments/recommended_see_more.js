import $ from "jquery";

import { systemMessages } from "@src/util/system-messages/index";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { SITE_URL } from "@src/common/constants";

import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

const recommendedSeeMore = async button => {
    const buttonElement = $(button);
    const blogRecommended = $(".js-mblog-recommended-list");

    try {
        button.prop("disabled", true);
        showLoader(blogRecommended);

        const { mess_type: messageType, message, list: listItems, count: listCount } = await postRequest(
            `${SITE_URL}blogs/ajax_blogs_operation/recommended_more`,
            {
                user: buttonElement.data("user"),
                count: blogRecommended.find(".js-mblog-recommended-list-item").length,
            }
        );

        if (messageType !== "success") {
            systemMessages(message, messageType);

            return;
        }

        blogRecommended.append(listItems);

        if (blogRecommended.find(".js-mblog-recommended-list-item").length >= listCount) {
            buttonElement.remove();
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(blogRecommended);
        button.prop("disabled", false);
    }
};

export default recommendedSeeMore;
