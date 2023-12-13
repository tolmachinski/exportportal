import $ from "jquery";

import loadBootstrapDialog, { openHeaderImageModal } from "@src/plugins/bootstrap-dialog/index";
import { SUBDOMAIN_URL } from "@src/common/constants";
import postRequest from "@src/util/http/post-request";
import sentPopupViewed from "@src/util/common/send-popup-viewed";

const openFindBusinessPartnersPopup = async () => {
    await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/find_business_partners`)
        .then(async response => {
            if (response.mess_type === "success") {
                await loadBootstrapDialog();

                // @ts-ignore
                await import("@scss/components/popups/find_business_partners/index.scss");
                await openHeaderImageModal({
                    title: response.title,
                    subTitle: response.subTitle,
                    content: "",
                    contentFooter: response.content,
                    titleImage: response.image,
                    titleUppercase: true,
                    classes: "",
                    classContent: "",
                    validate: true,
                    isAjax: false,
                    closeCallBack: () => {
                        const checkbox = $("#js-find-business-partners-actions .js-find-business-partners");
                        if (checkbox.length) {
                            if (checkbox.prop("checked")) {
                                sentPopupViewed("find_business_partners", "cancel");
                            } else {
                                sentPopupViewed("find_business_partners");
                            }
                        }
                    },
                });
            }
        })
        .catch(() => {});
};

export default openFindBusinessPartnersPopup;
