import $ from "jquery";
import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";
import EventHub from "@src/event-hub";
import initReadMoreText from "@src/components/read-more-text/index";

// styles
import "@scss/user_pages/b2b/detail/index.scss";

initReadMoreText({ item: $(".js-read-more-text"), content: $(".js-read-more-content") });

$(() => {
    lazyLoadingScriptOnScroll(
        $("#js-b2b-request-products-slider"),
        async () => {
            const { default: initProductsSlider } = await import("@src/pages/b2b/detail/fragments/items-slider");
            initProductsSlider();
        },
        "50%"
    );

    EventHub.off("did-help:click");
    EventHub.on("did-help:click", async (e, btn) => {
        e.preventDefault();

        const { default: didHelp } = await import("@src/components/did-help/index");
        didHelp(btn);
    });

    EventHub.off("b2b-requests:load-more");
    EventHub.on("b2b-requests:load-more", async (_e, btn) => {
        const { default: loadMoreB2bRequests } = await import("@src/pages/b2b/detail/fragments/load-more-b2b-requests");
        loadMoreB2bRequests(btn);
    });
    EventHub.off("b2b-requests:partners.load-more");
    EventHub.on("b2b-requests:partners.load-more", async (_e, btn) => {
        const { default: loadMorePartners } = await import("@src/pages/b2b/detail/fragments/load-more-partners");
        loadMorePartners(btn);
    });
    EventHub.off("b2b-requests:followers.load-more");
    EventHub.on("b2b-requests:followers.load-more", async (_e, btn) => {
        const { default: loadMoreFollowers } = await import("@src/pages/b2b/detail/fragments/load-more-followers");
        loadMoreFollowers(btn);
    });
    EventHub.off("b2b-requests:advices.load-more");
    EventHub.on("b2b-requests:advices.load-more", async (_e, btn) => {
        const { default: loadMoreAdvices } = await import("@src/pages/b2b/detail/fragments/load-more-advices");
        loadMoreAdvices(btn);
    });
    EventHub.off("b2b-requests:unfollow");
    EventHub.on("b2b-requests:unfollow", async (_e, btn) => {
        const { default: unfollowB2b } = await import("@src/pages/b2b/detail/fragments/unfollow-b2b");
        unfollowB2b(btn);
    });
    EventHub.off("b2b-requests:moderate-follower");
    EventHub.on("b2b-requests:moderate-follower", async (_e, btn) => {
        const { default: moderateFollower } = await import("@src/pages/b2b/detail/fragments/moderate-follower");
        moderateFollower(btn);
    });
    EventHub.off("b2b-requests:moderate-advice");
    EventHub.on("b2b-requests:moderate-advice", async (_e, btn) => {
        const { default: moderateAdvice } = await import("@src/pages/b2b/detail/fragments/moderate-advice");
        moderateAdvice(btn);
    });
    EventHub.on("b2b-request:read-more-text.toggle", async (_e, btn) => {
        const { default: toggleReadMoreTextBlock } = await import("@src/components/read-more-text/toggle-read-more-text-block");
        toggleReadMoreTextBlock(btn);
    });
});
