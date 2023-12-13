<?php
    use App\Documents\Versioning\AbstractVersion;
    use App\Documents\Versioning\ContentContextEntries;
?>
<div class="wr-modal-b">
    <form id="verification--edit-additional-fields--form"
        class="validateModal relative-b"
        method="post"
        action="<?php echo $action; ?>"
        data-callback="saveVerificationCustomFields">
        <input type="hidden" name="user" value="<?php echo cleanOutput($user); ?>">
        <input type="hidden" name="document" value="<?php echo cleanOutput($document); ?>">
		<div class="modal-b__content p-0">
            <?php /** @var AbstractVersion $version */ ?>
            <?php if (in_array('businessNumber', $version->getContext()->get(ContentContextEntries::DYNAMIC_FIELDS_NAMES_LIST))) { ?>
                <label class="modal-b__label">Business Number</label>
                <input
                    <?php echo addQaUniqueIdentifier("admin-users__verification-edit-field-form__business-number-input")?>
                    type="text"
                    name="business_number"
                    class="validate[required,minSize[3],maxSize[30]]"
                    value="<?php echo cleanOutput($version->getContext()->get(ContentContextEntries::DYNAMIC_FIELDS_STORED_VALUES)['businessNumber'] ?? null); ?>"
                    placeholder="e.g. LP003139">
            <?php } ?>
		</div>
        <div class="modal-b__btns clearfix js-buttons-container">
            <button class="btn btn-success pull-right" type="submit">
                <span class="ep-icon ep-icon_ok"></span> Save
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        var saveField = function (form, isDialog) {
            var url = form.attr('action');
            var data = form.serializeArray();
            var onRequestSuccess = function (response) {
                systemMessages(response.message, response.mess_type);
                if(response.mess_type == 'success'){
                    if (isDialog) {
                        closeBootstrapDialog(form);
                    } else {
                        closeFancyboxPopup()
                    }

                    mix(globalThis, { saveVerificationCustomFields: null }, false);
                }
            };

            if (null === url) {
                return Promise.resolve();
            }
            showLoader(form);

            return postRequest(url, data)
                .then(onRequestSuccess)
                .catch(onRequestError)
                .finally(function() {
                    hideLoader(form);
                    form.find('button[type="submit"]').prop('disabled', false);
                });
        };

        var form = $('#verification--edit-additional-fields--form');
        var isDialog = Boolean(~~parseInt('<?php echo (int) $is_dialog ?? false; ?>', 10));

        mix(globalThis, { saveVerificationCustomFields: saveField.bind(null, form, isDialog) }, false);
    });
</script>
