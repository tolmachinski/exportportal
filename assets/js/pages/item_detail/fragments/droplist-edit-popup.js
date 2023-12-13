import $ from "jquery";

import EventHub from "@src/event-hub";
import { hideLoader, showLoader } from "@src/util/common/loader";
import postRequest from "@src/util/http/post-request";
import { SUBDOMAIN_URL } from "@src/common/constants";
import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { systemMessages } from "@src/util/system-messages";
import handleRequestError from "@src/util/http/handle-request-error";
import { translate } from "@src/i18n";
import { closeFancyBox, updateFancyboxPopup } from "@src/plugins/fancybox/v2/util";

const sendDroplistEditPopup = async (e, formSelector) => {
    const form = $(formSelector);
    const submitButton = form.find("button[type=submit]");
    submitButton.addClass("disabled");
    showLoader(form);
    try {
        const { mess_type: messType, message, modal_subtitle: modalSubTitle, modal_text: modalText } = await postRequest(
            `${SUBDOMAIN_URL}/items/edit_droplist_item`,
            form.serialize(),
            "JSON"
        );
        closeFancyBox();
        if (messType === "warning") {
            await loadBootstrapDialog();
            await openResultModal({
                subTitle: modalSubTitle,
                content: modalText,
                closable: true,
                buttons: [
                    {
                        label: translate({ plug: "BootstrapDialog", text: "ok" }),
                        cssClass: "btn btn-light",
                        action(dialogRef) {
                            dialogRef.close();
                        },
                    },
                ],
            });
            return;
        }
        form.trigger("reset");
        await systemMessages(message, messType);
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(form);
        submitButton.removeClass("disabled");
    }
};

export default async () => {
    /* css */
    await import("@scss/user_pages/item_detail_page/popups/droplist-popup.scss");
    await updateFancyboxPopup();

    EventHub.off("droplist-edit-popup:form-submit");
    EventHub.on("droplist-edit-popup:form-submit", (e, form) => sendDroplistEditPopup(e, form));
    $("button.js-close-fancybox").on("click", e => {
        e.preventDefault();
        $(".validateModal").validationEngine("detach");
        $.fancybox.close();
    });
};
