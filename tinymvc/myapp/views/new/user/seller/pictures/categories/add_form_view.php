<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" action="<?php echo $action; ?>">
		<div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12 mb-15">
                        <label class="input-label input-label--required">
                            <?php echo translate('seller_pictures_categories_dashboard_modal_field_name_label_text'); ?>
                        </label>
			            <input type="text"
                            <?php echo addQaUniqueIdentifier('page__seller-pictures-categories__form_category-input'); ?>
                            name="title"
                            class="validate[required,maxSize[50]]"
                            placeholder="<?php echo translate('seller_pictures_categories_dashboard_modal_field_name_placeholder', null, true); ?>"/>
                    </div>
                </div>
            </div>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <?php if($add_picture) { ?>
                    <a href="<?php echo $add_picture_url; ?>"
                        class="btn btn-dark fancybox.ajax fancyboxValidateModalDT"
                        data-title="<?php echo translate("seller_pictures_dashboard_add_picture_button_title", null, true); ?>"
                        title="<?php echo translate("seller_pictures_dashboard_add_picture_modal_title", null, true); ?>">
                        <?php echo translate('seller_pictures_categories_dashboard_modal_button_add_picture_text'); ?>
                    </a>
                <?php } ?>
                <?php if($edit_picture) { ?>
                    <a href="<?php echo $edit_picture_url; ?>"
                        class="btn btn-dark fancybox.ajax fancyboxValidateModalDT"
                        data-title="<?php echo translate("seller_pictures_dt_button_edit_picture_modal_title", null, true); ?>"
                        title="<?php echo translate("seller_pictures_dt_button_edit_picture_title", null, true); ?>">
                        <?php echo translate('seller_pictures_categories_dashboard_modal_button_edit_picture_text'); ?>
                    </a>
                <?php } ?>
            </div>

            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" <?php echo addQaUniqueIdentifier('popup__seller-pictures-categories__form_save-btn'); ?> type="submit"><?php echo translate('general_modal_button_save_text'); ?></button>
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
        var hasReturnButtons = Boolean(~~'<?php echo (int) ($add_picture || $edit_picture); ?>');

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
                callFunction('callbackAddPicturesCategory', response);
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
