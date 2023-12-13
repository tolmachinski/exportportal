/* eslint-disable no-undef */
import $ from "jquery";

import "@src/boot/jquery-hooks";
import "@src/boot/http-api";

import { preventDefault } from "@src/util/events";
import { openDialogModal, openConfirmDialog } from "@src/epl/common/popups/types/modal-dialog";
import { systemMessagesCardClose, systemMessagesClose } from "@src/util/system-messages/index";
import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import tabsShowContent from "@src/epl/common/tabs/index";
import Platform from "@src/epl/platform";
import EventHub from "@src/event-hub";
import { CHECKBOX, EMAIL } from "@src/plugins/jquery-validation/rules";
import mix from "@src/util/common/mix";

import "@src/epl/common/popups/types/modals";
import "@src/epl/footer";
import "@src/epl/styles";
import "@src/epl/app.fragments";

// @ts-ignore
import "@scss/epl/general.scss";

mix(globalThis, { ENCORE_MODE: true });

$(() => {
    const body = $(document.body);

    Platform.eplPage = true;

    lazyLoadingInstance(".js-lazy", { threshhold: "10px" });

    EventHub.on("system-mesages:card-close", (e, button) => {
        systemMessagesCardClose(button);
    });
    EventHub.on("system-mesages:close", () => {
        systemMessagesClose();
    });
    EventHub.on("tabs:show-content", (e, button) => {
        tabsShowContent(e, button);
    });
    EventHub.on("lazy-loading:epl-login", () => import(/* webpackChunkName: "epl-login-index" */ "@src/epl/pages/login/index"));

    let fancyboxInit = false;
    /**
     * @param {JQuery.Event} e
     */
    const fancyboxInitialization = function (e) {
        e.preventDefault();

        import(/* webpackChunkName: "fancybox3-popups-chunk" */ "@src/epl/common/popups/index").then(async ({ modalDefault, modalVideo }) => {
            await modalDefault(".js-fancybox");
            await modalVideo(".js-fancybox-video");
            setTimeout(() => {
                $(this).addClass("js-fancybox-init").trigger("click");
            }, 0);
        });

        if (!fancyboxInit) {
            fancyboxInit = true;
            import(/* webpackChunkName: "fancybox3-util-chunk" */ "@src/plugins/fancybox/v3/util").then(({ closeFancyBoxConfirm, onChangePopupContent }) => {
                body.on("change", ".fancybox-inner form :input", onChangePopupContent);
                EventHub.on("fancybox:close", () => closeFancyBoxConfirm());
            });
        }
    };

    body
        // eslint-disable-next-line func-names
        .on("click", ".js-info-dialog", function (e) {
            e.preventDefault();
            const { title, message: subTitle, keepModal, src } = $(this).data();
            openDialogModal({
                title,
                subTitle,
                src,
                keepOtherModals: keepModal,
                closable: true,
                buttons: [
                    {
                        label: "Ok",
                        cssClass: "btn btn-outline-primary",
                        action(dialog) {
                            dialog.close();
                        },
                    },
                ],
            });
        })
        .on(
            // @ts-ignore
            "click",
            ".js-confirm-dialog",
            // eslint-disable-next-line func-names
            preventDefault(function (e) {
                openConfirmDialog(e, $(this));
            })
        )
        .on(
            // @ts-ignore
            "click",
            ".js-fancybox:not(.js-fancybox-init), .js-fancybox-video:not(.js-fancybox-init)",
            fancyboxInitialization
        )
        // eslint-disable-next-line func-names
        .on("input", ".input-number", function () {
            this.value = this.value.replace(/[^0-9]/g, "");
        })
        // eslint-disable-next-line func-names
        .on("click", ".js-accordion-item", async function (e) {
            if (e.target.tagName === "A" || e.target === "BUTTON") return;

            const { default: toggleAccordion } = await import("@src/epl/common/accordion/index");
            toggleAccordion($(this));
        });

    // Footer subscribe form
    const footerFormSubscribe = $(".js-footer-form-subscribe");
    const footerFormSubscribeValidation = async () => {
        const validationOptions = {
            rules: {
                email: EMAIL,
                // eslint-disable-next-line camelcase
                terms_cond: CHECKBOX,
            },
        };
        const { default: initJqueryValidation } = await import("@src/plugins/jquery-validation/lazy");
        const { subscribeFormCallBack } = await import("@src/common/subscribe/index_epl");
        initJqueryValidation(".js-footer-form-subscribe", subscribeFormCallBack.bind(null, null, footerFormSubscribe), validationOptions);
    };

    lazyLoadingScriptOnScroll(footerFormSubscribe, footerFormSubscribeValidation, "50%");
    footerFormSubscribe.on("submit", footerFormSubscribeValidation);
});
