import $ from "jquery";

import { BACKSTOP_TEST_MODE, LOGGED_IN } from "@src/common/constants";
import EventHub from "@src/event-hub";

import "bootstrap/js/dist/popover";
import "@src/plugins/bootstrap/dist/dropdown";
import "bootstrap/js/dist/tab";

$(async () => {
    EventHub.on("navbar:hide-dashboard-menu", async () => {
        const { default: hideEpuserSubline } = await import("@src/epl/components/dashboard/fragments/hide-dashboard-menu");
        hideEpuserSubline();
    });

    EventHub.on("navbar:toggle-dashboard-menu", async () => {
        const { default: toggleDashboardMenu } = await import("@src/epl/components/dashboard/fragments/toggle-dashboard-block");
        toggleDashboardMenu();
    });

    EventHub.on("navbar:toggle-dashboard-mobile-menu", async () => {
        const { default: toggleMobileMenu } = await import("@src/epl/components/navigation/fragments/toggle-dashboard-mobile-menu");
        toggleMobileMenu();
    });

    EventHub.on("navbar:scroll-to", async (_e, button) => {
        const { default: navScrollTo } = await import("@src/epl/components/navigation/fragments/nav-scroll-to");
        navScrollTo(button);
    });

    if (LOGGED_IN) {
        const { default: triggerNotificationsPopover } = await import("@src/epl/components/navigation/fragments/notifications-popover");
        const { default: createPopover } = await import("@src/epl/common/popover/index");
        if (!BACKSTOP_TEST_MODE) {
            triggerNotificationsPopover();
            if ($("#js-popover-messages").length) {
                createPopover("#js-popover-messages", "#js-tooltip-messages");
            }
        }
    }
});
