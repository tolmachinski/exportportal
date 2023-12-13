<div class="wr-modal-b">
    <form method="post" action="<?php echo $action; ?>" id="resource-block--form" class="validateModal relative-b">
        <input type="hidden" name="resource" value="<?php echo $resource['id']; ?>">
        <input type="hidden" name="author" value="<?php echo $resource['author']; ?>">
		<div class="modal-b__content w-750">
			<div class="row">
                <div class="col-xs-12 initial-b">
                    <div class="form-group">
                        <label class="modal-b__label">Type of abuse</label>
						<select class="validate[required]" name="abuse" id="resource-block--formfield--abuse">
							<option value="" selected disabled>Select type of abuse</option>
							<?php foreach($themes as $theme) { ?>
								<option value="<?php echo $theme['id_theme'];?>"
                                    data-message="<?php echo cleanOutput(arrayGet(record_i18n($theme['i18n'], 'message', $resource['author_lang_code']), 'value', $theme['message'])); ?>"
                                    data-standard>
                                    <?php echo arrayGet(record_i18n($theme['i18n'], 'theme', $resource['author_lang_code']), 'value', $theme['theme']); ?>
                                </option>
							<?php } ?>
                            <option value="other">Other</option>
						</select>
                        <div class="form-group">
                            <input class="validate[required,maxSize[500]] mt-15" type="text" name="abuse_other" id="resource-block--formfield--other-abuse" placeholder="Enter the abuse" style="display: none;"/>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="form-group">
                    <label class="modal-b__label">Message</label>
						<textarea class="validate[required,maxSize[1000]] h-150 textcounter" id="resource-block--formfield--message" data-max="1000" name="content" placeholder="Message"></textarea>
                    </div>
                </div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <button class="btn btn-success pull-right" type="submit" id="resource-block--form-action--submit">
                <span class="ep-icon ep-icon_ok"></span> Block
            </button>
        </div>
    </form>
</div>
<script type="application/javascript">
    $(function() {
        var abusesList = $("#resource-block--formfield--abuse");
        var abuseInput = $("#resource-block--formfield--other-abuse");
        var abuseMessage = $("#resource-block--formfield--message");
        var sendRequest = function (url, data) {
            return $.post(url, data, null, 'json');
        };
        var onChooseAbuse = function(event) {
            var self = $(this);
            var selected = self.find(':selected');
            if('other' === self.val()) {
                abuseInput.show();
            } else {
                abuseInput.hide();
            }

            if(selected.filter("[data-standard]").length) {
                abuseMessage.val(selected.data('message') || '');
            }
        };
        var onContentSend = function(formElement) {
            var form = $(formElement);
            var wrapper = form.closest('.wr-modal-b');
            var submitButton = form.find('button[type=submit]');
            var formData = form.serializeArray() || [];
            var url = form.attr('action') || null;
            var beforeSend = function() {
                showFormLoader(wrapper);
                submitButton.addClass('disabled');
            };
            var onRequestEnd = function() {
                hideFormLoader(wrapper);
                submitButton.removeClass('disabled');
            };
            var onRequestSuccess = function(data){
                hideFormLoader(wrapper);
                systemMessages(data.message, data.mess_type);
                if(data.mess_type === 'success'){
                    closeFancyBox();
                    callFunction('onResourceBlock', data);
                }
            };

            if(null === url) {
                return;
            }

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };

        abusesList.on('change', onChooseAbuse);

        window.modalFormCallBack = onContentSend
    });
</script>