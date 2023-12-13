import $ from "jquery";
import hideHeaderOverlay from "@src/components/navigation/fragments/hide-header-overlay";

const hideSideNav = () => {
    const menu = $("#js-ep-header-top");
    const params = $(window).width() > 767 ? { right: -menu.width() } : { top: -menu.height() };

    menu.stop(true).animate(params, 500, () => {
        $("#js-mep-header-burger-btn").removeClass("active");
        menu.hide();
        menu.removeAttr("style").removeClass("active");
    });

    hideHeaderOverlay();
};

export default hideSideNav;
