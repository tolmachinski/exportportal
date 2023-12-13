import $ from "jquery";

import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";

export default ({ resourceId }) => {
    lazyLoadingScriptOnScroll(
        $("#js-comments-desktop-wrapper"),
        async () => {
            // @ts-ignore
            await import("@scss/components/comments/index.scss");
            const { default: placeCommentsAndOtherNews } = await import("@src/components/comments/fragments/place_comments_and_other_news");
            const { default: firstLoadComments } = await import("@src/components/comments/fragments/first_load_comments");
            const { default: EventHub } = await import("@src/event-hub");

            placeCommentsAndOtherNews();
            firstLoadComments(resourceId);

            let page = 1;
            EventHub.off("comments:load-more");
            EventHub.on("comments:load-more", async (_e, button) => {
                const { default: loadMoreComments } = await import("@src/components/comments/fragments/load_more_comments");
                loadMoreComments(button, page, resourceId).then(responce => {
                    if (!responce.error) {
                        page = responce;
                    }
                });
            });

            EventHub.off("comment:delete");
            EventHub.on("comment:delete", async (_e, button) => {
                const { default: deleteComment } = await import("@src/components/comments/fragments/delete_comment");
                deleteComment(button);
            });
        },
        "100px"
    );
};
