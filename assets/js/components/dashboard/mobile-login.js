import $ from "jquery";

import { login, viewPassword } from "@src/pages/login/fragments/login/index";

import loadingValidationEngine from "@src/plugins/validation-engine/lazy";
import EventHub from "@src/event-hub";

export default async () => {
    await import(/* webpackChunkName: "login-index" */ "@src/pages/login/index");

    const mepHeaderDashboard = $("#js-mep-header-dashboard");
    mepHeaderDashboard.on("click focusout", ".validengine input", loadingValidationEngine);
    mepHeaderDashboard.on("submit", ".validengine", loadingValidationEngine);

    EventHub.off("login:view-password");
    EventHub.off("login:authentification");
    EventHub.on("login:view-password", (_e, button) => viewPassword(button));
    EventHub.on("login:authentification", (_e, button) => login(button));
};
