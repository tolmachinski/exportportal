import { translate } from "@src/i18n";
import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";

/**
 * Open the notification dialog.
 */
export default async function successDialog({ title = null, text = null }) {
    await loadBootstrapDialog();

    return openResultModal({
        type: "success",
        title: title ?? "",
        subTitle: text ?? "",
        closable: true,
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
}
