import $ from "jquery";

import loadBootstrapDialog, { openHeaderImageModal } from "@src/plugins/bootstrap-dialog/index";
import { SUBDOMAIN_URL } from "@src/common/constants";
import postRequest from "@src/util/http/post-request";
import sentPopupViewed from "@src/util/common/send-popup-viewed";

const openSubscribePopup = async () => {
    await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/giveaway_contest`)
        .then(async response => {
            if (response.mess_type === "success") {
                await loadBootstrapDialog();

                // @ts-ignore
                await import("@scss/components/popups/giveaway_contest/index.scss");
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
                        const checkbox = $("#js-giveaway-contests-actions .js-giveaway-contests");
                        if (checkbox.length) {
                            if (checkbox.prop("checked")) {
                                sentPopupViewed("giveaway_contest");
                            }
                        }
                    },
                });
            }
        })
        .catch(() => {});
};

export default openSubscribePopup;
