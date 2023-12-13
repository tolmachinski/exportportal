import $ from "jquery";

import loadCommentsRequest from "@src/components/comments/fragments/load_comments_request";

const loadMoreComments = (button, page, resourceId) => {
    button.prop("disabled", true);

    const pageNext = page + 1;
    return loadCommentsRequest(resourceId, pageNext).then(responce => {
        if (responce.error) {
            button.prop("disabled", false);
            return responce;
        }

        if (!responce.hasMorePages) {
            $("#js-comments-more-block").remove();
        } else {
            button.prop("disabled", false);
        }

        return pageNext;
    });
};

export default loadMoreComments;
