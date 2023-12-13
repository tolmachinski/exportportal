import { translate } from "@src/i18n";
import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";

/**
 * Open the notification dialog.
 *
 * @param {{additionalButton: { text: string, class: string, location: string }, title: string, subTitle?: string }} param
 */
export default async function notificationDialog({ additionalButton, title, subTitle = "" }) {
    await loadBootstrapDialog();

    const buttons = [];
    if (additionalButton) {
        const { text, class: className, location } = additionalButton;
        buttons.push({
            label: translate({ plug: "general_i18n", text }),
            cssClass: className,
            action() {
                globalThis.location.href = location;
            },
        });
    }
    buttons.push({
        label: translate({ plug: "BootstrapDialog", text: "close" }),
        cssClass: "btn-light",
        action: (/** @type {{ close: () => void; }} */ dialogRef) => {
            dialogRef.close();
        },
    });

    return openResultModal({
        title,
        subTitle,
        type: "success",
        closable: true,
        buttons,
    });
}
