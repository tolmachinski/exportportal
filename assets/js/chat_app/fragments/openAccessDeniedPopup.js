import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { translate } from "@src/i18n";
import { SITE_URL } from "@src/common/constants";

const openAccessDeniedPopup = async btn => {
    await loadBootstrapDialog();
    const data = btn.data();
    openResultModal({
        title: data.title,
        subTitle: data.message,
        type: data.type ?? "info",
        closable: true,
        closeByBg: true,
        buttons: [
            {
                label: translate({ plug: "BootstrapDialog", text: "contact_us" }),
                cssClass: "btn btn-primary",
                action() {
                    globalThis.location.href = `${SITE_URL}contact`;
                },
            },
        ],
    });
};

export default openAccessDeniedPopup;
