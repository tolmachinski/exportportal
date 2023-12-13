<div class="wr-modal-flex inputs-40">
	<form
        id="create-sample--form"
        class="modal-flex__form validateModal"
        data-callback="sampleOrdersPopupsCreateSampleOrderFormCallBack"
        data-js-action="sample-order:create"
    >
        <input type="hidden" name="room" value="<?php echo cleanOutput($room ?? null); ?>">
        <input type="hidden" name="recipient" value="<?php echo cleanOutput($recipient ?? null); ?>">
		<div class="modal-flex__content">
            <label class="input-label mt-0 input-label--required">Select item</label>
            <div class="input-search-products">
                <input type="text" class="js-search-product" placeholder="Search item or insert item's link">

                <div class="input-search-products__results js-products-list">
                </div>
            </div>

            <table class="main-data-table mt-15 js-selected-products input-search-products-selected">
                <thead>
                    <tr>
                        <th class="">Item title</th>
                        <th class="w-120">Quantity</th>
                        <th class="w-130">Price, USD</th>
                        <th class="w-65"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="js-no-product">
                        <td class="input-search-products-selected__empty tac" colspan="4" data-title="Items">No data available</td>
                    </tr>
                </tbody>
            </table>

            <label class="input-label input-label--info input-label--required">
                <span class="input-label__text">What do you want to include in your Sample Order?</span>
                <a class="info-dialog ep-icon ep-icon_info"
                    data-content="#js-info-dialog-want-included-order-sample"
                    data-title="What do you want to include in your Sample Order?"
                    href="#">
                </a>
            </label>
            <div class="display-n" id="js-info-dialog-want-included-order-sample">
                <p>
                    Describe your sample order: <br>
                    Is there any order information the buyer should know?
                </p>
            </div>

            <div class="form-group">
			    <textarea class="validate[required] js-sample-description" name="description" data-max="5000" placeholder="Describe your sample order"></textarea>
            </div>
        </div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Create Sample Order</button>
            </div>
		</div>
	</form>
</div>

<script type="text/template" id="create-sample--form-template--product">
    <tr class="js-product" style="display: none;" data-item="{{index}}">
        <td data-title="Item title">
            <input type="hidden" name="item[{{index}}][id]" value="{{id}}">
            <a class="input-search-products-selected__link flex-card" href="{{url}}" target="_blank">
                <div class="input-search-products-selected__img image-card3 flex-card__fixed">
                    <span class="link">
                        <img class="image" src="{{image}}" alt="{{title}}">
                    </span>
                </div>
                <div class="input-search-products-selected__name flex-card__float">
                    <div class="grid-text">
                        <div class="grid-text__item">
                            {{title}}
                        </div>
                    </div>
                </div>
            </a>
        </td>
        <td data-title="Quantity">
            <div class="form-group">
                <input type="text" placeholder="Ex. 5" name="item[{{index}}][quantity]" class="validate[required,custom[positive_integer],min[1],max[99999999999]]">
            </div>
        </td>
        <td data-title="Price, USD">
            <div class="form-group">
                <input type="text" placeholder="Ex. 5" name="item[{{index}}][price]" class="validate[required,custom[positive_number],min[0.01],max[99999999999.99]]">
            </div>
        </td>
        <td>
            <a class="btn btn-light js-delete-product" title="Remove product" data-message="Do you really want to delete this item?">
                <i class="ep-icon ep-icon_trash-stroke"></i>
            </a>
        </td>
    </tr>
</script>

<?php if (!isset($webpackData)) { ?>
    <script><?php echo getPublicScriptContent('plug/lodash-custom-4-17-5/lodash.custom.min.js', true); ?></script>
<?php }?>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        "sample-order:create",
        asset('public/plug/js/sample_orders/popups/create.js', 'legacy'),
        sprintf(
            "function () {
                var sampleRequestHandler = new CreateSampleOrderPopupModule(
                    Object.assign(
                        {
                            productTemplate: $('#create-sample--form-template--product').text() || null,
                        },
                        %s
                    )
                );
                mix(window, { sampleOrdersPopupsCreateSampleOrderFormCallBack: function () { sampleRequestHandler.save() } }, false);
            }",
            json_encode(
                $params = [
                    'saveUrl'       => getUrlForGroup('sample_orders/ajax_operations/create_order'),
                    'searchUrl'     => getUrlForGroup('sample_orders/ajax_operations/find_products'),
                    'isDialogPopup' => $isDialog ?? false,
                    'selectors'     => [
                        'form'               => '#create-sample--form',
                        'searchField'        => '#create-sample--form input.js-search-product',
                        'productsList'       => '#create-sample--form .js-products-list',
                        'selectedProducts'   => '#create-sample--form .js-selected-products',
                        'sampleDescription'  => '#create-sample--form textarea.js-sample-description',
                        'deleteProduct'      => '.js-delete-product',
                        'noProductsRow'      => '.js-no-product',
                        'productRow'         => '.js-product',
                    ],
                ]
            ),
        ),
        [$params],
        true
    );
?>
