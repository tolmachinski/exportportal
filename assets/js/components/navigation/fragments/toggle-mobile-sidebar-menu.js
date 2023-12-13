import $ from "jquery";

import showHeaderOverlay from "@src/components/navigation/fragments/show-header-overlay";
import hideSideNav from "@src/components/navigation/fragments/hide-side-nav";
import closeMainOverlay2 from "@src/components/navigation/fragments/close-main-overlay2";

const toggleMobileSidebarMenu = () => {
    const menu = $("#js-ep-header-top");
    const button = $("#js-mep-header-burger-btn");
    let delay = 0;

    if ($("#js-ep-header-content-search").is(":visible") || $("#js-mep-header-dashboard").is(":visible")) {
        delay = 500;
        closeMainOverlay2();
    }

    if (button.hasClass("active") || menu.is(":visible")) {
        hideSideNav();
    } else {
        showHeaderOverlay();
        let minusHeader = 51;
        const maintenanceBanner = $("#js-maintenance-banner");
        const upgradeBanner = $(".js-upgrade-banner-top");

        if (maintenanceBanner.length) {
            minusHeader += maintenanceBanner.height();
        }

        if (upgradeBanner.length) {
            minusHeader += upgradeBanner.height();
        }

        menu.show()
            .stop(true)
            .delay(delay)
            .animate($(window).width() > 767 ? { right: "0" } : { top: minusHeader }, 500, () => {
                button.addClass("active");
                menu.addClass("active");
            });
    }
};

export default toggleMobileSidebarMenu;
