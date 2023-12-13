import $ from "jquery";

import "@src/polyfill";
import "@src/boot/jquery-hooks";
import "@src/boot/http-api";

import { translate } from "@src/i18n";
import { requireLoggedSystmess, preventDefault } from "@src/util/events";
import { systemMessages, systemMessagesCardClose, systemMessagesClose } from "@src/util/system-messages/index";
import loadBootstrapDialog, { onPopupOpen, openResultModal, openConfirmationDialog, openVideoModal, openDialog } from "@src/plugins/bootstrap-dialog/index";
import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";
import loadingValidationEngine from "@src/plugins/validation-engine/lazy";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import sharePopups from "@src/components/popups/share-popup/index";
import EventHub from "@src/event-hub";
import mix from "@src/util/common/mix";
import { LOGGED_IN } from "./common/constants";

import "@src/critical-styles";

mix(globalThis, { ENCORE_MODE: true });
sharePopups();

$(() => {
    // Lazy loading images
    lazyLoadingInstance(".js-lazy", { threshhold: "10px" });

    let fancyboxInit = false;
    const body = $(document.getElementsByTagName("body"));
    /**
     * @param {JQuery.ClickEvent} e
     */
    const fancyboxInitialization = e => {
        e.preventDefault();

        if (!fancyboxInit) {
            fancyboxInit = true;

            import(/* webpackChunkName: "fancybox-popups-chunk" */ "@src/common/popups/index").then(
                async ({ modalLang, modalWithValidation, modalMep, modalDefault, modalGallery, modalVideo, modalSidebar, modalIframe }) => {
                    await modalLang(".fancyboxLang");
                    await modalDefault(".fancybox");
                    await modalMep(".fancyboxMep");
                    await modalWithValidation(".fancyboxValidateModal");
                    await modalWithValidation(".js-fancybox-validate-modal");
                    await modalGallery(".fancyboxGallery");
                    await modalVideo(".fancyboxVideo");
                    await modalSidebar(".fancyboxSidebar");
                    await modalIframe(".fancyboxIframe");
                    setTimeout(() => {
                        $(e.target).trigger("click");
                    }, 0);
                }
            );

            import(/* webpackChunkName: "fancybox-util-chunk" */ "@src/plugins/fancybox/v2/util").then(({ closeFancyBoxConfirm, onChangePopupContent }) => {
                body.on("change", ".fancybox-inner form :input", () => onChangePopupContent());
                EventHub.on("fancy-box:close", () => closeFancyBoxConfirm());
            });
        }
    };
    // Body events
    body
        // @ts-ignore
        .on("click", ".info-dialog", async e => {
            e.preventDefault();

            let modalContent;
            const { title, subTitle, message, content, keepModal } = $(e.currentTarget).data();

            if (message) {
                modalContent = message;
            } else if (content) {
                modalContent = $(content).html();
            }

            await loadBootstrapDialog();
            openResultModal({
                title,
                subTitle,
                content: modalContent,
                keepModal,
                closable: true,
                closeByBg: true,
                buttons: [
                    {
                        label: translate({ plug: "BootstrapDialog", text: "ok" }),
                        cssClass: "btn btn-light",
                        action(dialog) {
                            dialog.close();
                        },
                    },
                ],
            });
        })
        .on("click", ".info-dialog-ajax", async function onClickInfoDialogAjax(e) {
            e.preventDefault();

            const btn = $(this);
            const closeClickAction = btn.data("close-click") || "overlay";

            await loadBootstrapDialog();
            openResultModal({
                title: btn.data("title"),
                subTitle: null,
                content: btn.data("href") || null,
                isAjax: true,
                validate: Boolean(~~(btn.data("validate") || false)),
                classes: btn.data("classes") || "",
                closable: true,
                closeByBg: closeClickAction === "overlay",
                type: "info",
                buttons: [
                    {
                        label: translate({ plug: "BootstrapDialog", text: "close" }),
                        cssClass: "btn btn-light",
                        action(dialog) {
                            dialog.close();
                        },
                    },
                ],
            });
        })
        .on("click", ".js-require-logged-systmess", requireLoggedSystmess)
        // @ts-ignore
        // eslint-disable-next-line prettier/prettier
        .on("click", ".popup-dialog", preventDefault(onPopupOpen))
        // @ts-ignore
        // eslint-disable-next-line prettier/prettier
        .on("click", ".js-open-dialog", preventDefault(e => openDialog($(e.currentTarget || e.target))))
        // @ts-ignore
        // eslint-disable-next-line prettier/prettier
        .on(
            // @ts-ignore
            "click",
            ".confirm-dialog",
            preventDefault(e => openConfirmationDialog(e, $(e.currentTarget || e.target)))
        )
        .on("change", '.js-pseudo-radio-btn input[type="radio"]', function onChangePseudoRadio() {
            $(`.js-pseudo-radio-btn input[name=${$(this).attr("name")}]`)
                .not(this)
                .parent()
                .removeClass("selected");
            $(this).parent().addClass("selected");
        })
        .on("click", ".call-systmess", function onClickCallSystmess(e) {
            e.preventDefault();

            const btn = $(this);
            const mess = btn.data("message");
            const type = btn.data("type");

            systemMessages(mess, type);

            return false;
        });

    // Validation-engine
    $(document).on("click focusout", ".validengine input", loadingValidationEngine);
    $(document).on("submit", ".validengine", loadingValidationEngine);

    // Fancybox
    $(document).on(
        "click",
        ".fancyboxLang, .fancyboxMep, .fancyboxValidateModal, .js-fancybox-validate-modal, .fancybox, .fancyboxGallery, .fancyboxIframe, .fancyboxVideo",
        fancyboxInitialization
    );

    // Maintenance worker
    setTimeout(async () => {
        const { default: checkMaintenanceMode } = await import("@src/common/maintenance-mode/index");
        checkMaintenanceMode();
    }, 5000);

    // Periodical check session, each 10min;
    if (LOGGED_IN) {
        import("@src/common/session/checkSession").then(({ default: checkSession }) => checkSession());
    }

    // Hub events
    EventHub.on("system-message:show", (e, btn) => systemMessages(btn.data("message"), btn.data("type") || "error"));
    EventHub.on("system-mesages:card-close", (e, btn) => systemMessagesCardClose(btn));
    EventHub.on("system-mesages:close", () => systemMessagesClose());
    EventHub.on("lazy-loading:login", (e, button) => {
        if ($(window).width() > 991) {
            import(/* webpackChunkName: "login-index" */ "@src/pages/login/index");
        } else {
            button.removeClass("fancybox.ajax fancyboxValidateModal");
            $(".js-mep-header-mobile-login").trigger("click");
            setTimeout(() => {
                button.addClass("fancybox.ajax fancyboxValidateModal");
            }, 1000);
        }
    });
    EventHub.on("modal:open-video-modal", (e, btn) => openVideoModal(btn));
    EventHub.on("link:move-by-link", async (e, btn) => {
        const { default: callMoveByLink } = await import("@src/util/dom/call-move-by-link");
        callMoveByLink(btn);
    });
    EventHub.on("form:submit_form_subscribe", async (e, form) => {
        const { subscribeFormCallBack } = await import("@src/common/subscribe/index");
        subscribeFormCallBack(e, form);
    });
    EventHub.on("promo-banners:open-schedule-demo-popup", async (e, btn) => {
        const { default: openScheduleDemoPopup } = await import("@src/common/webinar_requests/schedule-demo-popup");
        openScheduleDemoPopup(btn);
    });

    if (document.getElementById("js-ep-sidebar")) {
        EventHub.on("sidebar:toggle-visibility", async () => {
            const { default: toggleSidebar } = await import("@src/components/sidebar/index");
            toggleSidebar();
        });
    }
});
