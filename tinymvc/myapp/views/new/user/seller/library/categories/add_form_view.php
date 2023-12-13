<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        action="<?php echo $action; ?>"
        <?php echo addQaUniqueIdentifier('seller-library-categories__add-category_form'); ?>
    >
		<div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12 mb-15">
                        <label class="input-label input-label--required">
                            <?php echo translate('seller_library_categories_dashboard_modal_field_name_label_text'); ?>
                        </label>
			            <input type="text"
                            name="title"
                            class="validate[required,maxSize[50]]"
                            placeholder="<?php echo translate('seller_library_categories_dashboard_modal_field_name_placeholder', null, true); ?>"
                            <?php echo addQaUniqueIdentifier('seller-library-categories__add-category_form-input'); ?>
                        />
                    </div>
                </div>
            </div>
		</div>
		<div class="modal-flex__btns">
            <?php if($edit_library || $add_library){ ?>
                <div class="modal-flex__btns-right">
                    <a
                        class="btn btn-dark call-function"
                        data-callback="bootstrapDialogCloseAll"
                        href="#"
                        <?php echo addQaUniqueIdentifier('seller-library-categories__add-category_form-close-btn'); ?>
                    >
                        <?php if($edit_library) {
                                echo translate('seller_library_categories_dashboard_modal_button_edit_document_text');
                            }else{
                                echo translate('seller_library_categories_dashboard_modal_button_add_document_text');
                            }
                        ?>
                    </a>
                </div>
            <?php }?>

            <div class="modal-flex__btns-right">
                <button
                    class="btn btn-primary"
                    type="submit"
                    <?php echo addQaUniqueIdentifier('seller-library-categories__add-category_form-save-btn'); ?>
                >
                    <?php echo translate('general_modal_button_save_text'); ?>
                </button>
            </div>
		</div>
	</form>
</div>

<script type="application/javascript">
    var modalFormCallBack = function(formElement) {
        var form = $(formElement);
        var data = form.serializeArray();
        var url = form.attr('action') || null;
        var wrapper = form.closest('.js-modal-flex');
        var button = form.find('button[type=submit]');
        var hasReturnButtons = Boolean(~~'<?php echo (int) ($add_library || $edit_library); ?>');

        var onRequestStart = function() {
            showLoader(wrapper);
            button.addClass('disabled');
        };
        var onRequestEnd = function() {
            hideLoader(wrapper);
            button.removeClass('disabled');
        }
        var onRequestSuccess = function(response) {
            hideLoader(wrapper);
            systemMessages(response.message, response.mess_type);
            if(response.mess_type == 'success'){
                callFunction('callbackAddLibraryCategory', response);
                if(!hasReturnButtons) {
                    closeFancyBox();
                } else {
                    form.get(0).reset();
                }
            }
        };
        if(null !== url) {
            onRequestStart();
            $.post(url, data, null, 'json')
                .done(onRequestSuccess)
                .fail(onRequestError)
                .always(onRequestEnd);
        }
    }
</script>
