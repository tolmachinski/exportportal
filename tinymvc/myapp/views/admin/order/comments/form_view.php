<div class="wr-modal-b">
    <form action="<?php echo $formAction;?>" class="modal-b__form validateModal">
		<div class="modal-b__content pb-0 w-700">
			<div class="row">
                <div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Text</label>
                    <textarea class="form-control validate[required, maxSize[1000]]" name="message"></textarea>
                </div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <input type="hidden" name="order" value="<?php echo $orderId;?>">
            <button class="btn btn-success pull-right" type="submit">
                <span class="ep-icon ep-icon_ok"></span> Save
            </button>
        </div>
    </form>
</div>

<script>
    var modalFormCallBack = function (formNode, dataGrid) {
        var form = $(formNode);
        var url = form.attr('action');
        var data = form.serializeArray();

        var onRequestSuccess = function (response) {
            systemMessages(response.message, 'message-' + response.mess_type);
            if (response.mess_type == 'success'){
                closeFancyBox();

                $(globalThis).trigger('order:success-add-comment');
            }
        };

        showLoader(form);
        $.post(url, data, null, 'json').done(onRequestSuccess).fail(onRequestError).always(function() {
            hideLoader(form);
        });
    };
</script>

