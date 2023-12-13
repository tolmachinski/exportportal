import loadBootstrapDialog, { openHeaderImageModal } from "@src/plugins/bootstrap-dialog/index";
import { SUBDOMAIN_URL } from "@src/common/constants";
import sentPopupViewed from "@src/util/common/send-popup-viewed";
import postRequest from "@src/util/http/post-request";

// @ts-ignore
import imgTitle from "@images/subscribe/popups/subscribe_header.jpg";

const openSubscribePopup = async () => {
    await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/subscribe_popup`)
        .then(async response => {
            if (response.mess_type === "success") {
                await loadBootstrapDialog();
                // @ts-ignore
                await import("@scss/components/popups/subscribe/index.scss");
                await openHeaderImageModal({
                    title: response.title,
                    subTitle: response.subTitle,
                    content: response.content,
                    titleImage: imgTitle,
                    titleUppercase: true,
                    classes: " subscribe-benefits__modal",
                    classContent: "",
                    validate: true,
                    isAjax: false,
                    closeCallBack: () => {
                        if (!globalThis.closeDialog || globalThis.closeDialog !== "subscribe") {
                            globalThis.closeDialog = "";
                            sentPopupViewed("subscribe", "cancel");
                        }
                    },
                });
            }
        })
        .catch(() => {});
};

export default openSubscribePopup;
