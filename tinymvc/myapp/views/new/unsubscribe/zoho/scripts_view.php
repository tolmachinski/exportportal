<?php tmvc::instance()->controller->view->display('new/google_recaptcha/script_inclusions');?>

<script>
    $(function() {
        var url = __site_url + 'user/unsubscribe_zohocrm';
        var form = $('#js-unsubscribe-form');
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
                systemMessages(response.message, response.mess_type);

                if ('success' === response.mess_type) {
                    form.get(0).reset();
                    $("#js-select-reason").val('');
                    $("#js-select-reason").change();

                    if ($('#js-reason-message').is(':visible')) {
                        $('#js-reason-message').hide();
                    }
                }
            };

            onRequestStart();
            return $.post(url, data, null, 'json').done(onRequestSuccess).fail(onRequestError).always(function() {
                onRequestEnd();
            });
        };

        $("#js-select-reason").select2({
            minimumResultsForSearch: Infinity,
            placeholder: translate_js({plug: "general_i18n", text: "js_select_the_reason_placeholder"}),
        }).on('select2:select', function (e) {
            var option = $(this).val();
            var reasonMess = $('#js-reason-message');

            if ("other" === option) {
                reasonMess.show();
            } else {
                reasonMess.hide();
            }
        });

        mix(window, {
            onUnsubscribe: onCheck
        });
    });
</script>
