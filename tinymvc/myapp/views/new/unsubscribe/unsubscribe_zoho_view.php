<?php tmvc::instance()->controller->view->display('new/google_recaptcha/script_inclusions');?>

<div class="main-login mt-25 footer-connect">
    <div class="main-login-form-wrp">
        <form
            class="main-login-form validengine"
            data-sto="-60"
            method="post"
            id="unsubscribe--form"
            data-callback="onUnsubscribe"
            onsubmit="return submitFunction(event)"
        >
            <div class="pt-25 pl-25 pr-25 pb-25">
                <div class="main-login-form__ttl">
                    <h1>Unsubscribe</h1>
                </div>

                <div class="info-alert-b tal">
                    <i class="ep-icon ep-icon_info-stroke"></i>
                    <span>If you don't wish to receive our notifications, please enter your email in this form and click the <strong>Unsubscribe</strong> button.</span>
                    <br>
                    <span>Please note that it may take some time to process your request.</span>
                </div>

                <div class="form-group">
                    <label class="input-label">Enter Your Email Address</label>
                    <input class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[100]]" type="text" name="email" placeholder="E-mail"/>
                </div>

                <button class="btn btn-primary btn-block" type="submit" name="usubscribe">Unsubscribe</button>
            </div>
        </form>
        <div class="main-login-form d-none" id="js-unsubscribe-success">
            <div class="pt-25 pl-25 pr-25 pb-25">
                <div class="main-login-form__ttl">
                    <h2>Unsubscribe</h2>
                </div>

                <div class="success-alert-b tal mb-25">
                    <i class="ep-icon ep-icon_info-stroke"></i>
                    <span>You have been successfully unsubscribed.</span>
                </div>

                <a href="<?php echo __SITE_URL;?>" class="btn btn-primary btn-block">Visit the Home page.</a>
            </div>
        </div>
    </div>
</div>

<script>

    function submitFunction(event) {
        event.preventDefault();
    }

    $(function() {
        var url = __site_url + 'user/unsubscribe_zohocrm';
        var form = $('#unsubscribe--form');
        var onCheck = function (){
            googleRecaptchaValidation(recaptcha_parameters, form).then(function(form) { onSave() });
        }
        var onSave = function (){
            var data = form.serializeArray();
            var saveButton = form.find('button[type=submit]');
            var onRequestStart = function() {
                saveButton.prop('disabled', true);
                showLoader(form);
            };
            var onRequestEnd = function() {
                saveButton.prop('disabled', false);
                hideLoader(form);
            };
            var onRequestSuccess = function (response) {
                if ('success' === response.mess_type) {
                    form.remove();
                    $('#js-unsubscribe-success').removeClass('d-none');
                } else{
                    systemMessages(response.message, response.mess_type);
                }
            };

            onRequestStart();
            return $.post(url, data, null, 'json').done(onRequestSuccess).fail(onRequestError).always(function() {
                onRequestEnd();
            });
        };

        mix(window, {
            onUnsubscribe: onCheck
        });
    });
</script>
