import $ from "jquery";
import { scrollupState } from "@src/common/scroll-up";
import EventHub from "@src/event-hub";

scrollupState.active = true;

const scrollToTop = (e, btn) => {
    e.preventDefault();
    if (!scrollupState.active) return;

    btn.hide();
    scrollupState.active = false;
    $("html, body").animate({ scrollTop: 0 }, 600, () => {
        scrollupState.active = true;
    });
};

const toggleScrollAnchor = function () {
    const scrollAncorNode = $("#js-btn-scrollup");

    if (!scrollupState.active) return;

    if ($(this).scrollTop() > 500) {
        scrollAncorNode.show();
    } else {
        scrollAncorNode.hide();
    }
};

export default () => {
    EventHub.on("scroll-up:toggle", (e, btn) => {
        scrollToTop(e, btn);
    });

    $(window).on("scroll load", toggleScrollAnchor);
};
