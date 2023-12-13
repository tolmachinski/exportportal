import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { translate } from "@src/i18n";
import { SITE_URL, SUBDOMAIN_URL } from "@src/common/constants";
import sentPopupViewed from "@src/util/common/send-popup-viewed";
import postRequest from "@src/util/http/post-request";

const openUpdateProfilePicturePopup = async () => {
    await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/update_profile_picture`)
        .then(async response => {
            if (response.mess_type === "success") {
                await loadBootstrapDialog();
                // @ts-ignore
                await import("@scss/components/popups/update_profile_picture/index.scss");
                await openResultModal({
                    title: response.title,
                    subTitle: response.subTitle,
                    content: response.content,
                    classes: " inputs-40",
                    closable: true,
                    isAjax: false,
                    type: "success",
                    buttons: [
                        {
                            label: translate({ plug: "BootstrapDialog", text: "skip" }),
                            cssClass: "btn btn-light",
                            action: dialog => {
                                dialog.close();
                            },
                        },
                        {
                            label: translate({ plug: "BootstrapDialog", text: "apply" }),
                            cssClass: "btn btn-primary",
                            action: () => {
                                globalThis.location.href = `${SITE_URL}user/photo`;
                            },
                        },
                    ],
                    openCallBack: sentPopupViewed("update_profile_picture"),
                });
            }
        })
        .catch(() => {});
};

export default openUpdateProfilePicturePopup;
