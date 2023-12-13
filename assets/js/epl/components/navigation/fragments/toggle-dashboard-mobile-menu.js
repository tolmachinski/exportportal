import $ from "jquery";

import onResizeCallback from "@src/util/dom/on-resize-callback";
import EventHub from "@src/event-hub";

const closeSideNavOnResize = () => {
    onResizeCallback(async () => {
        if ($(window).width() > 991) {
            const { default: closeDashboardMobileMenu } = await import("@src/epl/components/navigation/fragments/close-dashboard-mobile-menu");
            closeDashboardMobileMenu();
        }
    });
};

const toggleDashboardMobileMenu = () => {
    let timeout = 0;

    if ($(".js-open-chat.chat-active").length) {
        EventHub.trigger("chat:close-chat-popup");
    }

    if ($("#js-epuser-subline").hasClass("active")) {
        EventHub.trigger("navbar:toggle-dashboard-menu");
        timeout = 500;
    }

    setTimeout(async () => {
        $("#js-epl-header-line").toggleClass("active");
        $("#js-epl-header-line-background").data("jsAction", "navbar:toggle-dashboard-mobile-menu");
        $("body").toggleClass("locked");

        const { default: toggleHeaderLineBackground } = await import("@src/epl/components/navigation/fragments/toggle-header-line-background");
        toggleHeaderLineBackground();
    }, timeout);

    closeSideNavOnResize();
};

export default toggleDashboardMobileMenu;
