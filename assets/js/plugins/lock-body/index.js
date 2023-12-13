import $ from "jquery";

let scrollTopContent;

const lockBody = function () {
    if (window.pageYOffset) {
        scrollTopContent = window.pageYOffset;
        const content = $(".community-content, .ep-content");
        if (content.length) {
            content.css({ top: -scrollTopContent });
        }
    }

    $("html, body").css({
        overflow: "hidden",
    });
};

const unlockBody = () => {
    $("html, body").css({
        overflow: "",
    });

    const $communityContent = $(".community-content");
    if ($communityContent.length) {
        $communityContent.css({ top: "" });
    }

    const $epContent = $(".ep-content");
    if ($epContent.length) {
        $epContent.css({ top: "" });
    }

    globalThis.scrollTo(0, scrollTopContent);
    globalThis.setTimeout(() => {
        scrollTopContent = null;
    }, 0);
};

export { lockBody };
export { unlockBody };
