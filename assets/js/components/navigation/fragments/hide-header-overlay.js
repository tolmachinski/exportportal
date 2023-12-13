import $ from "jquery";

const hideHeaderOverlay = () => {
    $(".fancybox-margin2").removeClass("fancybox-margin2");
    $("html").removeClass("fancybox-lock2");
    $("body > .header-main-overlay2").removeClass("in").hide();
};

export default hideHeaderOverlay;
