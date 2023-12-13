var SendProductRequestModule = (function () {
    "use strict";

    /**
     * Sends product request to the server.
     *
     * @returns {Promise<any>}
     */
    function sendProductRequest(isDialogPopup) {
        var form = $("#js-product-request-send-form");

        var showSuccessDialog = function (message) {
            return new Promise(function (resolve, reject) {
                if (typeof open_result_modal === "undefined" || !("open_result_modal" in window)) {
                    reject(new Error("The 'open_result_modal' is not found."));

                    return;
                }

                open_result_modal({
                    title: translate_js({ plug: 'general_i18n', text: 'product_requests_success_dialog_title' }) || null,
                    content: message || null,
                    type: "success",
                    buttons: [
                        {
                            label: translate_js({ plug: "BootstrapDialog", text: "close" }),
                            cssClass: "btn btn-light",
                            action: function (dialog) {
                                dialog.close();
                            },
                        }
                    ]
                });
            });
        }

        var onRequestSuccess = function (response) {
            if (response.mess_type && "success" === response.mess_type) {
                if (isDialogPopup) {
                    if ("BootstrapDialog" in globalThis) {
                        closeBootstrapDialog(form);
                    }
                } else {
                    closeFancyboxPopup();
                }

                showSuccessDialog(renderTemplate(translate_js({ plug: 'general_i18n', text: 'request_products_success_message' }), { email: response.data.product.email || '', url: __current_sub_domain_url + "contact" }));
            }

            return { data: response.data || {}, texts: response.texts || {} };
        };

        showLoader(form);
        form.find('button[type="submit"]').prop("disabled", true);

        return postRequest(__current_sub_domain_url + "product_requests/ajax_operations/send", form.serializeArray())
            .then(onRequestSuccess)
            .catch(onRequestError)
            .finally(function () {
                form.find('button[type="submit"]').prop("disabled", false);
                hideLoader(form);
            });
    }

    /**
     * Module entrypoint.
     *
     * @param {ModuleParameters} params
     */
    function entrypoint() {
        const form = $("#js-product-request-send-form");

        form.find(".js-details").textcounter({
            countDown: true,
            countDownTextAfter: translate_js({ plug: "textcounter", text: "count_down_text_after" }),
            countDownTextBefore: translate_js({ plug: "textcounter", text: "count_down_text_before" }),
        });

        form.find(".js-info-toggle-handler").on("click", function(e) {
            e.preventDefault();
            $(this).find(".js-info-toggle-icon").toggleClass("ep-icon_minus-stroke ep-icon_plus-stroke");
            $(".js-info-toggle-block").toggle();

            if (typeof $.fancybox !== "undefined") {
                $.fancybox.update();
            }
        });

        return {
            save: function () {
                return sendProductRequest();
            },
        };
    }

    return { entrypoint };
})();
