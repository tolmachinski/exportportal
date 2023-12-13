var isSubmitedForm = false;

var openScheduleDemoPopup = function (btn) {
    var url = __current_sub_domain_url + btn.data("href");

    if (btn.data("hash")) {
        url = __current_sub_domain_url + btn.data("href") + "?request=" + btn.data("hash");
    }

    callHeaderImageModal({
        title: translate_js({ plug: "general_i18n", text: "js_schedule_a_demo_popup_title" }),
        titleUppercase: true,
        subTitle: translate_js({ plug: "general_i18n", text: "js_schedule_a_demo_popup_subtitle" }),
        titleImage: btn.data("popupBg"),
        isAjax: true,
        content: url,
        classes: "schedule-demo-popup",
        validate: true,
        closeCallBack: function() {
            if (!isSubmitedForm) {
                callGAEvent('schedule_demo_popup_close_not_submit', 'webinar-requests');
            }
        }
    });

    callGAEvent('schedule_demo_popup_open', 'webinar-requests');
};

var webinarRequestFormCallBack = function (formSelector) {
    var form = $(formSelector);

    form.find("button[type=submit]").addClass("disabled");
    showLoader(form);

    postRequest(__current_sub_domain_url + "webinar_requests/ajax_operations/requesting_a_demo", form.serialize())
        .then(async response => {
            if (response.mess_type === "success") {
                form.trigger("reset");
                isSubmitedForm = true;
                bootstrapDialogCloseAll();

                open_result_modal({
                    subTitle: response.message,
                    type: "success",
                    closable: true,
                    closeByBg: true,
                    buttons: [
                        {
                            label: translate_js({
                                plug: "BootstrapDialog",
                                text: "ok",
                            }),
                            cssClass: "btn btn-light",
                            action(dialog) {
                                dialog.close();
                            },
                        },
                    ],
                });
            } else {
                systemMessages(response.message, response.mess_type);
            }
        })
        .catch(onRequestError)
        .finally(() => {
            hideLoader(form);
            form.find("button[type=submit]").removeClass("disabled");
        });
};
