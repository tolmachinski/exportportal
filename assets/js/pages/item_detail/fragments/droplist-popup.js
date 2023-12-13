import $ from "jquery";

import EventHub from "@src/event-hub";
import { hideLoader, showLoader } from "@src/util/common/loader";
import postRequest from "@src/util/http/post-request";
import { SITE_URL, SUBDOMAIN_URL } from "@src/common/constants";
import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { systemMessages } from "@src/util/system-messages";
import handleRequestError from "@src/util/http/handle-request-error";
import { translate } from "@src/i18n";
import { closeFancyBox, updateFancyboxPopup } from "@src/plugins/fancybox/v2/util";

const sendDroplistPopup = async (e, formSelector) => {
    const btn = $(".js-add-to-droplist");
    const form = $(formSelector);
    const submitButton = form.find("button[type=submit]");
    submitButton.addClass("disabled");
    showLoader(form);
    try {
        const { mess_type: messType, message, messTitle } = await postRequest(`${SUBDOMAIN_URL}/items/add_to_droplist`, form.serialize(), "JSON");
        if (messType === "success") {
            await loadBootstrapDialog();
            await openResultModal({
                title: messTitle,
                subTitle: message,
                classes: " inputs-40",
                closable: true,
                isAjax: false,
                type: "success",
                buttons: [
                    {
                        label: translate({ plug: "BootstrapDialog", text: "go_to_droplist" }),
                        cssClass: "btn btn-primary",
                        action: () => {
                            globalThis.location.href = `${SITE_URL}items/droplist`;
                        },
                    },
                    {
                        label: translate({ plug: "BootstrapDialog", text: "close" }),
                        cssClass: "btn btn-light",
                        action: dialog => {
                            dialog.close();
                        },
                    },
                ],
            });

            btn.data({
                message: translate({ plug: "BootstrapDialog", text: "items_droplist_remove_subttl" }),
                "js-action": "remove:droplist-item",
                title: translate({ plug: "BootstrapDialog", text: "items_droplist_remove_ttl" }),
            });
            btn.find("span").html(translate({ plug: "BootstrapDialog", text: "items_remove_from_droplist_btn" }));
            btn.addClass("js-confirm-dialog").removeClass("js-fancybox-validate-modal fancybox.ajax js-add-to-droplist");

            closeFancyBox();
            form.trigger("reset");
        } else {
            systemMessages(message, messType);
        }
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

    EventHub.off("droplist-popup:form-submit");
    EventHub.on("droplist-popup:form-submit", (e, form) => sendDroplistPopup(e, form));
    $("button.js-close-fancybox").on("click", e => {
        e.preventDefault();
        $(".validateModal").validationEngine("detach");
        $.fancybox.close();
    });
};
