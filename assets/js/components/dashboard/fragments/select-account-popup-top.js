import $ from "jquery";

import loadFancybox, { open } from "@src/plugins/fancybox/v2/index";
import { calculateModalBoxSizes } from "@src/plugins/fancybox/v2/util";
import { TEMPLATES } from "@src/common/popups/templates";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { SUBDOMAIN_URL, LANG } from "@src/common/constants";
import { translate, i18nDomain } from "@src/i18n";
import { systemMessages } from "@src/util/system-messages/index";

import postRequest from "@src/util/http/post-request";
import handleRequestError from "@src/util/http/handle-request-error";

const onLogRequestSuccessTop = async resp => {
    if (resp.mess_type === "success") {
        globalThis.location.reload();
    } else {
        hideLoader($("body"));

        if (typeof resp.status === "undefined") {
            systemMessages(resp.message, resp.mess_type);
        } else if (resp.status === "logged") {
            await loadFancybox().then(async () => {
                const adjustments = calculateModalBoxSizes();
                await import(/* webpackChunkName: "login-index" */ "@src/pages/login/index");

                open(
                    {
                        // @ts-ignore
                        title: translate({ plug: "general_i18n", text: "login_clean_session" }),
                        type: "ajax",
                        href: resp.clean_session_url,
                        closeBtn: TEMPLATES.closeBtn,
                    },
                    {
                        tpl: TEMPLATES,
                        // eslint-disable-next-line no-underscore-dangle
                        lang: LANG,
                        i18n: i18nDomain({ plug: "fancybox" }),
                        width: "70%",
                        height: "auto",
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
            });
        }
    }
};

const selectAccountPopupTop = async button => {
    const url = `${SUBDOMAIN_URL}login/ajax_login_selected_account`;

    try {
        showLoader($("body"), translate({ plug: "general_i18n", text: "login_changing_word" }), "fixed", 1003);

        const responce = await postRequest(url, { id_user: button.data("user") });
        const { mess_type: messType } = responce;

        if (globalThis.matrixLogoutEmitter && messType === "success") {
            globalThis.dispatchEvent(
                new CustomEvent("matrixLogout", {
                    detail: {
                        callback: () => onLogRequestSuccessTop(responce),
                    },
                })
            );
        } else {
            onLogRequestSuccessTop(responce);
        }
    } catch (error) {
        handleRequestError(error);
        hideLoader($("body"));
    }
};

export default selectAccountPopupTop;
