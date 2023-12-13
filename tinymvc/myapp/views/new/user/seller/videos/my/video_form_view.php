<div class="js-wr-modal wr-modal-flex inputs-40">
    <form
        class="modal-flex__form validateModal"
        data-callback="sellerVideosVideoFormCallBack"
        action="<?php echo $action; ?>"
    >
        <div class="modal-flex__content">

            <!-- Categories -->

            <?php views("new/user/seller/categories_field_view", array("categories" => $videos_categories, "main" => $video)); ?>

            <!-- Title -->

            <div class="form-group">
                <label class="input-label input-label--required">
                    <?php echo translate('seller_pictures_dashboard_modal_field_image_title_label_text'); ?>
                </label>
                <input
                    type="text"
                    name="title"
                    class="validate[required,maxSize[200]]"
                    value="<?php echo cleanOutput($video['title_video']); ?>"
                    placeholder="<?php echo translate('seller_videos_dashboard_modal_field_title_label_placeholder', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('popup__seller-videos__add-video-form_title-input'); ?>
                />
            </div>

            <!-- Link -->

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_videos_dashboard_modal_field_link_label_text'); ?></label>
                <div class="input-group">
                    <input
                        type="text"
                        maxlength="200"
                        name="link"
                        value="<?php echo cleanOutput($video['url_video']); ?>"
                        class="form-control validate[required,maxSize[200],custom[url]]"
                        placeholder="<?php echo translate('seller_videos_dashboard_modal_field_link_label_placeholder', null, true); ?>"
                        <?php echo addQaUniqueIdentifier('popup__seller-videos__add-video-form_url-input'); ?>
                    >
                    <div class="input-group-append">
                        <div class="input-info">
                            <div class="input-info__icon">
                                <i class="ep-icon ep-icon_info-stroke"></i>
                            </div>
                            <div class="input-info__txt"><?php echo translate('seller_videos_dashboard_modal_field_link_help_text'); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->

            <div class="form-group" <?php echo addQaUniqueIdentifier('popup__seller-videos__add-video-form_description-group'); ?>>
                <label class="input-label input-label--required"><?php echo translate('seller_videos_dashboard_modal_field_description_label_text'); ?></label>
                <textarea
                    name="text"
                    id="js-video-description"
                    class="validate[required,maxSize[2000]]"
                    data-max="2000"
                    placeholder="<?php echo translate('seller_videos_dashboard_modal_field_description_placeholder', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('popup__seller-videos__add-video-form_description-textarea'); ?>
                ><?php echo cleanOutput($video['description_video']); ?></textarea>
            </div>

            <!-- Post on wall -->

            <div class="mt-10">
                <ul class="list-form-checked-info">
                    <li class="list-form-checked-info__item">
                        <label class="custom-checkbox" <?php echo addQaUniqueIdentifier('popup__seller-videos__add-video-form_post-on-wall-checkbox'); ?>>
                            <input id="js-video-post-wall" name="post_wall" type="checkbox">
                            <span class="custom-checkbox__text"><?php echo translate('general_modal_field_post_on_wall_label_text'); ?></span>
                        </label>
                    </li>
                </ul>
            </div>

            <?php if(!empty($video)) { ?>
                <input type="hidden" name="video" value="<?php echo $video['id_video']; ?>"/>
            <?php } ?>
        </div>

        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button
                    id="edit-video--formaction--submit"
                    class="btn btn-primary"
                    type="submit"
                    <?php echo addQaUniqueIdentifier('popup__seller-videos__add-video-form_save-btn'); ?>
                >
                    <?php echo translate('general_modal_button_save_text'); ?>
            </button>
            </div>
        </div>
    </form>
</div>

<script type="application/javascript">

    $(function() {
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
                    callFunction('callbackEditSellerVideos', data);
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };

        var description = $('#js-video-description');
        var counterOptions = {
            countDown: true,
            countDownTextBefore: translate_js({plug: 'textcounter', text: 'count_down_text_before'}),
            countDownTextAfter: translate_js({plug: 'textcounter', text: 'count_down_text_after'})
        };
        if(description.length) {
            description.textcounter(counterOptions);
        }

        window.sellerVideosVideoFormCallBack = onSaveContent;
    });
</script>
