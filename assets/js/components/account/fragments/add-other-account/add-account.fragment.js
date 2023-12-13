import AccountPopup from "@src/components/account/fragments/add-other-account/account-popup";
import { closeAllDialogs } from "@src/plugins/bootstrap-dialog/index";
import EventHub from "@src/event-hub";

export default params => {
    const accountPopup = new AccountPopup(params);

    [
        "accounts:add-other:next-step",
        "accounts:add-other:prev-step",
        "accounts:add-other:next-steps",
        "accounts:add-other:prev-steps",
        "accounts:add-other:close-all",
        "accounts:add-other:validate",
        "accounts:add-other:create",
    ].forEach(e => EventHub.off(e));

    EventHub.on("accounts:add-other:next-step", () => {
        accountPopup.onNextRegisterStep();
    });
    EventHub.on("accounts:add-other:prev-step", () => {
        accountPopup.onPrevRegisterStep();
    });
    EventHub.on("accounts:add-other:next-steps", () => {
        accountPopup.onNextRegisterSteps();
    });
    EventHub.on("accounts:add-other:prev-steps", () => {
        accountPopup.onPrevRegisterSteps();
    });
    EventHub.on("accounts:add-other:close-all", () => {
        closeAllDialogs();
    });
    EventHub.on("accounts:add-other:validate", (e, button) => {
        accountPopup.onValidateTabSubmit(button);
    });
    EventHub.on("accounts:add-other:create", (e, form) => {
        accountPopup.onPopupRegisterForm(form);
    });
};
