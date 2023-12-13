<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        class="modal-flex__form validateModal"
        data-callback="usersReviewsAddReviewFormCallBack"
    >
        <div class="modal-flex__content feedback-popup">
            <div class="ep-tinymce-text">
                <?php echo translate('seller_ep_reviews_add_review_form_title'); ?>
            </div>

            <label class="input-label"><?php echo translate('seller_ep_reviews_add_review_form_what_was_ordered'); ?></label>
            <?php if (!empty($ordered_item_for_review)) { ?>
                <div class="feedback-popup__bought-select">
                    <div class="wr-select-buy-b">
                        <a class="select-buy-dropdown" href="<?php echo __SITE_URL . 'items/ordered/' . strForURL($ordered_item_for_review['title'] . ' ' . $ordered_item_for_review['id_ordered_item']); ?>" target="_blank">
                            <div class="select-buy-dropdown__img image-card3">
                                <span class="link">
                                    <img class="image" src="<?php echo getDisplayImageLink(['{ID}' => $ordered_item_for_review['id_snapshot'], '{FILE_NAME}' => $ordered_item_for_review['main_image']], 'items.snapshot', ['thumb_size' => 1]); ?>" alt="<?php echo $ordered_item_for_review['title']; ?>" />
                                </span>
                            </div>
                            <span class="select-buy-dropdown__title"><?php echo $ordered_item_for_review['title']; ?></span>
                        </a>
                    </div>
                </div>

                <input type="hidden" name="item" value="<?php echo $ordered_item_for_review['id_item'] . '_' . $ordered_item_for_review['id_ordered_item']; ?>">
            <?php } else { ?>
                <div class="feedback-popup__bought-select">
                    <div class="wr-select-buy-b">
                        <select name="item" class="select-buy-b">
                            <option data-image="" data-item="javascript:void(0);" data-default-option><?php echo translate('seller_ep_reviews_add_review_form_select_ordered_item_placeholder'); ?></option>
                            <?php foreach ($user_ordered_items_for_reviews as $item) { ?>
                                <optgroup label="Order: <?php echo $item['order']; ?>">
                                    <?php foreach ($item['items'] as $option) { ?>
                                        <option value="<?php echo $option['id_item'] . '_' . $option['id_ordered_item']; ?>" data-item="<?php echo __SITE_URL . 'items/ordered/' . strForURL($option['title'] . ' ' . $option['id_ordered_item']); ?>" data-image="<?php echo getDisplayImageLink(['{ID}' => $option['id_snapshot'], '{FILE_NAME}' => $option['main_image']], 'items.snapshot', ['thumb_size' => 1]); ?>" data-title="<?php echo $option['title']; ?>" data-price="$<?php echo $option['price_ordered']; ?>"></option>
                                    <?php } ?>
                                </optgroup>
                            <?php } ?>
                        </select>
                    </div>
                    <a class="feedback-popup__view-item" id="select-link-item" href="javascript:void(0);" target="_blank"><?php echo translate('seller_ep_reviews_add_review_form_product_detail_btn'); ?></a>
                </div>
            <?php } ?>

            <label class="input-label input-label--required"><?php echo translate('seller_ep_reviews_add_review_form_click_to_rate_label'); ?></label>
            <div class="feedback-popup__rating pb-0">
                <input id="rating-review" class="rating-tooltip" data-filled="ep-icon ep-icon_star txt-orange fs-25" data-empty="ep-icon ep-icon_star-empty txt-orange fs-25" type="hidden" name="rev_raiting" value="0">
            </div>
            <div class="input-group">
                <label class="input-label input-label--required"><?php echo translate('edit_review_form_title_label'); ?></label>
                <input class="validate[required,maxSize[200]]" type="text" name="title" maxlength="200" value="" placeholder="<?php echo translate('edit_review_form_title_placeholder', null, true); ?>" <?php echo addQaUniqueIdentifier("popup__add-review__form_title-input")?>/>
            </div>
            <div class="input-group">
                <label class="input-label input-label--required"><?php echo translate('edit_review_form_message_label'); ?></label>
                <textarea class="validate[required,maxSize[500]] textcounter-reviews_description" name="description" data-max="500" placeholder="<?php echo translate('edit_review_form_message_placeholder', null, true); ?>" <?php echo addQaUniqueIdentifier("popup__add-review__form_comment-textarea")?>></textarea>
            </div>
            <?php views('new/users_reviews/image_uploader_view'); ?>

        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit"><?php echo translate('edit_review_form_submit_btn'); ?></button>
            </div>
        </div>
    </form>
</div>
<script>

    $(document).ready(function() {
        $('.textcounter-reviews_description').textcounter({
            countDown: true,
            countDownTextBefore: translate_js({
                plug: 'textcounter',
                text: 'count_down_text_before'
            }),
            countDownTextAfter: translate_js({
                plug: 'textcounter',
                text: 'count_down_text_after'
            })
        });

        var selectBuyParams = {
            width: '100%',
            height: 40,
            templateResult: format,
            templateSelection: format,
            escapeMarkup: function(m) {
                return m;
            }
        }

        if ($(window).width() < 768) {
            selectBuyParams.minimumResultsForSearch = -1;
        }

        $(".select-buy-b").select2(selectBuyParams)
            .on('select2:select', function(e) {
                var $selectLinkItem = document.querySelector("#select-link-item");
                $selectLinkItem.setAttribute('href', $(".select-buy-b option:selected").data('item'));
            });

        $('.rating-tooltip').rating({
            extendSymbol: function(rate) {
                $(this).attr('title', ratingBootstrapStatus(rate));
            }
        });

        $('#rating-review').on('change', function() {
            var $this = $(this);
            ratingBootstrap($this);
        });
    });

    function format(state) {
        originalOption = state.element;
        if (!state.id) {
            return state.text;
        }

        if ($(originalOption).data('image') != '') {
            return "<div class='select-buy-dropdown'><img class='select-buy-dropdown__img' src='" + $(originalOption).data('image') + "'/><span class='select-buy-dropdown__title'>" + $(originalOption).data('title') + "</span><span class='select-buy-dropdown__price'>" + $(originalOption).data('price') + "</span></div>";
        } else {
            return state.text;
        }
    }

    function usersReviewsAddReviewFormCallBack(form) {
        var $form = $(form);
        var $wrform = $form.closest('.js-modal-flex');
        var fdata = $form.serialize();

        $.ajax({
            type: 'POST',
            url: 'reviews/ajax_review_operation/add_review/<?php echo $page_type; ?>',
            data: fdata,
            dataType: 'JSON',
            beforeSend: function() {
                showLoader($wrform);
                $form.find('button[type=submit]').addClass('disabled');
            },
            success: function(resp) {
                hideLoader($wrform);
                systemMessages(resp.message, resp.mess_type);

                if (resp.mess_type == 'success') {
                    callFunction('addReviewCallback', resp);
                    closeFancyBox();
                } else {
                    $form.find('button[type=submit]').removeClass('disabled');
                }
            }
        });
    }
</script>
