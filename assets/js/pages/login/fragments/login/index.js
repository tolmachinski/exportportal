import $ from "jquery";

import { hideLoader, showLoader } from "@src/util/common/loader";
import { TEMPLATES } from "@src/common/popups/templates";
import { i18nDomain, translate } from "@src/i18n";
import { calculateModalBoxSizes, closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { systemMessages } from "@src/util/system-messages/index";
import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import loadFancybox from "@src/plugins/fancybox/v2/index";
import delay from "@src/util/async/delay";
import { LANG, SHIPPER_URL, SUBDOMAIN_URL } from "@src/common/constants";

const showChooseAccount = async function ($form, resp) {
    hideLoader($form);
    if ($("#js-mep-header-dashboard").is(":visible")) {
        $(".mep-header-login__content").hide();
        $(".mep-header-login__switch-account").html(resp.choose_account_content).show();
    } else {
        const adjustments = calculateModalBoxSizes();

        await loadFancybox();
        await delay(200);
        $.fancybox.open(
            {
                // @ts-ignore
                title: "Select Account",
                type: "ajax",
                href: resp.choose_account_url,
                closeBtn: true,
            },
            {
                tpl: TEMPLATES,
                lang: LANG,
                i18n: i18nDomain({ plug: "fancybox" }),
                width: adjustments.width,
                height: adjustments.height,
                maxWidth: 401,
                autoSize: true,
                closeBtn: true,
                closeClick: false,
                nextClick: false,
                arrows: false,
                mouseWheel: false,
                keys: null,
                loop: false,
                helpers: {
                    title: { type: "inside", position: "top" },
                    overlay: { locked: true, closeClick: false },
                },
                padding: adjustments.gutter,
                closeBtnWrapper: ".fancybox-skin .fancybox-title",
            }
        );
    }
};

const resetPasswordIsLegacy = function ($form) {
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "/authenticate/reset_legacy_ajax",
        data: {},
        async success(resp) {
            openResultModal({
                content: resp.message,
                type: resp.mess_type,
                closable: true,
                buttons: [
                    {
                        label: translate({ plug: "BootstrapDialog", text: "close" }),
                        action(dialogRef) {
                            dialogRef.close();
                        },
                    },
                ],
            });
            hideLoader($form);
            closeFancyBox();
        },
    });
};

const showLogInToEplPopup = subTitle => {
    openResultModal({
        subTitle,
        closable: true,
        buttons: [
            {
                label: translate({ plug: "BootstrapDialog", text: "go_to_epl" }),
                cssClass: "btn btn-primary",
                action() {
                    window.location.href = `${SHIPPER_URL}login`;
                },
            },
            {
                label: translate({ plug: "BootstrapDialog", text: "cancel" }),
                cssClass: "btn btn-outline-primary",
                action(dialog) {
                    dialog.close();
                },
            },
        ],
    });
};

