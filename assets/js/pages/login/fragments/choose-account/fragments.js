import $ from "jquery";

import { translate } from "@src/i18n";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import EventHub from "@src/event-hub";
import { SUBDOMAIN_URL } from "@src/common/constants";

const onLogRequestSuccess = function (resp) {
    if (resp.mess_type === "success") {
        if ($('input[name="referer"]').length > 0) {
            globalThis.location.href = $('input[name="referer"]').val().toString();
        } else {
            const currentLink = globalThis.location.href.split("?");
            if (currentLink.length > 1) {
                const [firstCurrentLink] = currentLink;
                globalThis.location.href = firstCurrentLink;
            } else {
                globalThis.location.reload();
            }
        }
    } else if (typeof resp.status === "undefined") {
        systemMessages(resp.message, resp.mess_type);
        hideLoader($("body"));
    } else if (resp.status === "logged") {
        hideLoader($("body"));
        const $popupSelectAccounts = $("#js-popup-select-accounts");

        if ($popupSelectAccounts.length) {
            $popupSelectAccounts.next(".js-main-login-delog").show();
        }

        const $mepHeaderDashboard = $("#js-mep-header-dashboard");
        if ($mepHeaderDashboard.length) {
            if ($mepHeaderDashboard.is(":visible")) {
                $mepHeaderDashboard.find(".js-main-login-delog").show();
            }
        }

        // @ts-ignore
        if (globalThis.$?.fancybox && globalThis.$.fancybox.isOpen) {
            // @ts-ignore
            globalThis.$.fancybox.update();
        }
    }
};

const onLogRequestError = function () {
    systemMessages(translate({ plug: "general_i18n", text: "login_something_went_wrong_message" }), "error");

    hideLoader($("body"));
    $.fancybox.close();
};

const selectAccountPopup = function ($this) {
    // eslint-disable-next-line camelcase
    const id_user = $this.data("user");
    // eslint-disable-next-line no-underscore-dangle
    const url = `${SUBDOMAIN_URL}login/ajax_login_selected_account`;
    const $remember = $('input[name="remember_input"]');
    let remember = null;

    if ($remember.length) {
        remember = $remember.val();
    }

    const $btnCleanSession = $(".js-clean-session-btn");
    if ($btnCleanSession.length) {
        $btnCleanSession.attr("data-user", id_user);
    }

    showLoader($("body"), translate({ plug: "general_i18n", text: "login_changing_word" }), "fixed", 1003);

    $.ajax({
        type: "POST",
        dataType: "json",
        url,
        // eslint-disable-next-line camelcase
        data: { id_user, remember },
        beforeSend(xhr) {
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        },
        success(resp) {
            if (globalThis.matrixLogoutEmitter) {
                globalThis.dispatchEvent(
                    new CustomEvent("matrixLogout", {
                        detail: {
                            callback: () => onLogRequestSuccess(resp),
                        },
                    })
                );
            } else {
                onLogRequestSuccess(resp);
            }
        },
        error() {
            onLogRequestError();
        },
    });
};

export default () => {
    EventHub.off("login:choose-your-account");
    EventHub.on("login:choose-your-account", (e, button) => {
        selectAccountPopup(button);
    });
};
