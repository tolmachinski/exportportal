var subscribeFormCallBack = function (form) {
    var $form = $(form);
    var url = __current_sub_domain_url + "subscribe/ajax_subscribe_operation/subscribe";
    var fdata = $form.serialize();

    $form.find("button[type=submit]").addClass("disabled");
    showLoader($form);

    postRequest(url, fdata)
        .then(function (response) {
            if (response.mess_type === "success") {
                $form[0].reset();
                bootstrapDialogCloseAll();
                showSuccessSubscribtionPopup(response.message);
            } else if (response.mess_type === "info") {
                $form[0].reset();
                bootstrapDialogCloseAll();
                showConfirmSubscriptionPopup(response);
            } else {
                systemMessages(response.message, response.mess_type);
            }
        })
        .catch(function (e) {
            onRequestError(e);
        })
        .finally(function () {
            hideLoader($form);
            $form.find("button[type=submit]").removeClass("disabled");
        });
};

var showConfirmSubscriptionPopup = function (response) {
    open_result_modal({
        title: response.popupTitle,
        subTitle: response.message,
        type: "info",
        closable: true,
        closeByBg: true,
        buttons: [{
            label: translate_js({
                plug: "BootstrapDialog",
                text: "ok",
            }),
            cssClass: "btn btn-light",
            action: function (dialog) {
                dialog.close();
            },
        }, ],
    });
}

var showSuccessSubscribtionPopup = function (message) {
    open_result_modal({
        title: translate_js({ plug: "general_i18n", text: "subscribe_popup_success_txt" }),
        subTitle: message,
        type: "success",
        closable: true,
        closeByBg: true,
        buttons: [{
            label: translate_js({
                plug: "BootstrapDialog",
                text: "ok",
            }),
            cssClass: "btn btn-light",
            action: function (dialog) {
                dialog.close();
            },
        }, ],
    });
}
