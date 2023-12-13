import navHeaderMenu from "@src/components/dashboard/fragments/nav-header-menu";
import EventHub from "@src/event-hub";

export default (metadata = {}) => {
    navHeaderMenu(metadata);

    EventHub.off("dashboard:add-accounts");
    EventHub.off("dashboard:select-account");
    EventHub.off("dashboard:user-menu-mobile");
    EventHub.off("dashboard:switch-account-mobile");

    EventHub.on("dashboard:add-accounts", async (_e, button) => {
        const { default: openNewAddAnotherAccount } = await import("@src/components/dashboard/fragments/open-new-add-another-account");
        openNewAddAnotherAccount(button);
    });

    EventHub.on("dashboard:select-account", async (_e, button) => {
        const { default: selectAccountPopupTop } = await import("@src/components/dashboard/fragments/select-account-popup-top");
        selectAccountPopupTop(button);
    });

    EventHub.on("dashboard:user-menu-mobile", async (_e, button) => {
        const { default: selectorMenuMobile } = await import("@src/components/dashboard/fragments/selector-menu-mobile");
        selectorMenuMobile(button);
    });

    EventHub.on("dashboard:switch-account-mobile", async (_e, button) => {
        const { default: toggleMobileSwitchAccount } = await import("@src/components/dashboard/fragments/toggle-mobile-switch-account");
        toggleMobileSwitchAccount(button);
    });
};
