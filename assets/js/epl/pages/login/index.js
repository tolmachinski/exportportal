import $ from "jquery";

import { cleanSession, loginOtherAccount } from "@src/epl/pages/login/callbacks/index";
import viewPassword from "@src/epl/common/view-password/index";
import EventHub from "@src/event-hub";

// @ts-ignore
import(/* webpackChunkName: "epl_styles_login" */ "@scss/epl/pages/login/index.scss");

$(() => {
    EventHub.on("epl-login:clean-session", (e, button) => cleanSession(button));
    EventHub.on("epl-login:login-another-account", (e, button) => loginOtherAccount(button));

    $(document).on("click", ".js-view-password-btn", e => {
        e.preventDefault();
        viewPassword($(e.currentTarget));
    });
});
