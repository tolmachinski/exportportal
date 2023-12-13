import $ from "jquery";
import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";

import "@scss/user_pages/about_us_page/index_page.scss";

$(() => {
    const url = new URL(globalThis.location.href);
    const hash = url.searchParams.get("request");

    // Check if exist hash for webinar requests
    if (hash) {
        setTimeout(() => {
            $("#js-request-demo-banner button").data("hash", hash).trigger("click");
        }, 500);
    }

    lazyLoadingScriptOnScroll(
        $("#js-about-videos-container"),
        () =>
            import(/* webpackChunkName: "videos-slide-chunk" */ "@src/pages/about/fragments/videos-slider").then(({ default: aboutVideosSlider }) =>
                aboutVideosSlider()
            ),
        "50px"
    );
});
