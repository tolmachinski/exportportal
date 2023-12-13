import loadBootstrapDialog, { openHeaderImageModal } from "@src/plugins/bootstrap-dialog/index";
import { translate } from "@src/i18n";
import { SITE_URL, SUBDOMAIN_URL } from "@src/common/constants";
import postRequest from "@src/util/http/post-request";

const openCertificationExpireSoonPopup = async () => {
    await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/certification_expire_soon`)
        .then(async response => {
            if (response.mess_type === "success") {
                await import("@scss/components/popups/certification_expire_soon/index.scss");
                await loadBootstrapDialog();
                await openHeaderImageModal({
                    title: response.title,
                    subTitle: response.subTitle,
                    content: response.content,
                    type: "warning",
                    buttons: [
                        {
                            label: translate({ plug: "BootstrapDialog", text: "ok" }),
                            cssClass: "btn btn-light",
                            action: dialog => dialog.close(),
                        },
                        {
                            label: 'Renew <span class="hide-767">your Certification</span>',
                            cssClass: "btn btn-primary",
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

export default openCertificationExpireSoonPopup;
