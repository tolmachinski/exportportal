<div class="js-wr-modal wr-modal-flex inputs-40">
    <form class="modal-flex__form validateModal" action="<?php echo $action; ?>">
        <div class="modal-flex__content">
            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_library_dashboard_modal_field_category_label_text'); ?></label>

                <div id="js-add-video-category" class="input-group"></div>

                <!-- <div class="input-group">
                    <select name="library_category" class="form-control validate[required]">
                        <option value=""><?php echo translate('seller_library_dashboard_modal_field_category_placeholder'); ?></option>
                        <?php if(!empty($library_categories)){ ?>
                            <?php foreach($library_categories as $category){ ?>
                                <option value="<?php echo $category['id_category']; ?>" <?php echo selected($document['id_category'], $category['id_category']); ?>>
                                    <?php echo $category['category_title']; ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                    <div class="input-group-btn">
                        <button
                            class="btn btn-dark js-validate-modal"
                            data-href="<?php echo $category_url; ?>"
                            data-validate="1"
                            data-close-click="none"
                            data-title="<?php echo translate("seller_library_categories_dashboard_add_category_modal_title", null, true); ?>"
                            title="<?php echo translate("seller_library_categories_dashboard_add_category_modal_title", null, true); ?>">
                                <span class="d-none d-sm-inline">
                                <?php echo translate('seller_library_categories_dashboard_add_category_button_title'); ?>
                            </span>
                            <i class="ep-icon ep-icon_plus d-inline d-sm-none"></i>
                        </button>
                    </div>
                </div> -->
            </div>

            <div class="form-group">
                <label class="input-label input-label--required">
                    <?php echo translate('seller_library_dashboard_modal_field_document_title_label_text'); ?>
                </label>
                <input
                    type="text"
                    name="title"
                    class="validate[required,maxSize[50]]"
                    placeholder="<?php echo translate('seller_library_dashboard_modal_field_document_title_placeholder_text', null, true); ?>"
                    value="<?php if(isset($document)) echo $document['title_file'];?>"/>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required">
                    <?php echo translate('seller_library_dashboard_modal_field_document_access_type_label_text'); ?>
                </label>

                <ul class="list-form-checked-info">
                    <li class="list-form-checked-info__item pb-0">
                        <label class="list-form-checked-info__label">
                            <input class="js-edit-document-access-type" type="radio" name="file_type" value="private" <?php echo checked($document['type_file'], 'private'); ?>/>
                            <span class="list-form-checked-info__check-text">
                                <?php echo translate('seller_library_dashboard_modal_field_document_access_type_private_label_text'); ?>
                            </span>
                            <span class="txt-gray ml-5">(<?php echo translate('seller_library_dashboard_modal_field_document_access_type_private_help_text'); ?>)</span>
                        </label>
                    </li>
                    <li class="list-form-checked-info__item">
                        <label class="list-form-checked-info__label">
                            <input class="js-edit-document-access-type" type="radio" name="file_type" value="public" <?php echo checked($document['type_file'], 'public'); ?>/>
                            <span class="list-form-checked-info__check-text">
                                <?php echo translate('seller_library_dashboard_modal_field_document_access_type_public_label_text'); ?>
                            </span>
                            <span class="txt-gray ml-5">(<?php echo translate('seller_library_dashboard_modal_field_document_access_type_public_help_text'); ?>)</span>
                        </label>
                    </li>
                </ul>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required">
                    <?php echo translate('seller_library_dashboard_modal_field_document_description_label_text'); ?>
                </label>
                <textarea name="text"
                    id="js-edit-document-document-description"
                    class="validate[required,maxSize[250]]"
                    data-max="250"
                    placeholder="<?php echo translate('seller_library_dashboard_modal_field_document_description_placeholder_text', null, true); ?>"><?php if(isset($document)) echo $document['description_file'];?></textarea>
            </div>

            <div class="form-group mt-10">
                <ul class="list-form-checked-info">
                    <li class="list-form-checked-info__item">
                        <label class="list-form-checked-info__label mb-0">
                            <input id="js-edit-document-wall-post" name="post_wall" type="checkbox">
                            <span class="list-form-checked-info__check-text"><?php echo translate('seller_library_dashboard_modal_field_wall_label_text'); ?></span>
                        </label>
                    </li>
                </ul>
            </div>

            <input type="hidden" name="id" value="<?php echo $document["id_file"];?>"/>
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

<script type="text/template" id="js-video-categories-select">

    <select name="library_category" class="form-control validate[required]" id="js-add-document-category">
        <option value=""><?php echo translate('seller_library_dashboard_modal_field_category_placeholder'); ?></option>
        <?php foreach($library_categories as $category){ ?>
            <option value="<?php echo $category['id_category']; ?>" <?php echo selected($document['id_category'], $category['id_category']); ?>>
                <?php echo $category['category_title']; ?>
            </option>
        <?php } ?>
    </select>

    <div class="input-group-btn">
        <a
            class="btn btn-dark call-function"
            data-callback="addNewCategory"
            data-title="<?php echo translate("seller_pictures_categories_dashboard_add_category_modal_title", null, true); ?>"
            title="<?php echo translate("seller_pictures_categories_dashboard_add_category_modal_title", null, true); ?>"
            href="#"
        >
            <i class="ep-icon ep-icon_plus-circle "></i>
        </a>
    </div>

</script>

<script type="text/template" id="js-video-category-input">
    <input
        class="form-control validate[required,maxSize[50]]"
        type="text"
        name="new_category"
        placeholder="<?php echo translate("seller_pictures_write_new_category_text", null, true); ?>">

    <div class="input-group-btn">
        <a class="btn btn-dark call-function" data-callback="showSelectCategories" href="#">
            <i class="ep-icon ep-icon_remove-circle "></i>
        </a>
    </div>
</script>

<script type="application/javascript">

    var addNewCategory = function() {
        $("#js-add-video-category").html($('#js-video-category-input').html());
    }

    var showSelectCategories = function(){
        $("#js-add-video-category").html($('#js-video-categories-select').html());
    };

    $(function(){

        showSelectCategories();

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
                    callFunction('callbackEditLibraryDocument', data);
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };
        var changeWallPostAcessibility = function(event) {
            var self = $(this);
            var value = self.val() || null;
            if(null !== value) {
                if('public' === value) {
                    wallPostFlag.icheck('enable');
                } else {
                    wallPostFlag.icheck('disable');
                }
            }
        };

        var wallPostFlag = $('#js-edit-document-wall-post')
        var accessTypeRadio = $('.js-edit-document-access-type');
        var documentDescription = $('#js-edit-document-document-description');
        var accessTypeRadioOptions = {
            radioClass: 'iradiobox iradiobox--20 iradiobox--blue',
            increaseArea: '20%',
        };
        var wallFlagOptions = {
            checkboxClass: 'icheckbox icheckbox--20 icheckbox--blue',
            increaseArea: '20%'
        };
        var counterOptions = {
            countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'}),
        };

        if(accessTypeRadio.length) {
            accessTypeRadio
                .icheck(accessTypeRadioOptions)
                .on('ifChecked', changeWallPostAcessibility);
        }
        if(wallPostFlag.length) {
            wallPostFlag.icheck(wallFlagOptions);
        }
        if(documentDescription.length) {
            documentDescription.textcounter(counterOptions);
        }

        window.modalFormCallBack = onSaveContent;
    });
</script>
