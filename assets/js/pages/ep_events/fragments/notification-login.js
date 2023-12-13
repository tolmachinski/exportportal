import $ from "jquery";
import { translate } from "@src/i18n";

const notificationEventLogin = async (e, btn) => {
    const { title, subTitle } = btn.data();
    const { default: boot, openResultModal } = await import("@src/plugins/bootstrap-dialog/index");
    await boot();
    await openResultModal({
        title,
        subTitle,
        closable: true,
        buttons: [
            {
                label: translate({ plug: "BootstrapDialog", text: "login" }),
                cssClass: "btn btn-primary",
                action(dialog) {
                    dialog.close();
                    $(".js-sign-in").trigger("click");
                },
            },
            {
                label: translate({ plug: "BootstrapDialog", text: "cancel" }),
                cssClass: "btn btn-light",
                action(dialogRef) {
                    dialogRef.close();
                },
            },
        ],
    });
};

export default notificationEventLogin;
