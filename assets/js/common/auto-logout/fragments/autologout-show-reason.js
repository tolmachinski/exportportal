import { openResultModal } from "@src/plugins/bootstrap-dialog";
import { translate } from "@src/i18n";
import { LANG, SHIPPER_PAGE, SHIPPER_URL, SITE_URL } from "@src/common/constants";

export default async () => {
    globalThis.history.pushState({}, document.title, globalThis.location.origin + globalThis.location.pathname);

    await import(/* webpackChunkName: "fancybox-i18n" */ `@plug/bootstrap-dialog-1-35-4/lang/${LANG}.js`);

    openResultModal({
        title: "Session Timeout",
        content: "You have been logged out due to inactivity. Please sign in again to continue using Export Portal.",
        isAjax: false,
        closable: true,
        type: "info",
        classContent: "modal-tinymce-text",
        buttons: [
            {
                label: translate({ plug: "BootstrapDialog", text: "close" }),
                cssClass: "btn btn-light",
                action(dialog) {
                    dialog.close();
                },
            },
            {
                label: "Sign in",
                cssClass: "btn btn-primary",
                action() {
                    window.location.href = SHIPPER_PAGE ? `${SHIPPER_URL}login` : `${SITE_URL}login`;
                },
            },
        ],
    });
};
