import $ from "jquery";

import { hideLoader, showLoader } from "@src/util/common/loader";
import { updateFancyboxPopup3, closeFancyboxPopup } from "@src/plugins/fancybox/v3/util";
import { systemMessages } from "@src/util/system-messages/index";
import { LANG, SHIPPER_URL, SITE_URL, SUBDOMAIN_URL } from "@src/common/constants";
import { translate } from "@src/i18n";
import { openDialogModal } from "@src/epl/common/popups/types/modal-dialog";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

const resetPasswordIsLegacy = () => {
    return postRequest(`${SUBDOMAIN_URL}authenticate/reset_legacy_ajax`)
        .then(resp => {
            openDialogModal({
                subTitle: resp.message,
                category: resp.mess_type,
                closable: true,
                buttons: [
                    {
                        label: translate({ plug: "fancybox3", text: "cancel" }),
                        cssClass: "btn btn-outline-primary",
                        action(dialog) {
                            dialog.close();
                        },
                    },
                ],
            });
        })
        .catch(handleRequestError);
};

const showLogInToEpPopup = async subTitle => {
    await import(/* webpackChunkName: "fancybox-i18n" */ `@plug/fancybox-3-5-7/lang/${LANG}.js`);
    openDialogModal({
        subTitle,
        closable: true,
        buttons: [
            {
                label: translate({ plug: "fancybox3", text: "go_to_ep" }),
                cssClass: "btn btn-primary",
                action() {
                    window.location.href = `${SITE_URL}login`;
                },
            },
            {
                label: translate({ plug: "fancybox3", text: "cancel" }),
                cssClass: "btn btn-outline-primary",
                action(dialog) {
                    dialog.close();
                },
            },
        ],
    });
};

const login = formSelector => {
    const form = $(formSelector);
    const fdata = form.serialize();
    const delog = form.find(".js-epl-login-delog");
    const url = `${SUBDOMAIN_URL}login/login_ajax`;

    showLoader(form);

    return postRequest(url, fdata)
        .then(async resp => {
            if (resp.mess_type === "info" && resp.userType) {
                await closeFancyboxPopup();
                showLogInToEpPopup(resp.message);
                hideLoader(form);
            } else if (resp.mess_type === "success") {
                const referrer = $('form.js-epl-login-form input[name="referer"]');
                if (typeof resp.additional_action !== "undefined") {
                    // eslint-disable-next-line default-case
                    switch (resp.additional_action) {
                        case "reset":
                            openDialogModal({
                                type: "html",
                                title: translate({ plug: "general_i18n", text: "js_reset_password_title" }),
                                category: "warning",
                                closable: true,
                                closeCallBack() {
                                    hideLoader(form);
                                },
                                content: `<p>${translate({ plug: "general_i18n", text: "js_reset_password_text_1" })}</p>
                                        <p>${translate({
                                            plug: "general_i18n",
                                            text: "js_reset_text_contact",
                                            replaces: { "{LINK_START}": '<a href="mailto:support@exportportal.com">', "{LINK_END}": "</a>" },
                                        })}</p>
                                    <p>${translate({ plug: "general_i18n", text: "js_reset_password_text_2" })}</p>
                                `,
                                buttons: [
                                    {
                                        label: translate({ plug: "general_i18n", text: "js_reset_password_button" }),
                                        cssClass: "btn btn-primary",
                                        action(dialog) {
                                            resetPasswordIsLegacy();
                                            dialog.close();
                                        },
                                    },
                                ],
                            });
                            break;
                    }
                } else if (referrer.length) {
                    hideLoader(form);
                    globalThis.location.href = referrer.val().toString();
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
                hideLoader(form);
                systemMessages(resp.message, resp.mess_type);
            } else if (resp.status === "logged") {
                hideLoader(form);
                form.parent().prev().find(".fancybox-title").html("Clear session");
                form.find(".js-epl-login-form-content").hide();
                delog.show();
                updateFancyboxPopup3();
            }
            return false;
        })
        .catch(e => {
            handleRequestError(e);
            hideLoader(form);
        });
};

const cleanSession = btn => {
    const form = btn.closest("form");
    const delog = btn.closest(".js-epl-login-delog");
    const url = `${SUBDOMAIN_URL}login/clean_session_request`;
    const fdata = form.serialize();

    showLoader(form);
    return postRequest(url, fdata)
        .then(response => {
            systemMessages(response.message, response.mess_type);

            if (response.mess_type === "success") {
                delog.hide();
                $(".js-epl-login-form-content").show();
                form.parent().prev().find(".fancybox-title").html("Sign in");
                updateFancyboxPopup3();
            }
        })
        .catch(handleRequestError)
        .finally(() => hideLoader(form));
};

const loginOtherAccount = btn => {
    const form = btn.closest("form");
    const delog = btn.closest(".js-epl-login-delog");

    if (form.length) {
        form[0].reset();
    }

    delog.hide();
    $(".js-epl-login-form-content").show();
    form.parent().prev().find(".fancybox-title").html("Sign in");
    updateFancyboxPopup3();
};

export { login, cleanSession, loginOtherAccount };
