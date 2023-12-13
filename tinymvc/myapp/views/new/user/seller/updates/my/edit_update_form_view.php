<div class="js-wr-modal wr-modal-flex inputs-40">
    <form
        class="modal-flex__form validateModal"
        data-callback="sellerUpdatesEditUpdateFormCallBack"
        action="<?php echo $action; ?>"
    >
        <div class="modal-flex__content">
            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_updates_dashboard_modal_field_description_label_text'); ?></label>
                <textarea name="text"
                    data-max="250"
                    id="js-edit-document-text"
                    class="validate[required,maxSize[250]]"
                    placeholder="<?php echo translate('seller_updates_dashboard_modal_field_description_placeholder', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('popup__seller-updates-my__edit-update-form_description-textarea'); ?>
                >
                    <?php echo cleanInput($update['text_update']);?>
                </textarea>
            </div>

            <div class="form-group mt-10">
                <ul class="list-form-checked-info">
                    <li class="list-form-checked-info__item">
                        <label class="custom-checkbox" <?php echo addQaUniqueIdentifier('popup__seller-updates-my__edit-update-form_post-wall-checkbox'); ?>>
                            <input name="post_wall" type="checkbox">
                            <span class="custom-checkbox__text"><?php echo translate('general_dashboard_modal_field_wall_flag_label_text'); ?></span>
                        </label>
                    </li>
                </ul>
            </div>

            <input type="hidden" name="update" value ="<?php echo $update['id_update']; ?>">
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">
                    <?php echo translate('general_modal_button_save_text'); ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script type="application/javascript">
    $(function() {
        $.fn.setValHookType = function (type) {
            this.each(function () {
                this.type = type;
            });

            return this;
        };

        var onSaveContent = function(formElement) {
            var form = $(formElement);
            var wrapper = form.closest('.js-wr-modal');
            var submitButton = form.find('button[type=submit]');
            var formData = form.serializeArray();
            var url = form.attr('action');
            var sendRequest = function (url, data) {
                return $.post(url, data, null, 'json');
            };
            var beforeSend = function() {
                showLoader(wrapper);
                submitButton.addClass('disabled');
            };
            var onRequestEnd = function() {
                hideLoader(wrapper);
                submitButton.removeClass('disabled');
            };
            var onRequestSuccess = function(data){
                hideLoader(wrapper);
                systemMessages(data.message, data.mess_type);
                if(data.mess_type === 'success'){
                    closeFancyBox();
                    callFunction('callbackEditUpdate', data);
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };
        var initializeEditor = function(editor) {
            initNewTinymce(editor, {validate: 'validate[required,maxSize[250]]', valHook: 'editor'});
        };

        var descriptionField = $('#js-edit-document-text');
        var editorOptions = {
			target: descriptionField.get(0),
			height : 140,
			resize: false,
			menubar: false,
			statusbar : true,
			plugins: ["lists charactercount contextmenu paste"],
			toolbar: "undo redo | bold italic underline | numlist bullist |",
            contextmenu: "undo redo | bold italic underline | numlist bullist",
			dialog_type : "modal",
            paste_filter_drop: true,
            valid_elements: 'p,span,strong,em,b,i,u,ol,ul,li,br',
            paste_word_valid_elements: 'p,span,strong,em,b,i,u,ol,ul,li,br',
            paste_enable_default_filters: true,
            paste_webkit_styles: 'none',
            paste_webkit_styles: 'text-decoration',
            paste_data_images: false,
            paste_retain_style_properties: 'text-decoration',
            init_instance_callback: initializeEditor,
		};

        if(descriptionField.length) {
            tinymce.remove("#" + descriptionField.attr('id'));
            tinymce.init(editorOptions);
        }

        $.valHooks.editor = {
            get: function (el) {
                return tinymce.get(descriptionField.attr('id')).getContent({format : 'text'}) || "";
            }
        };

        window.sellerUpdatesEditUpdateFormCallBack = onSaveContent;
    });
</script>
