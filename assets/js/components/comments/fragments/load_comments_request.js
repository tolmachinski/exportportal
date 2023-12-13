import $ from "jquery";

import { hideLoader, showLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { SUBDOMAIN_URL } from "@src/common/constants";

import postRequest from "@src/util/http/post-request";
import handleRequestError from "@src/util/http/handle-request-error";

const loadCommentsRequest = async (resourceId, page = 1) => {
    const commentsWrapper = $("#js-comments-wrapper");

    showLoader(commentsWrapper, "Loading...");

    try {
        const { message, mess_type: messType, html, paginator } = await postRequest(`${SUBDOMAIN_URL}comments/ajax_operations/list`, {
            resource: resourceId,
            level: 0,
            page,
        });

        if (message) {
            systemMessages(message, messType || null);
        }

        if (html) {
            $("#js-comments-list").append(html);
        }

        return { ...(paginator || { paginator: { hasMorePages: false } }) };
    } catch (error) {
        handleRequestError(error);

        return { error };
    } finally {
        hideLoader(commentsWrapper);
    }
};

export default loadCommentsRequest;
