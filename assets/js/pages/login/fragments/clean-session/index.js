import EventHub from "@src/event-hub";
import { cleanSessionById, cleanSession, chooseAnotherAccount, loginOtherAccount } from "@src/pages/login/fragments/login/index";

export default () => {
    EventHub.off("login:choose-another-account");
    EventHub.off("login:login-another-account");
    EventHub.off("login:clean-session");
    EventHub.off("login:clean-session-by-id");
    EventHub.on("login:choose-another-account", () => chooseAnotherAccount());
    EventHub.on("login:login-another-account", (_e, button) => loginOtherAccount(button));
    EventHub.on("login:clean-session", () => cleanSession());
    EventHub.on("login:clean-session-by-id", () => cleanSessionById());
};
