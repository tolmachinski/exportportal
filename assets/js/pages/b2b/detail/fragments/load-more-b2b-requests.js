import $ from "jquery";
import { SITE_URL } from "@src/common/constants";
import stopScrollLoadMore from "@src/util/dom/stop-scroll-load-more";
import { hideLoader, showLoader } from "@src/util/common/loader";
import postRequest from "@src/util/http/post-request";
import handleRequestError from "@src/util/http/handle-request-error";
import lazyLoadingInstance from "@src/plugins/lazy/index";

/**
 * It loads more B2B requests from the server and appends them to the list
 * @param {JQuery} btn - load more btn
 */
const loadMoreB2bRequests = async btn => {
    const list = $("#js-other-b2b-requests-wrapper");
    let itemsLength = list.find(".js-b2b-card").length;
    const start = itemsLength;

    stopScrollLoadMore(btn);
    btn.addClass("disabled");
    showLoader(btn);

    try {
        const { mess_type: messageType, content, totalCount } = await postRequest(
            `${SITE_URL}b2b/ajax_b2b_operation/more_b2b_requests`,
            { request: btn.data("request"), start },
            "json"
        );

        if (messageType === "success" && content) {
            list.append(content);
            itemsLength = list.find(".js-b2b-card").length;

            if (itemsLength >= totalCount) {
                btn.remove();
            }

            lazyLoadingInstance(".js-lazy", { threshhold: "10px" });
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        btn.removeClass("disabled");
        hideLoader(btn);
    }
};

export default loadMoreB2bRequests;
