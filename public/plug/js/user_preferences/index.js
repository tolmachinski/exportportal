var saveUserInfo = function (form) {
    var $this = $(form);
    var onRequestStart = function () {
        showLoader('form[data-callback="saveUserInfo"]', "default", "fixed");
    };
    var onRequestEnd = function () {
        hideLoader('form[data-callback="saveUserInfo"]');
    };
    var onRequestSuccess = function (response) {
        if (response.mess_type == "success") {
            var params = {
                title: "Success!",
                subTitle: response.message,
                additional_button: null,
            };

            if (null !== response.url) {
                params.additional_button = {
                    text: "js_bootstrap_dialog_view_info",
                    class: "btn-primary",
                    location: response.url || null,
                };
            }

            companyNotificationModal(params);
            dispatchCustomEvent("user-profile:saved", globalThis);
        } else {
            systemMessages(response.message, response.mess_type);
        }
    };
    onRequestStart();

    return postRequest(__current_sub_domain_url + "profile/ajax_operations/save-legacy", form.serializeArray())
        .then(onRequestSuccess)
        .catch(onRequestError)
        .then(onRequestEnd);
};

$(function () {
    $(".textcounter").textcounter({
        countDown: true,
        countDownTextBefore: translate_js({
            plug: "textcounter",
            text: "count_down_text_before",
        }),
        countDownTextAfter: translate_js({
            plug: "textcounter",
            text: "count_down_text_after",
        }),
    });
});

var useExistingInformation = function (btn) {
    var accountId = btn.data("account");
    var $body = $("body");

    showLoader($body);
    bootstrapDialogCloseAll();

    return postRequest(__current_sub_domain_url + "profile/ajax_operations/use-existing", { account: accountId })
        .then(function (data) {
            if ("success" === data.mess_type) {
                location.reload(true);
            } else {
                hideLoader($body);
                systemMessages(data.message, data.mess_type);
            }
        })
        .catch(onRequestError)
        .finally(function(e) {
            hideLoader($body);
        });
};

var openConfirmPopup = function(btn) {
    var $thisBtn = $(btn);

    open_result_modal({
        title: $thisBtn.data('title'),
        subTitle: $thisBtn.data('message'),
        isAjax: false,
        closable: true,
        closeByBg: true,
        type: "warning",
        classes: "tac",
        buttons: [
            {
                label: translate_js({ plug: "BootstrapDialog", text: "cancel" }),
                cssClass: "btn btn-light",
                action: function (dialog) {
                    dialog.close();
                },
            },
            {
                label: translate_js({ plug: "BootstrapDialog", text: "confirm" }),
                cssClass: "btn btn-primary",
                action: function () {
                    useExistingInformation($thisBtn);
                },
            }
        ]
    });
}
