<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        id="add-estimate-response--form"
        class="modal-flex__form validateModal"
        data-callback="estimateRequestsFormCallBack"
        method="post"
        action="<?php echo $action; ?>"
    >
        <input type="hidden" name="estimate" value="<?php echo $estimate['id']; ?>">
		<div class="modal-flex__content">
            <label class="input-label">Delivery days</label>
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-6">
                        <input type="number" name="delivery_from"
                            min="1"
                            max="1000"
                            step="1"
                            class="validate[required,integer,min[1],max[1000]]" placeholder="From" data-title="Created from">
                    </div>
                    <div class="col-6">
                        <input type="number" name="delivery_to"
                            min="1"
                            max="1000"
                            step="1"
                            class="validate[required,integer,min[1],max[1000]]" placeholder="To" data-title="Created to">
                    </div>
                </div>
            </div>
            <label class="input-label">Price</label>
            <input type="number"
                    min="1"
                    max="9999999999"
                    step="0.01"
                    name="price"
                    id="add-estimate-response--form-field--price"
                    class="validate[required,number,min[1],max[9999999999]]"
                    placeholder="Enter the price">

            <label class="input-label">Comment</label>
            <textarea name="comment"
                data-max="500"
                class="validate[maxLength[500]]"
                id="add-estimate-response--form-field--translation"
                placeholder="Enter the comment"></textarea>
		</div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-success" type="submit" id="add-estimate-response--form-action--submit">
                    Save
                </button>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(function() {
        var onSave = function (formNode, dataGrid){
            var form = $(formNode);
            var saveButton = form.find('button[type=submit]');
            var url = form.attr('action');
            var data = form.serializeArray();
            var dataTable = dataGrid.parents('table').first();
            var onBeforeSave = function() {
                saveButton.prop('disabled', true);
                showLoader(form);
            };
            var onAfterSave = function() {
                saveButton.prop('disabled', false);
                hideLoader(form);
            };
            var onRequestSuccess = function (response) {
                systemMessages(response.message, 'message-' + response.mess_type);
                if('success' === response.mess_type){
                    closeFancyBox();
                    if(dataTable) {
                        $(dataTable).DataTable().draw(false);
                    }
                }
            };

            onBeforeSave();
            $.post(url, data, null, 'json').done(onRequestSuccess).fail(onRequestError).always(function() {
                onAfterSave();
            });
        };

        var comment = $('#add-estimate-response--form-field--translation');
        var counterOptions = {
            countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'}),
        };

        if(comment.length) {
            comment.textcounter(counterOptions);
        }

        window.estimateRequestsFormCallBack = onSave;
    });
</script>
