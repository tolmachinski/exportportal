var ContactUsLegacyFragment = (function () {

    function sendRequestContact(loadingMessage, url, form) {
        var $form = $(form);
        var fdata = $form.serialize();
        $.ajax({
            type: 'POST',
            url: url,
            data: fdata,
            dataType: 'JSON',
            beforeSend: function(){
                showLoader($(form), loadingMessage);
            },
            success: function(resp){
                systemMessages( resp.message, resp.mess_type );

                if(resp.mess_type == 'success'){
                    $form[0].reset();
                    closeFancyBox();
                }
            },
            complete: function(){
                hideLoader($(form));
                setTimeout(function(){
                    $form.find('button[type=submit]').removeClass('disabled');
                }, 350);
            }
        });
    }

    function contactUsFormSubmit(isLogged, loadingMessage, url, form){
        $(form).find('button[type=submit]').addClass('disabled');

        if(isLogged){
            sendRequestContact(loadingMessage, url, form);
        } else {
            googleRecaptchaValidation(recaptcha_parameters, form).then(function(form) {
                sendRequestContact(loadingMessage, url, form);
            }).catch(function(){
                $(form).find('button[type=submit]').removeClass('disabled');
            });
        }
    }

    function entrypoint (isLogged, loadingMessage, url, textErorCountryCode, textErorPhoneMask) {
        $('.textcounter_contact-message').textcounter({
            countDown: true,
            countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
            countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
        });

        mix(globalThis, {
            contactUsFormSubmit: contactUsFormSubmit.bind(null, isLogged, loadingMessage, url)
        }, false);

        if (!isLogged) {
            const dropdownWrapper = $(".js-contact-us-dropdown-wrapper");

            userPhoneMask.init({
                selectedFax: 0,
                selectedPhone: 0,
                selectorPhoneCod: 'select#js-contact-country-code',
                selectorPhoneNumber: '#js-contact-phone-number',
                textErorCountryCode: textErorCountryCode,
                textErorPhoneMask: textErorPhoneMask,
                dropdownParent: dropdownWrapper.length ? dropdownWrapper : $("body"),
            });
        }
    }

    return {
        default: entrypoint,
    };
})();
