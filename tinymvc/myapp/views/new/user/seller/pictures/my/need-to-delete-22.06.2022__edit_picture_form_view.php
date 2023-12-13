<div class="js-modal-flex wr-modal-flex inputs-40">
    <form class="modal-flex__form validateModal" action="<?php echo $action; ?>">
        <div class="modal-flex__content">
            <div class="form-group">
                <label class="input-label input-label--required mt-0"><?php echo translate('seller_pictures_dashboard_modal_field_categrory_label_text'); ?></label>

                <div id="js-add-picture-select-category" class="input-group">
                    <select class="form-control validate[required]" name="category">
                        <option value=""><?php echo translate('seller_pictures_dashboard_modal_field_category_placeholder'); ?></option>
                        <?php if (!empty($pictures_categories)){ ?>
                            <?php foreach ($pictures_categories as $pictures_category) { ?>
                                <option value="<?php echo $pictures_category['id_category'];?>" <?php echo selected($picture['id_category'], $pictures_category['id_category'])?>><?php echo $pictures_category['category_title'];?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>

                    <div class="input-group-btn">
                        <a
                            class="btn btn-dark call-function"
                            data-callback="showPictureNewCategory"
                            data-title="<?php echo translate("seller_pictures_categories_dashboard_add_category_modal_title", null, true); ?>"
                            title="<?php echo translate("seller_pictures_categories_dashboard_add_category_modal_title", null, true); ?>"
                            href="#"
                        >
                            <i class="ep-icon ep-icon_plus-circle"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div id="js-add-picture-new-category" class="form-group display-n">
                <input class="form-control validate[required,maxSize[50]]" type="text" name="new_category" placeholder=" <?php echo translate('seller_pictures_write_new_category_text', null, true); ?>">
                <div class="input-group-btn">
                    <a class="btn btn-dark call-function" data-callback="showPictureSelectCategory" href="#">
                        <i class="ep-icon ep-icon_remove-circle"></i>
                    </a>
                </div>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_pictures_dashboard_modal_field_image_title_label_text'); ?></label>
                <input type="text"
                    name="title"
                    class="validate[required,maxSize[200]]"
                    placeholder="<?php echo translate('seller_pictures_dashboard_modal_field_image_title_placeholder_public', null, true); ?>"
                    value="<?php if(isset($picture)) echo $picture['title_photo'];?>"/>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_pictures_dashboard_modal_field_image_description_label_text'); ?></label>
                <textarea name="text"
                    id="js-edit-picture-image-description"
                    class="validate[required,maxSize[2000]] textcounter"
                    data-max="2000"
                    placeholder="<?php echo translate('seller_pictures_dashboard_modal_field_image_description_placeholder_public', null, true); ?>"><?php if(isset($picture)) echo $picture['description_photo'];?></textarea>
            </div>

            <div class="form-group mt-10">
                <ul class="list-form-checked-info">
                    <li class="list-form-checked-info__item">
                        <label class="list-form-checked-info__label mb-0">
                            <input id="js-edit-picture-image-visibility" name="post_wall" type="checkbox">
                            <span class="list-form-checked-info__check-text"><?php echo translate('seller_pictures_dashboard_modal_field_visibility_label_text'); ?></span>
                        </label>
                    </li>
                </ul>
            </div>
            <input type="hidden" name="photo" value="<?php echo !empty($picture) ? $picture['id_photo'] : null; ?>"/>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit"><?php echo translate('general_modal_button_save_text'); ?></button>
            </div>
        </div>
    </form>
</div>

<script type="application/javascript">
(function() {
	"use strict";

	window.editPicturesModal = ({
		init: function (params) {
			editPicturesModal.self = this;

            editPicturesModal.$addPictureSelectCategory = $('#js-add-picture-select-category');
            editPicturesModal.$addPictureNewCategory = $('#js-add-picture-new-category');
            editPicturesModal.visibilityFlag = $('#js-edit-picture-image-visibility');
            editPicturesModal.imageDescription = $('#js-edit-picture-image-description');

			editPicturesModal.self.initPlug();
			editPicturesModal.self.initListiners();
		},
		initPlug: function(){
            if(editPicturesModal.visibilityFlag.length) {
                editPicturesModal.visibilityFlag.icheck({
                    checkboxClass: 'icheckbox icheckbox--20 icheckbox--blue',
                    increaseArea: '20%',
                });
            }

            if(editPicturesModal.imageDescription.length) {
                editPicturesModal.imageDescription.textcounter({
                    countDown: true,
                    countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
                    countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'}),
                });
            }
		},
		initListiners: function(){
			mix(
				window,
				{
					modalFormCallBack: editPicturesModal.self.onSaveContent,
                    showPictureNewCategory: editPicturesModal.self.onShowPictureNewCategory,
                    showPictureSelectCategory: editPicturesModal.self.onShowPictureSelectCategory,
				},
                false
			);
		},
        onShowPictureNewCategory: function($this){
            editPicturesModal.$addPictureSelectCategory.hide();
            editPicturesModal.$addPictureNewCategory.css({'display': 'flex'});
        },
        onShowPictureSelectCategory: function($this){
            editPicturesModal.$addPictureSelectCategory.show();
            editPicturesModal.$addPictureNewCategory.hide();
            editPicturesModal.$addPictureNewCategory.find('input[name="new_category"]').val("");
        },
		onSaveContent: function(formElement){
            var form = $(formElement);
            var wrapper = form.closest('.js-modal-flex');
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
                    callFunction('callbackEditSellerPictures', data);
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
		}

	});

}());

$(function() {
	editPicturesModal.init();
});
</script>
