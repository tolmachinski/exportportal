<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        id="js-product-request-send-form"
        class="modal-flex__form validateModal"
        data-callback="productRequestsPopupsSendRequestFormCallBack"
        data-js-action="request-products:save"
    >
		<div class="modal-flex__content">
			<label class="input-label input-label--info input-label--required">
                <span class="input-label__text">
                    <?php echo translate('product_requests_product_name_label', null, true); ?>
                </span>
                <a class="info-dialog ep-icon ep-icon_info"
                    data-message="<?php echo translate('product_requests_product_name_info_message', null, true); ?>"
                    data-title="<?php echo translate('product_requests_product_name_label', null, true); ?>"
                    title="<?php echo translate('product_requests_product_name_label', null, true); ?>"
                    href="#">
                </a>
            </label>
            <input
                type="text"
                name="title"
                class="validate[required,minSize[2],maxSize[300]]"
                placeholder="<?php echo translate('product_requests_enter_product_name_placeholder', null, true); ?>">

            <label class="input-label input-label--info input-label--required">
                <span class="input-label__text">
                    <?php echo translate('product_requests_categories_label', null, true); ?>
                </span>
                <a class="info-dialog ep-icon ep-icon_info"
                    data-message="<?php echo translate('product_requests_categories_info_message', null, true); ?>"
                    data-title="<?php echo translate('product_requests_categories_info_title', null, true); ?>"
                    title="<?php echo translate('product_requests_categories_info_title', null, true); ?>"
                    href="#">
                </a>
            </label>
            <select name="category" class="validate[required]">
                <option disabled selected>
                    <?php echo translate('product_requests_choose_category_option_text', null, true); ?>
                </option>
                <?php foreach ($industries as $industry) { ?>
                    <option value="<?php echo cleanOutput($industry['category_id']); ?>">
                        <?php echo cleanOutput($industry['name']); ?>
                    </option>
                <?php } ?>
            </select>

            <?php if (!logged_in()) { ?>
                <label class="input-label input-label--required">
                    <?php echo translate('product_requests_user_name_label', null, true); ?>
                </label>
                <input
                    type="text"
                    name="name"
                    class="validate[required,custom[validUserName],minSize[2],maxSize[200]]"
                    placeholder="<?php echo translate('product_requests_user_name_placeholder', null, true); ?>">

                <label class="input-label input-label--required">
                    <?php echo translate('product_requests_user_email_label', null, true); ?>
                </label>
                <input
                    type="text"
                    name="email"
                    class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[254]]"
                    placeholder="<?php echo translate('product_requests_user_email_placeholder', null, true); ?>">
            <?php } ?>

            <div class="product-requests__info">
                <div class="product-requests__info-block">
                    <p>
                        <span class="txt-bold">
                            <?php echo translate('product_requests_additional_information_label', null, true); ?>
                        </span>
                        <span class="txt-gray">
                            (<?php echo translate('product_requests_optional_text', null, true); ?>)
                        </span>
                        <a class="info-dialog ep-icon ep-icon_info"
                            data-message="<?php echo translate('product_requests_additional_information_info_message', null, true); ?>"
                            data-title="<?php echo translate('product_requests_additional_information_label', null, true); ?>"
                            title="<?php echo translate('product_requests_additional_information_label', null, true); ?>"
                            href="#">
                        </a>
                    </p>

                    <span class="link call-function cur-pointer js-info-toggle-handler"
                        data-inactive-icon="ep-icon_minus-stroke"
                        data-active-icon="ep-icon_plus-stroke"
                        data-active="0"
                        <?php echo addQaUniqueIdentifier('modal__request-product-toggle-additional-info')?>>
                        <i class="ep-icon ep-icon_plus-stroke js-info-toggle-icon"></i>
                    </span>
                </div>

                <div class="js-info-toggle-block display-n pb-20">
                    <label class="input-label">
                        <?php echo translate('product_requests_amount_label', null, true); ?>
                    </label>
                    <input
                        type="text"
                        name="quantity"
                        class="validate[custom[positive_integer],min[1],max[99999999999]]"
                        placeholder="<?php echo translate('product_requests_number_of_products', null, true); ?>">

                    <label class="input-label">
                        <?php echo translate('product_requests_price_from_label', null, true); ?>
                    </label>
                    <input
                        id="js-product-request-send-formfields-start-price"
                        type="text"
                        name="start_price"
                        class="validate[custom[positive_number],min[0.01],max[999999999999.99],custom[maxField[#js-product-request-send-formfields-final-price]]]"
                        placeholder="<?php echo translate('product_requests_price_from_placeholder', null, true); ?>">

                    <label class="input-label">
                        <?php echo translate('product_requests_price_to_label', null, true); ?>
                    </label>
                    <input
                        id="js-product-request-send-formfields-final-price"
                        type="text"
                        name="final_price"
                        class="validate[custom[positive_number],min[0.01],max[999999999999.99],custom[minField[#js-product-request-send-formfields-start-price]]]"
                        placeholder="<?php echo translate('product_requests_price_to_placeholder', null, true); ?>">

                    <label class="input-label">
                        <span class="input-label__text">
                            <?php echo translate('product_requests_country_from_label', null, true); ?>
                        </span>
                        <a class="info-dialog ep-icon ep-icon_info"
                            data-message="<?php echo translate('product_requests_country_from_info_message', null, true); ?>"
                            data-title="<?php echo translate('product_requests_country_from_label', null, true); ?>"
                            title="<?php echo translate('product_requests_country_from_label', null, true); ?>"
                            href="#">
                        </a>
                    </label>
                    <select name="departure_country">
                        <option disabled selected>
                            <?php echo translate('product_requests_choose_a_country', null, true); ?>
                        </option>
                        <?php foreach ($countries as $country) { ?>
                            <option value="<?php echo cleanOutput($country['id']); ?>">
                                <?php echo cleanOutput($country['country']); ?>
                            </option>
                        <?php } ?>
                    </select>

                    <label class="input-label">
                        <span class="input-label__text">
                            <?php echo translate('product_requests_country_to_label', null, true); ?>
                        </span>
                        <a class="info-dialog ep-icon ep-icon_info"
                            data-message="<?php echo translate('product_requests_country_to_info_message', null, true); ?>"
                            data-title="<?php echo translate('product_requests_country_to_label', null, true); ?>"
                            title="<?php echo translate('product_requests_country_to_label', null, true); ?>"
                            href="#">
                        </a>
                    </label>
                    <select name="destination_country">
                        <option disabled selected>
                            <?php echo translate('product_requests_choose_a_country', null, true); ?>
                        </option>
                        <?php foreach ($countries as $country) { ?>
                            <option value="<?php echo cleanOutput($country['id']); ?>">
                                <?php echo cleanOutput($country['country']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <label class="input-label">
                <?php echo translate('product_requests_details_label', null, true); ?>
            </label>
            <textarea name="details"
                class="textcounter js-details"
                data-max="500"
                placeholder="<?php echo translate('product_requests_describe_the_product', null, true); ?>"></textarea>
        </div>

		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">
                    <?php echo translate('product_requests_send_request', null, true); ?>
                </button>
            </div>
		</div>
	</form>
</div>

<?php echo dispatchDynamicFragmentInCompatMode(
    'product-requests:boot',
    asset('public/plug/js/product_requests/popups/send.js', 'legacy'),
    sprintf(
        "function () {
            if (!('SendProductRequestModule' in window)) {
                if (__debug_mode) {
                    console.error(new SyntaxError(\"'SendProductRequestModule' must be defined\"))
                }

                return;
            }

            var productRequestsHandler = SendProductRequestModule.entrypoint();
            mix(globalThis, { productRequestsPopupsSendRequestFormCallBack: function () { productRequestsHandler.save() } }, false);
        }",
    ),
    null,
    true
); ?>
