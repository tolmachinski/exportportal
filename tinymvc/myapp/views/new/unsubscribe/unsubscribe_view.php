<div class="main-login mt-25 footer-connect">
    <div class="main-login-form-wrp">
        <form
            class="main-login-form validengine"
            data-sto="-60"
            method="post"
            id="unsubscribe--form"
            data-callback="onUnsubscribe"
        >
            <div class="main-login-form__ttl">
                <h1>Unsubscribe</h1>

                <div class="info-alert-b tal mt-25">
                    <i class="ep-icon ep-icon_info-stroke"></i>
                    <span>If you don't wish to receive our notifications, please enter your email in this form and click the "Unsubscribe" button.</span>
                    <br>
                    <span>Please note that it may take some time to process your request.</span>
                </div>
            </div>

            <input class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]" type="text" name="email" placeholder="E-mail"/>
            <span class="view-password">
                <span class="ep-icon ep-icon_invisible call-function" data-callback="viewPassword"></span>
                <input type="password" name="password" class="validate[required]" placeholder="Password"/>
            </span>

            <button class="btn btn-primary btn-block" type="submit" name="usubscribe">Unsubscribe</button>
        </form>
    </div>
</div>

<script>
    $(function() {
        var url = __site_url + 'user/ajax_user_unsubscribe';
        var form = $('#unsubscribe--form');
        var onSave = function (form, url){
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
                systemMessages(response.message, response.mess_type);
                if ('success' === response.mess_type || 'info' === response.mess_type) {
                    form.get(0).reset();
                }
            };

            onRequestStart();
            return $.post(url, data, null, 'json').done(onRequestSuccess).fail(onRequestError).always(function() {
                onRequestEnd();
            });
        };

        mix(window, {
            onUnsubscribe: onSave.bind(null, form, url)
        });
    });
</script>
