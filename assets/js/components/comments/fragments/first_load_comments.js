import $ from "jquery";

import { translate } from "@src/i18n";

import loadCommentsRequest from "@src/components/comments/fragments/load_comments_request";

const firstLoadComments = resourceId => {
    return loadCommentsRequest(resourceId).then(paginator => {
        const wrapper = $("#js-comments-wrapper");
        wrapper.removeClass("display-n");
        if (paginator.hasMorePages) {
            wrapper.append(
                `<div
                    id="js-comments-more-block"
                    class="common-comments__all"
                >
                    <button
                        class="btn btn-light call-action"
                        data-js-action="comments:load-more"
                        type="button"
                    >${translate({ plug: "general_i18n", text: "comments_tree_button_more" })}</button>
                </div>`
            );
        }

        return true;
    });
};

export default firstLoadComments;
