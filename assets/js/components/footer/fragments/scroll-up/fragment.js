import $ from "jquery";

import headerFixed from "@src/components/navigation/fragments/navigation-fixed";

export default () => {
    globalThis.scrollupState = true;
    const scrollAncorNode = $("#js-btn-scrollup");
    const scrollToTop = function (e) {
        e.preventDefault();
        if (!globalThis.scrollupState) return;

        $(this).hide();
        globalThis.scrollupState = false;
        $("html, body").animate({ scrollTop: 0 }, 600, () => {
            globalThis.scrollupState = true;
            headerFixed();
        });
    };
    const toggleScrollAnchor = function () {
        if (!globalThis.scrollupState) return;

        if ($(this).scrollTop() > 500) {
            scrollAncorNode.show();
        } else {
            scrollAncorNode.hide();
        }
    };

    scrollAncorNode.on("click", scrollToTop);
    $(globalThis).on("scroll load", toggleScrollAnchor);
    headerFixed();
    $(window).on("scroll", headerFixed);
};
