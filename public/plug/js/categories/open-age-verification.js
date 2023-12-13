function openAgeVerificationModal(that, redirect) {
    var redirect = redirect || false;

    open_result_modal({
        title: 'Age verification',
        subTitle: 'Export Portal requires you to be 18 years or older to view this category. Please enter your date of birth to continue.',
        content: __group_site_url + "categories/age_verification/check_age",
        type: 'warning',
        isAjax: true,
        validate: true,
        closable: true,
        closeCallBack: function() {
            if (redirect == true) {
                window.location.href = __site_url;
            } else {
                $(".ep-header").css("filter", "unset");
                $(".ep-content").css("filter", "unset");
            }
        },
        buttons: [
            {
                label: translate_js({ plug: "general_i18n", text:"form_button_submit_text"}),
                cssClass: "btn btn-primary js-submit-form",
                action: function () {
                    if(that != null && that.data('redirect')) {
                        $(this).data("redirect", that.data('redirect'));
                    }
                    $('body').find("#js-age-verification").submit();
                },
            }
        ]
    });

    $(".ep-header").css("filter", "blur(10px)");
    $(".ep-content").css("filter", "blur(10px)");
};
