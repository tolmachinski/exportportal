$(function(){
    $('.js-textcounter-email-message').textcounter({
        countDown: true,
        countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
        countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
    });
});

function friendInviteFormSubmit(form){
    var formWrapper = form.closest(".js-modal-flex");

    $.ajax({
        type: "POST",
        url: __site_url + "company/ajax_send_email/invite_external_customers",
        data: form.serialize(),
        dataType: "JSON",
        beforeSend: function() {
            showLoader(formWrapper, "Sending email...");
            form.find("button[type=submit]").addClass("disabled");
        },
        success: function(resp) {
            systemMessages( resp.message, resp.mess_type );

            if (resp.mess_type === "success") {
                closeFancyBox();
            }
        },
        complete: function(resp) {
            hideLoader(formWrapper);
            form.find("button[type=submit]").removeClass("disabled");
        },
    });
}
