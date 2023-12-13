<div class="wr-modal-b">
    <form method="post" action="<?php echo $action; ?>" id="payment-method-translation--form" class="validateModal relative-b">
        <input type="hidden" name="id" id="payment-method-translation--form-input--method" value="<?php echo $method['id']; ?>">
		<div class="modal-b__content pb-0 w-900">
			<div class="row">
                <div class="col-xs-12 initial-b">
                    <label class="modal-b__label">Language</label>
                    <select class="form-control validate[required]" name="language" id="payment-method-translation--form-input--language">
                        <option selected disabled>Select language</option>
                        <?php if(!empty($languages)){ ?>
                            <?php foreach($languages as $lang_id => $lang_name){ ?>
                                <option value="<?php echo $lang_id; ?>" <?php echo in_array($lang_id, $translations) ? 'disabled' : ''; ?>>
                                    <?php echo $lang_name; ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-xs-12">
                    <label class="modal-b__label">Method</label>
                    <div class="form-control mb-15"><?php echo cleanOutput($method['method']); ?></div>
                    <input class="form-control validate[required,maxSize[255]]" type="text" name="method">
                </div>
                <div class="col-xs-12">
                    <label class="modal-b__label">Instructions</label>
                    <div class="col-xs-12 pl-0 pr-0 mb-15">
                        <textarea class="form-control h-250" id="payment-method-translation--form-input--instructions-original"><?php echo $method['instructions']; ?></textarea>
                    </div>
                    <div class="col-xs-12 pl-0 pr-0">
                        <textarea class="form-control h-250 validate[required]" id="payment-method-translation--form-input--instructions" name="instructions"></textarea>
                    </div>
                </div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <button class="btn btn-success pull-right" type="submit" id="payment-method-translation--form-action--submit">
                <span class="ep-icon ep-icon_ok"></span> Save
            </button>
        </div>
    </form>
</div>
<script type="text/javascript">
	var modalFormCallBack = function (formNode, dataGrid){
        var form = $(formNode);
        var url = form.attr('action');
        var data = form.serializeArray();
        var onRequestSuccess = function (response) {
            systemMessages(response.message, 'message-' + response.mess_type);
            if(response.mess_type == 'success'){
                closeFancyBox();
                if(dataGrid) {
                    $(dataGrid).DataTable().draw(false);
                }
            }
        };

        form.find('button[type=submit]').addClass('disabled');
        $.post(url, data, null, 'json').done(onRequestSuccess).fail(onRequestError).always(function() {
            form.find('button[type=submit]').removeClass('disabled');
        });
    };

    $(document).ready(function() {
        tinymce.remove('#payment-method-translation--form-input--instructions');
        tinymce.remove('#payment-method-translation--form-input--instructions-original');
        tinymce.init({
			selector: '#payment-method-translation--form-input--instructions-original',
            readonly: true,
            menubar: false,
			statusbar: false,
			height: 250,
			dialog_type: "modal",
			toolbar: false,
			resize: false
		});
        tinymce.init({
            selector:'#payment-method-translation--form-input--instructions',
            plugins: "autolink lists link textcolor code table",
            toolbar: "code bold italic underline forecolor backcolor | numlist bullist | table",
            resize: false,
            menubar: false,
            statusbar : false,
        });
    });
</script>