const login = function ($form) {
    const fdata = $form.serialize();
    const $delog = $form.find(".js-main-login-delog");
    const url = `${SUBDOMAIN_URL}login/login_ajax`;
    showLoader($form);

    return postRequest(url, fdata)
        .then(async resp => {
            if (resp.mess_type === "info" && resp.userType) {
                await loadBootstrapDialog();
                closeFancyBox();
                showLogInToEplPopup(resp.message);
                hideLoader($form);
            } else if (resp.mess_type === "success") {
                if (typeof resp.additional_action !== "undefined") {
                    // eslint-disable-next-line default-case
                    switch (resp.additional_action) {
                        case "choose_account":
                            showChooseAccount($form, resp);
                            break;
                        case "reset":
                            await loadBootstrapDialog();
                            openResultModal({
                                title: translate({ plug: "general_i18n", text: "js_reset_password_title" }),
                                content:
                                    `<p>${translate({ plug: "general_i18n", text: "js_reset_password_text_1" })}</p>` +
                                    `<p>${translate({
                                        plug: "general_i18n",
                                        text: "js_reset_text_contact",
                                        replaces: { "{LINK_START}": '<a href="mailto:support@exportportal.com">', "{LINK_END}": "</a>" },
                                    })}</p>` +
                                    `<p>${translate({ plug: "general_i18n", text: "js_reset_password_text_2" })}</p>`,
                                type: "warning",
                                closable: true,
                                closeCallBack() {
                                    hideLoader($form);
                                },
                                buttons: [
                                    {
                                        label: translate({ plug: "general_i18n", text: "js_reset_password_button" }),
                                        cssClass: "btn-primary",
                                        action(dialogRef) {
                                            resetPasswordIsLegacy($form);
                                            dialogRef.close();
                                        },
                                    },
                                ],
                            });
                            break;
                    }
                } else if ($('form.main-login-form input[name="referer"]').length > 0) {
                    hideLoader($form);
                    globalThis.location.href = $('form.main-login-form input[name="referer"]').val().toString();
                } else {
                    const currentLink = globalThis.location.href.split("?");
                    if (currentLink.length > 1) {
                        const [firstCurrentLink] = currentLink;
                        globalThis.location.href = firstCurrentLink;
                    } else {
                        globalThis.location.reload();
                    }
                }
            } else {
                hideLoader($form);
                if (typeof resp.status === "undefined") {
                    systemMessages(resp.message, resp.mess_type);
                } else if (resp.status === "logged") {
                    $delog.show();
                }
            }
            return false;
        })
        .catch(e => {
            handleRequestError(e);
            hideLoader($form);
        });
};

const onCleanSessionSuccess = function (resp, $delog) {
    systemMessages(resp.message, resp.mess_type);

    if ($delog.hasClass("main-login-delog--show-simple")) {
        $.fancybox.close();
    } else {
        $delog.hide();
        hideLoader($delog);
    }
};

const cleanSessionById = function () {
    const btn = $(".js-clean-session-btn");
    const $delog = btn.closest(".js-main-login-delog");
    const id = btn.data("user");
    const url = "/login/clean_session_request";

    showLoader($delog);
    // eslint-disable-next-line camelcase
    return postRequest(url, { id, by_id: true })
        .then(response => {
            if (response.mess_type === "success") {
                onCleanSessionSuccess(response, $delog);
            }
        })
        .catch(handleRequestError)
        .finally(() => hideLoader($delog));
};

const cleanSession = function () {
    const btn = $(".js-clean-session-btn");
    const $form = btn.closest("form");
    const $delog = btn.closest(".js-main-login-delog");
    const url = "/login/clean_session_request";

    const fdata = $form.serialize();
    $form.trigger("reset");

    return postRequest(url, fdata)
        .then(response => {
            systemMessages(response.message, response.mess_type);

            if (response.mess_type === "success") {
                hideLoader($form);
                $delog.hide();
            }
        })
        .catch(handleRequestError);
};

const chooseAnotherAccount = function () {
    const btn = $(".js-choose-another-account");
    const $delog = btn.closest(".js-main-login-delog");

    if ($delog.hasClass("main-login-delog--show-simple")) {
        $.fancybox.close();
    } else {
        $delog.hide();

        // @ts-ignore
        if (globalThis.$?.fancybox && globalThis.$.fancybox.isOpen) {
            // @ts-ignore
            globalThis.$.fancybox.update();
        }
    }
};

const loginOtherAccount = function ($this) {
    const $form = $this.closest("form");
    const $delog = $this.closest(".js-main-login-delog");

    if ($form.length) {
        $form[0].reset();
    }

    $delog.hide();
};

const viewPassword = function ($this) {
    const $input = $this.siblings("input");

    if ($input.prop("type") === "text") {
        $input.prop("type", "password");
    } else {
        $input.prop("type", "text");
    }

    $this.toggleClass("ep-icon_visible ep-icon_invisible");
};

export { login, cleanSession, cleanSessionById, chooseAnotherAccount, loginOtherAccount, viewPassword };
