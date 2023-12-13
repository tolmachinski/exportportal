import $ from "jquery";

import EventHub from "@src/event-hub";
import closeMenuOnResize from "@src/components/navigation/fragments/close-menu-on-resize";

import "bootstrap/js/dist/popover";
import "@src/plugins/bootstrap/dist/dropdown";
import "bootstrap/js/dist/tab";

import "@scss/user_pages/general/navigation.scss";

closeMenuOnResize();

$(() => {
    EventHub.on("navbar:hide-dashboard-menu", async () => {
        const { default: hideDashboardMenu } = await import("@src/components/dashboard/fragments/hide-dashboard-menu");
        hideDashboardMenu();
    });

    EventHub.on("navbar:toggle-dashboard-menu", async (_e, button) => {
        const { default: toggleDashboardMenu } = await import("@src/components/dashboard/fragments/toggle-dashboard-menu");
        toggleDashboardMenu(button);
    });

    EventHub.on("navbar:toggle-dashboard-mobile-menu", async (_e, button) => {
        const { default: toggleDashboardMobileMenu } = await import("@src/components/dashboard/fragments/toggle-dashboard-mobile-menu");
        toggleDashboardMobileMenu(button);
    });

    EventHub.on("navbar:close-mep-overlay", async () => {
        const { default: closeMainOverlay2 } = await import("@src/components/navigation/fragments/close-main-overlay2");
        closeMainOverlay2();
    });

    EventHub.on("navbar:toggle-mobile-sidebar-menu", async () => {
        const { default: toggleMobileSidebarMenu } = await import("@src/components/navigation/fragments/toggle-mobile-sidebar-menu");
        toggleMobileSidebarMenu();
    });

    EventHub.on("navbar:open-popup-preferences", async (_e, button) => {
        const { default: callPreferencesModal } = await import("@src/components/navigation/fragments/open-preferences");
        callPreferencesModal(button);
    });

    EventHub.on("navbar:show-header-mobile-search-form", async (_e, button) => {
        const { default: headerTopToggle } = await import("@src/components/navigation/fragments/header-top-toggle");
        headerTopToggle(button);
    });

    EventHub.on("top-upgrade-banner:close", async () => {
        const { hideTopBannerBecomeCertified } = await import("@src/components/navigation/top-upgrade-banner/index");
        hideTopBannerBecomeCertified();
    });

    EventHub.on("top-upgrade-banner:link", async (e, btn) => {
        const { linkTopBannerBecomeCertified } = await import("@src/components/navigation/top-upgrade-banner/index");
        linkTopBannerBecomeCertified(btn.attr("href"));
    });
});
