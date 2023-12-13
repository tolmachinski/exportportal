<script>
    $(function() {
        var url = __site_url + 'user/ajax_user_unsubscribe';
        var form = $('#js-unsubscribe-form');
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
