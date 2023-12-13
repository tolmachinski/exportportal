<div class="js-wr-modal wr-modal-flex inputs-40">
    <form class="modal-flex__form validateModal" action="<?php echo $action; ?>">
        <div class="modal-flex__content">


            <div class="form-group">

                <?php views("new/user/seller/categories_field_view", array("categories" => $videos_categories)); ?>

                <!-- <label class="input-label input-label--required mt-0"><?php echo translate('seller_videos_dashboard_modal_field_category_label_text'); ?></label> -->

                <!--
                    <select class="form-control validate[required]" name="videos_category">
                        <option value="" selected><?php echo translate('seller_videos_dashboard_modal_field_category_placeholder'); ?></option>

                        <?php if (!empty($videos_categories)) { ?>
                            <?php foreach ($videos_categories as $video_category) { ?>
                                <option value="<?php echo $video_category['id_category']; ?>">
                                    <?php echo $video_category['category_title']; ?>
                                </option>
                            <?php } ?>
                        <?php } ?>

                    </select>

                    <div class="input-group-btn">
                        <a
                            class="btn btn-dark call-function"
                            data-callback="onShowVideoSelectCategory"
                            data-title="<?php echo translate("seller_pictures_categories_dashboard_add_category_modal_title", null, true); ?>"
                            title="<?php echo translate("seller_pictures_categories_dashboard_add_category_modal_title", null, true); ?>"
                            href="#"
                        >
                            <i class="ep-icon ep-icon_plus-circle "></i>
                        </a>
                    </div>
                -->
            </div>
        <!--
            <div id="js-add-video-new-category" class="form-group display-n">
                <input class="form-control validate[required,maxSize[50]]" type="text" name="new_category" placeholder="<?php echo translate("seller_pictures_write_new_category_text", null, true); ?>">
                <div class="input-group-btn">
                    <a class="btn btn-dark call-function" data-callback="onShowVideoNewCategory" href="#">
                        <i class="ep-icon ep-icon_remove-circle "></i>
                    </a>
                </div>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_videos_dashboard_modal_field_category_label_text'); ?></label>
                <div class="input-group initial-b_i">
                    <select class="form-control validate[required]" name="videos_category">
                        <option value="" selected><?php echo translate('seller_videos_dashboard_modal_field_category_placeholder'); ?></option>
                        <?php if (!empty($videos_categories)) { ?>
                            <?php foreach ($videos_categories as $video_category) { ?>
                                <option value="<?php echo $video_category['id_category']; ?>">
                                    <?php echo $video_category['category_title']; ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                    <div class="input-group-btn">
                        <button
                            class="btn btn-dark js-validate-modal"
                            data-href="<?php echo $category_url;?>"
                            data-validate="1"
                            data-close-click="none"
                            data-title="<?php echo translate("seller_videos_categories_dashboard_add_category_modal_title", null, true); ?>"
                            title="<?php echo translate("seller_videos_categories_dashboard_add_category_button_title", null, true); ?>">
                            <span class="d-none d-sm-inline">
                                <?php echo translate('seller_videos_categories_dashboard_add_category_button_text'); ?>
                            </span>
                            <i class="ep-icon ep-icon_plus d-inline d-sm-none"></i>
                        </button>
                    </div>
                </div>
            </div>
        -->

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_videos_dashboard_modal_field_title_label_text'); ?></label>
                <input type="text"
                    maxlength="200"
                    name="title"
                    class="validate[required,maxSize[200]]"
                    placeholder="<?php echo translate('seller_videos_dashboard_modal_field_title_label_placeholder', null, true); ?>">
            </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_videos_dashboard_modal_field_link_label_text'); ?></label>
                <div class="input-group">
                    <input type="text"
                        maxlength="200"
                        name="link"
                        class="form-control validate[required,maxSize[200],custom[url]]"
                        placeholder="<?php echo translate('seller_videos_dashboard_modal_field_link_label_placeholder', null, true); ?>">
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

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_videos_dashboard_modal_field_description_label_text'); ?></label>
                <textarea name="text"
                    id="js-add-video-description"
                    class="validate[required,maxSize[2000]]"
                    data-max="2000"
                    placeholder="<?php echo translate('seller_videos_dashboard_modal_field_description_placeholder', null, true); ?>"></textarea>
            </div>

            <div class="form-group mt-10">
                <ul class="list-form-checked-info">
                    <li class="list-form-checked-info__item">
                        <label class="list-form-checked-info__label mb-0">
                            <input id="js-add-video-post-wall" name="post_wall" type="checkbox">
                            <span class="list-form-checked-info__check-text"><?php echo translate('general_modal_field_post_on_wall_label_text'); ?></span>
                        </label>
                    </li>
                </ul>
            </div>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit"><?php echo translate('general_modal_button_save_text'); ?></button>
            </div>
        </div>
    </form>
</div>

<script type="text/template" id="js-video-categories-select">
    <select class="form-control validate[required]" name="videos_category">
        <option value="" selected><?php echo translate('seller_videos_dashboard_modal_field_category_placeholder'); ?></option>

        <?php if (!empty($videos_categories)) { ?>
            <?php foreach ($videos_categories as $video_category) { ?>
                <option value="<?php echo $video_category['id_category']; ?>">
                    <?php echo $video_category['category_title']; ?>
                </option>
            <?php } ?>
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
    // var addNewCategory = function() {
    //     $("#js-add-video-category").html($('#js-video-category-input').html());
    // }

    // var showSelectCategories = function(){
    //     $("#js-add-video-category").html($('#js-video-categories-select').html());
    // };

    $(function() {

        // showSelectCategories();

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
                    callFunction('callbackAddSellerVideos', data);
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };

        var postWall = $('#js-add-video-post-wall');
        var description = $('#js-add-video-description');
        var postWallOptions = {
            checkboxClass: 'icheckbox icheckbox--20 icheckbox--blue',
            increaseArea: '20%'
        };
        var counterOptions = {
            countDown: true,
            countDownTextBefore: translate_js({plug: 'textcounter', text: 'count_down_text_before'}),
            countDownTextAfter: translate_js({plug: 'textcounter', text: 'count_down_text_after'})
        };

        if(postWall.length) {
            postWall.icheck(postWallOptions);
        }
        if(description.length) {
            description.textcounter(counterOptions);
        }

        window.modalFormCallBack = onSaveContent;
    });
</script>
