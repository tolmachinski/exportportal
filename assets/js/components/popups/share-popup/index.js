import { SUBDOMAIN_URL } from "@src/common/constants";
import { systemMessages } from "@src/util/system-messages/index";
import { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

let userSharePopupIsActive = false;
const userSharePopup = $this => {
    if (userSharePopupIsActive) {
        return true;
    }

    userSharePopupIsActive = true;
    const type = $this.data("type");
    const itemId = $this.data("item");

    postRequest(`${SUBDOMAIN_URL}user/popup_forms/share`, { type, itemId })
        .then(response => {
            if (response.mess_type === "success") {
                openResultModal({
                    title: response.title,
                    subTitle: response.subTitle,
                    content: response.content,
                    isAjax: false,
                    closable: true,
                    type: "info",
                    icon: "share-stroke2",
                    delimeterClass: "bootstrap-dialog--content-delimeter2 bootstrap-dialog--no-border",
                    buttons: [],
                });
            } else {
                systemMessages(response.message, response.mess_type);
            }

            userSharePopupIsActive = false;
        })
        .catch(handleRequestError);

    return true;
};

export default () => {
    EventHub.on("user:share-popup", (e, button) => userSharePopup(button));
};
