<div class="wr-modal-flex inputs-40">
	<form
        id="create-sample--form"
        class="modal-flex__form validateModal"
        data-callback="sampleOrdersPopupCreateItemSampleOrderFormCallBack"
        data-js-action="sample-order:create"
    >
        <input type="hidden" name="item" value="<?php echo cleanOutput($item['id'] ?? null); ?>">

		<div class="modal-flex__content">
            <?php views()->display('new/item/modal_product_detail_view', array('item' => $item ?? null, 'photo' => $photos ?? array())); ?>

            <div class="container-fluid-modal pt-15">
                <div class="row">
                    <div class="col-12 col-lg-6">
                        <label class="input-label input-label--info input-label--required">
                            <span class="input-label__text">
                                <?php echo cleanOutput(sprintf('Quantity %s', !empty($item['unit_name']) ? "({$item['unit_name']})" : null)); ?>
                            </span><a class="info-dialog ep-icon ep-icon_info" data-content="#js-info-quantitiy-order-sample" data-title="Quantity?" href="#"></a>
                        </label>

                        <div class="display-n" id="js-info-quantitiy-order-sample">Quantity</div>

                        <input type="text"
                            class="validate[required,custom[positive_integer],min[1],max[99999999999]]"
                            name="quantity"
                            placeholder="Ex. 5">
                    </div>
                    <div class="col-12 col-lg-6">
                        <label class="input-label input-label--info input-label--required">
                            <span class="input-label__text">Price, USD</span>
                            <a class="info-dialog ep-icon ep-icon_info"
                                data-content="#js-info-price-order-sample"
                                data-title="Price, USD?" href="#">
                            </a>
                        </label>

                        <div class="display-n" id="js-info-price-order-sample">Price, USD</div>

                        <input class="validate[required,custom[positive_number],min[0.01],max[99999999999.99]]" type="text" name="price" placeholder="Ex. 5">
                    </div>
                </div>
            </div>
			<label class="input-label input-label--info input-label--required">
                <span class="input-label__text">What do you want to include in your Sample Order?</span>
                <a class="info-dialog ep-icon ep-icon_info"
                    data-content="#js-info-detail-order-sample"
                    data-title="What do you want to include in your Sample Order?" href="#">
                </a>
            </label>

            <div class="display-n" id="js-info-detail-order-sample">
                <p>
                    Describe your sample order: <br>
                    Is there any order information the buyer should know?
                </p>
            </div>

            <div class="form-group">
                <textarea name="description" class="validate[required] js-sample-description" data-max="5000" placeholder="Describe your sample order"></textarea>
            </div>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-left"></div>
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Create Sample Order</button>
            </div>
		</div>
	</form>
</div>

<?php if (!isset($webpackData)) { ?>
    <script><?php echo getPublicScriptContent('plug/lodash-custom-4-17-5/lodash.custom.min.js', true); ?></script>
<?php }?>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        "sample-order:create",
        asset('public/plug/js/sample_orders/popups/create.js', 'legacy'),
        sprintf(
            "function () {
                var sampleRequestHandler = new CreateSampleOrderPopupModule(%s);
                mix(window, { sampleOrdersPopupCreateItemSampleOrderFormCallBack: function () { sampleRequestHandler.save() } }, false);
            }",
            json_encode(
                $params = [
                    'saveUrl'       => getUrlForGroup('sample_orders/ajax_operations/create_order'),
                    'isDialogPopup' => $is_dialog ?? false,
                    'selectors'     => array(
                        'form'               => '#create-sample--form',
                        'sampleDescription'  => '#create-sample--form textarea.js-sample-description',
                    ),
                ]
            ),
        ),
        [$params],
        true
    );
?>
