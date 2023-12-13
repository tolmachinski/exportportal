import $ from "jquery";

import loadBootstrapDialog, { closeAllDialogs } from "@src/plugins/bootstrap-dialog/index";
import { systemMessages } from "@src/util/system-messages/index";
import { SITE_URL } from "@src/common/constants";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

const submitShareModal = async form => {
    const element = $(form);
    element.find("button[type=submit]").addClass("disabled");

    try {
        const { mess_type: messageType, message } = await postRequest(`${SITE_URL}downloadable_materials/ajaxShareAdministration/create`, element.serialize());

        if (messageType === "success") {
            systemMessages(message, messageType);

            await loadBootstrapDialog();
            closeAllDialogs();
        }
    } catch (err) {
        handleRequestError(err);
    } finally {
        element.find("button[type=submit]").removeClass("disabled");
    }
};

export default () => {
    EventHub.off("user_register:share_submit");
    EventHub.on("user_register:share_submit", (e, form) => submitShareModal(form));
};
