<div class="wr-modal-b">
    <form method="post" action="<?php echo cleanOutput($url); ?>" id="edit-company-name--form" class="validateModal relative-b">
        <input type="hidden" name="type" value="<?php echo cleanOutput($type ?? null); ?>">
        <input type="hidden" name="company" value="<?php echo cleanOutput($company ?? null); ?>">
		<div class="modal-b__content pb-0 w-700">
			<div class="row">
                <div class="col-xs-12 initial-b">
                    <label class="modal-b__label">Legal Company Name</label>
                    <input
                        type="text"
                        name="legal_name"
                        class="validate[required,custom[companyTitle],minSize[3],maxSize[50]]"
                        value="<?php echo cleanOutput($legal_name ?? null); ?>"
                        placeholder="Example Company Ltd.">
                </div>

                <div class="col-xs-12">
                    <label class="modal-b__label">Company Name</label>
                    <input
                        type="text"
                        name="display_name"
                        class="validate[required,custom[companyTitle],minSize[3],maxSize[50]]"
                        value="<?php echo cleanOutput($display_name ?? null); ?>"
                        placeholder="Example Company">
                </div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <button class="btn btn-success pull-right" type="submit">
                <span class="ep-icon ep-icon_ok"></span> Save
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        var form = $('#edit-company-name--form');
        var isDialog = Boolean(~~parseInt('<?php echo (int) $isDialog ?? false; ?>', 10));
        var saveNames = function (form, isDialog) {
            var url = form.attr('action');
            var data = form.serializeArray();
            var onRequestSuccess = function (response) {
                systemMessages(response.message, response.mess_type);
                if(response.mess_type == 'success'){
                    $(globalThis).trigger('company:edit-name', [response]);
                    if (isDialog) {
                        closeBootstrapDialog(form);
                    } else {
                        closeFancyboxPopup()
                    }
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

        mix(globalThis, { modalFormCallBack: saveNames.bind(null, form, isDialog) }, false);
    });
</script>
