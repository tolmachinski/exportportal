import { hideLoader, showLoader } from "@src/util/common/loader";
import { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { systemMessages } from "@src/util/system-messages/index";
import { closeFancyBox } from "@src/plugins/fancybox/v2/util";
import { SITE_URL } from "@src/common/constants";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

const saveSearchSubmit = async (e, form) => {
    const submitButton = form.find("button[type=submit]");
    submitButton.addClass("disabled");
    showLoader(form);

    try {
        const { mess_type: messType, message } = await postRequest(
            `${SITE_URL}save_search/ajax_savesearch_operations/add_search_saved`,
            form.serialize(),
            "JSON"
        );
        if (messType === "success") {
            closeFancyBox();
            openResultModal({
                title: "Success!",
                subTitle: message,
                type: "success",
                closable: true,
                buttons: [
                    {
                        label: "Ok",
                        cssClass: "btn btn-light",
                        action(dialog) {
                            dialog.close();
                        },
                    },
                ],
            });
        } else {
            systemMessages(message, messType);
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        submitButton.removeClass("disabled");
        hideLoader(form);
    }
};

export default () => {
    EventHub.off("save-search:form-submit");
    EventHub.on("save-search:form-submit", saveSearchSubmit);
};
