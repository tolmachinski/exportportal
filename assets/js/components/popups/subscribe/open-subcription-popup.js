import { translate } from "@src/i18n";

const openSubscribtionPopup = async (title, subTitle, type) => {
    const { openResultModal, default: loadBootstrapDialog } = await import("@src/plugins/bootstrap-dialog/index");
    await loadBootstrapDialog();

    return openResultModal({
        title,
        subTitle,
        type,
        closable: true,
        closeByBg: true,
        buttons: [
            {
                label: translate({
                    plug: "BootstrapDialog",
                    text: "ok",
                }),
                cssClass: "btn btn-light",
                action(dialog) {
                    dialog.close();
                },
            },
        ],
    });
};

export default openSubscribtionPopup;
