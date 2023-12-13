import $ from "jquery";

import EventHub from "@src/event-hub";
import wrapBlogTables from "@src/pages/blog/fragments/wrap_blog_tables";

import "@scss/user_pages/blog_detail/index.scss";

$(() => {
    wrapBlogTables();

    EventHub.off("blog:load-more-recommended");
    EventHub.on("blog:load-more-recommended", async (_e, button) => {
        const { default: recommendedSeeMore } = await import("@src/pages/blog/fragments/recommended_see_more");
        recommendedSeeMore(button);
    });
});
