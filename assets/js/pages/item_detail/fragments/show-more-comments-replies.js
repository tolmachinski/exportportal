import $ from "jquery";

export const toggleBtnShowMoreReply = () => {
    $(".js-product-main-comment-replies").each(function eachComments() {
        let commentsTotal = 0;
        const item = $(this);

        item.find("> .js-product-comments-hide").each(function eachReplies() {
            if (item.find(".js-product-comments-item").length) {
                commentsTotal += 1;
            }
        });

        if (!commentsTotal) {
            item.find(".js-show-all-replies-btn").hide();
        }
    });
};

/**
 * It hides the button and then shows the hidden replies
 * @param {JQuery} btn
 */
const showMoreReply = btn => {
    btn.hide().closest(".js-product-comments-replies").find(".js-product-comments-hide .js-product-comments-item").slideDown();
};

export default showMoreReply;
