import { SUBDOMAIN_URL } from "@src/common/constants";
import { openResultModal } from "@src/plugins/bootstrap-dialog";
import { systemMessages } from "@src/util/system-messages";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

const shareEvent = async (_e, btn) => {
    try {
        const { subTitle, mess_type: type, title, content } = await postRequest(`${SUBDOMAIN_URL}user/popup_forms/share`, { type: "ep_event", itemId: btn.data("item") });
        if (type === "success") {
            openResultModal({
                title,
                subTitle,
                content,
                isAjax: false,
                closable: true,
                type: "info",
                icon: "share-stroke2",
                delimeterClass: "bootstrap-dialog--content-delimeter2 bootstrap-dialog--no-border",
                buttons: [],
            });
        } else {
            systemMessages(subTitle, type);
        }
    } catch (error) {
        handleRequestError(error);
    }
};

export default shareEvent;
