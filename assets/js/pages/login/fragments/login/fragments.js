import $ from "jquery";

import { login, viewPassword } from "@src/pages/login/fragments/login/index";

import EventHub from "@src/event-hub";

export default () => {
    EventHub.off("login:view-password");
    EventHub.off("login:authentification");
    EventHub.on("login:view-password", (_e, button) => viewPassword(button));
    EventHub.on("login:authentification", (_e, button) => login(button));
};
