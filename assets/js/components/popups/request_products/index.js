import $ from "jquery";

import { hideLoader, showLoader } from "@src/util/common/loader";
import { SUBDOMAIN_URL } from "@src/common/constants";

import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { closeFancyboxPopup, updateFancyboxPopup } from "@src/plugins/fancybox/v2/util";
import { renderTemplate } from "@src/util/templates";
import { addCounter } from "@src/plugins/textcounter/index";
import { translate } from "@src/i18n";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

const sendProductRequest = () => {
    const form = $("#js-product-request-send-form");

    const showSuccessDialog = async message => {
        await loadBootstrapDialog();
        openResultModal({
            title: translate({ plug: "general_i18n", text: "product_requests_success_dialog_title" }) || null,
            content: message || null,
            type: "success",
            buttons: [
                {
                    label: translate({ plug: "BootstrapDialog", text: "close" }),
                    cssClass: "btn btn-light",
                    action(dialog) {
                        dialog.close();
                    },
                },
            ],
        });
    };

    const onRequestSuccess = function (response) {
        if (response.mess_type && response.mess_type === "success") {
            closeFancyboxPopup();

            showSuccessDialog(
                renderTemplate(translate({ plug: "general_i18n", text: "request_products_success_message" }), {
                    email: response.data.product.email || "",
                    url: `${SUBDOMAIN_URL}contact`,
                })
            );
        }

        return { data: response.data || {}, texts: response.texts || {} };
    };

    showLoader(form);
    form.find('button[type="submit"]').prop("disabled", true);

    return postRequest(`${SUBDOMAIN_URL}product_requests/ajax_operations/send`, form.serializeArray())
        .then(onRequestSuccess)
        .catch(handleRequestError)
        .finally(() => {
            form.find('button[type="submit"]').prop("disabled", false);
            hideLoader(form);
        });
};

const initProductRequestForm = () => {
    const form = $("#js-product-request-send-form");
    addCounter(form.find(".js-details"));

    form.find(".js-info-toggle-handler").on("click", function clickToggleAdditional() {
        $(this).find(".js-info-toggle-icon").toggleClass("ep-icon_minus-stroke ep-icon_plus-stroke");
        $(".js-info-toggle-block").toggle();

        updateFancyboxPopup();
    });
};

export default () => {
    initProductRequestForm();

    EventHub.off("request-products:save");
    EventHub.on("request-products:save", () => sendProductRequest());
};
