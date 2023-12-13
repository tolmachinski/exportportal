import $ from "jquery";

import "@src/boot/jquery-hooks";
import "@src/boot/http-api";

import { closeFancyboxPopup } from "@src/plugins/fancybox/v3/util";
import { dispatchHubEvent } from "@src/util/events";
import loadBootstrapDialog, { closeAllDialogs, openResultModal, openConfirmationDialog } from "@src/plugins/bootstrap-dialog/index";
import EventHub from "@src/event-hub";
import Platform from "@src/epl/platform";
import "@src/app.fragment";
import { translate } from "./i18n";

$(() => {
    $(document.body).on("click", globalThis.ENCORE_MODE ? ".call-action" : ".call-action:not(.call-function)", dispatchHubEvent);

    const body = $(document.getElementsByTagName("body"));

    if (!globalThis.ENCORE_MODE) {
        let fancyboxInit = false;

        $(document).on("click", ".js-fancybox-validate-modal", e => {
            e.preventDefault();

            if (!fancyboxInit) {
                fancyboxInit = true;

                import(/* webpackChunkName: "fancybox-popups-chunk" */ "@src/common/popups/index").then(async ({ modalWithValidation }) => {
                    await modalWithValidation(".js-fancybox-validate-modal");
                    setTimeout(() => {
                        $(e.target).trigger("click");
                    }, 0);
                });

                import(/* webpackChunkName: "fancybox-util-chunk" */ "@src/plugins/fancybox/v2/util").then(({ closeFancyBoxConfirm, onChangePopupContent }) => {
                    body.on("change", ".fancybox-inner form :input", () => onChangePopupContent());
                    EventHub.on("fancy-box:close", () => closeFancyBoxConfirm());
                });
            }
        });
    }

    body.on(
        // @ts-ignore
        "click",
        ".js-confirm-dialog:not(.confirm-dialog)",
        function openConfirm(e) {
            if (!Platform.eplPage) {
                openConfirmationDialog(e, $(e.currentTarget || e.target));
            }
        }
    ).on("click", ".js-information-dialog", async e => {
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
    });

    globalThis.addEventListener("modal:call-close-modal", () => closeAllDialogs(), {});
    EventHub.on("modal:call-close-modal", () => closeAllDialogs());

    globalThis.addEventListener("modal:call-close-fancy3", () => closeFancyboxPopup(), {});
    EventHub.on("modal:call-close-fancy3", () => closeFancyboxPopup());

    EventHub.on("categories:show-side-categories", async () => {
        const { default: showSideCategories } = await import("@src/components/categories/index");
        showSideCategories();
    });
    EventHub.on("account:open-completion-popup", async () => {
        const { default: openCompleteProfilePopup } = await import("@src/components/popups_system/popups/complete_profile");
        openCompleteProfilePopup();
    });

    EventHub.on("favorites:save-product", async (_e, btn) => {
        const { default: saveProduct } = await import("@src/components/favorites-item/save-product");
        saveProduct(btn);
    });
    EventHub.on("favorites:remove-product", async (_e, btn) => {
        const { default: removeSavedProduct } = await import("@src/components/favorites-item/remove-saved-product");
        removeSavedProduct(btn);
    });

    EventHub.on("item-card:toggle-actions", async (_e, btn) => {
        const { default: toggleActions } = await import("@src/components/favorites-item/toggle-actions");
        toggleActions(btn);
    });

    EventHub.on("click-to-call:open-callback-popup", async (e, btn) => {
        const { default: openClickToCallPopup } = await import("@src/components/popups/click-to-call/click-to-call");
        openClickToCallPopup(btn);
    });

    EventHub.off("dashboard:logout");
    EventHub.on("dashboard:logout", async (e, button) => {
        const { default: dashboardLogout } = await import("@src/components/dashboard/fragments/dashboard-logout");
        dashboardLogout(e, button);
    });

    EventHub.off("chat:open-access-denied-popup");
    EventHub.on("chat:open-access-denied-popup", async (_e, button) => {
        const { default: openAccessDeniedPopup } = await import("@src/chat_app/fragments/openAccessDeniedPopup");
        openAccessDeniedPopup(button);
    });
});
