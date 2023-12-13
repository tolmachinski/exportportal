import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { translate } from "@src/i18n";
import { SITE_URL, SUBDOMAIN_URL } from "@src/common/constants";
import postRequest from "@src/util/http/post-request";

const openCertificationUpgradePopup = async () => {
    await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/certification_upgrade`)
        .then(async response => {
            if (response.mess_type === "success") {
                await loadBootstrapDialog();
                await openResultModal({
                    title: translate({
                        plug: "general_i18n",
                        text: "js_popup_certification_update_ttl",
                    }),
                    subTitle: response.subTitle,
                    type: "warning",
                    classes: " inputs-40",
                    isAjax: false,
                    closable: true,
                    buttons: [
                        {
                            label: translate({
                                plug: "BootstrapDialog",
                                text: "ok",
                            }),
                            cssClass: "btn btn-light w-50pr-md-min mw-150",
                            action: dialog => {
                                dialog.close();
                            },
                        },
                        {
                            label: translate({
                                plug: "BootstrapDialog",
                                text: "stay_certified",
                            }),
                            cssClass: "btn btn-primary w-50pr-md-min mw-150",
                            action: () => {
                                globalThis.location.href = `${SITE_URL}upgrade`;
                            },
                        },
                    ],
                });
            }
        })
        .catch(() => {});
};

export default openCertificationUpgradePopup;
