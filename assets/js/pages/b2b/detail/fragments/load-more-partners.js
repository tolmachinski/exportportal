import $ from "jquery";
import { SITE_URL } from "@src/common/constants";
import stopScrollLoadMore from "@src/util/dom/stop-scroll-load-more";
import { hideLoader, showLoader } from "@src/util/common/loader";
import postRequest from "@src/util/http/post-request";
import handleRequestError from "@src/util/http/handle-request-error";
import lazyLoadingInstance from "@src/plugins/lazy/index";

/**
 * It loads more partners of a request
 * @param {JQuery} btn - load more btn
 */
const loadMorePartners = async btn => {
    const list = $("#js-b2b-request-partners-wrapper");
    let itemsLength = list.find(".js-b2b-request-partners-item").length;
    const start = itemsLength;

    stopScrollLoadMore(btn);
    btn.addClass("disabled");
    showLoader(btn, "Loading...");

    try {
        const { mess_type: messageType, content, totalCount } = await postRequest(
            `${SITE_URL}b2b/ajax_b2b_operation/more_partners`,
            { request: btn.data("request"), start },
            "json"
        );

        if (messageType === "success" && content) {
            list.append(content);
            itemsLength = list.find(".js-b2b-request-partners-item").length;

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

export default loadMorePartners;
