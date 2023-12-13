import $ from "jquery";

const toggleHeaderLineBackground = () => {
    const headerLineBackground = $("#js-epl-header-line-background");

    if (headerLineBackground.hasClass("active")) {
        headerLineBackground.removeClass("active").hide();
    } else {
        headerLineBackground.addClass("active").show();
    }
};

export default toggleHeaderLineBackground;
