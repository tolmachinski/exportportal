import loadBootstrapDialog, { openHeaderImageModal } from "@src/plugins/bootstrap-dialog/index";
import { translate } from "@src/i18n";
import { SUBDOMAIN_URL } from "@src/common/constants";
import sentPopupViewed from "@src/util/common/send-popup-viewed";
import postRequest from "@src/util/http/post-request";

import imgTitle from "@images/bulk_popup/bulk_popup_header.jpg";

const openBulkUploadItemsPromotionPopup = async () => {
    await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/bulk_upload_items_promotion`)
        .then(async response => {
            if (response.mess_type === "success") {
                await import("@scss/components/popups/bulk_upload_items_promotion/index.scss");
                await loadBootstrapDialog();
                await openHeaderImageModal({
                    classes: "bootstrap-dialog-black-icon",
                    title: response.title,
                    subTitle: response.subTitle,
                    content: response.content,
                    titleImage: imgTitle,
                    titleUppercase: true,
                    isAjax: false,
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
                    openCallBack: () => {
                        sentPopupViewed("bulk_upload_items_promotion");
                    },
                });
            }
        })
        .catch(() => {});
};

export default openBulkUploadItemsPromotionPopup;
