import { translate } from "@src/i18n";
import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";

/**
 * Show confirmation dialog.
 *
 * @param {JQuery} button
 */
export default async function confirmationDialog(button) {
    await loadBootstrapDialog();

    return new Promise(resolve => {
        openResultModal({
            title: button.data("title"),
            subTitle: button.data("message"),
            isAjax: false,
            closable: true,
            closeByBg: true,
            type: "warning",
            classes: "tac",
            buttons: [
                {
                    label: translate({ plug: "BootstrapDialog", text: "cancel" }),
                    cssClass: "btn btn-light",
                    action(dialog) {
                        dialog.close();
                        resolve({ dialog: this, isConfirmed: false });
                    },
                },
                {
                    label: translate({ plug: "BootstrapDialog", text: "confirm" }),
                    cssClass: "btn btn-primary",
                    action() {
                        resolve({ dialog: this, isConfirmed: true });
                    },
                },
            ],
        });
    });
}
