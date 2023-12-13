function googleRecaptchaValidation (parameters, form) {
    return new Promise(function (resolve, reject) {
        if (!parameters.enabled_status) {
            resolve($(form));
        }

        grecaptcha.ready(function () {
            grecaptcha.execute(parameters.public_token).then(function(token) {
                resolve($(form).append('<input type="hidden" name="token" value="'+ token +'" />'));
            });
        });
    });
}


