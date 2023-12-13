import $ from "jquery";

const showHeaderOverlay = () => {
    const overlay = $("body > .header-main-overlay2");

    $("*:not(object)")
        .filter(function searchFixedBlocks() {
            const element = $(this);
            return element.css("position") === "fixed" && !element.hasClass("fancybox-overlay") && !element.hasClass("fancybox-wrap");
        })
        .addClass("fancybox-margin2");
    $("html").addClass("fancybox-margin2 fancybox-lock2");

    if (overlay.length) {
        overlay.show().addClass("in");
    } else {
        $("body").append('<div class="header-main-overlay2 in call-action" data-js-action="navbar:close-mep-overlay" style="display: block;"/>');
    }
};

export default showHeaderOverlay;
