import $ from "jquery";

import { translate } from "@src/i18n";
import { SUBDOMAIN_URL } from "@src/common/constants";

import postRequest from "@src/util/http/post-request";
import handleRequestError from "@src/util/http/handle-request-error";

const deleteComment = async button => {
    const commentId = parseInt(button.data("comment"), 10);
    button.prop("disabled", true);

    try {
        const { message, mess_type: messType } = await postRequest(`${SUBDOMAIN_URL}comments/ajax_operations/delete`, { comment: commentId });

        const { openResultModal, default: loadBootstrapDialog } = await import("@src/plugins/bootstrap-dialog/index");
        await loadBootstrapDialog();
        openResultModal({
            content: message,
            type: messType,
            closable: true,
            buttons: [
                {
                    label: translate({ plug: "BootstrapDialog", text: "close" }),
                    cssClass: "btn btn-light",
                    action(dialogRef) {
                        dialogRef.close();
                    },
                },
            ],
        });

        $(`.js-common-comments-row[data-comment-row="${commentId}"]`).remove();

        const commentsCount = $("#js-count-comments");
        commentsCount.text(parseInt(commentsCount.text(), 10) - 1);

        const commentsList = $("#js-comments-list");
        if (!commentsList.find(".js-common-comments-row").length) {
            commentsList.html(
                `<div class="js-common-comments-empty default-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>${translate({
                    plug: "general_i18n",
                    text: "comments_tree_item_not_found",
                })}</span></div>`
            );
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        button.prop("disabled", false);
    }

    return true;
};

export default deleteComment;
