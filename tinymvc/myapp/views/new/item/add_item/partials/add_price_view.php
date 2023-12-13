<?php
    $priceValidateRequired = form_validation_rules($validation, 'price', 'required');
    $priceValidateMin = form_validation_rules($validation, 'price', 'min');
    $priceValidateMax = form_validation_rules($validation, 'price', 'max');
    $priceValidate = "validate[{$priceValidateRequired},custom[positive_number],{$priceValidateMin},{$priceValidateMax}]";

    $quantityValidateRequired = form_validation_rules($validation, 'quantity', 'required');
    $quantityValidateMin = form_validation_rules($validation, 'quantity', 'min');
    $quantityValidateMax = form_validation_rules($validation, 'quantity', 'max');
    $quantityValidate = "validate[{$quantityValidateRequired},{$quantityValidateMin},{$quantityValidateMax},custom[positive_integer]]";

    $activePrice = $activeSpecificsPrice === 'price';
?>
<div
    id="js-add-price-wrapper"
    class="<?php echo $activePrice ? "active" : "display-n"; ?>"
>
    <label class="input-label txt-medium mb-0">
        Add Price
        <span
            class="button-link call-action"
            data-js-action="price-variation-add-item-module:toggle-price-specifics"
            data-type="back"
            <?php echo addQaUniqueIdentifier("items-my-add__price-tab-return")?>
        >Change</span>
    </label>

    <div class="add-info-row">
        <div class="add-info-row__col">
            <label class="input-label input-label--info <?php echo form_validation_label($validation, 'price', 'required'); ?>">
                <span class="input-label__text">Price, USD</span><a
                    class="info-dialog ep-icon ep-icon_info"
                    data-message="This is the intended price for sale without discount. In case a Discount Price will be added, the Price will be visually cut for customers."
                    data-title="What is: Price?"
                    title="What is: Price?"
                ></a>
            </label>
            <input
                <?php echo addQaUniqueIdentifier("items-my-add__price")?>
                id="js-add-item-price-in-dol"
                <?php echo $activePrice ? "class='{$priceValidate}'" : ""; ?>
                data-validate-class="<?php echo $priceValidate;?>"
                type="text"
                name="price_in_dol"
                value="<?php echo isset($item['price']) && $item['price'] > 0 && $activePrice ? $item['price'] : null; ?>"
                placeholder="e.g. 10.55">
        </div>
        <div class="add-info-row__col">
            <label class="input-label">
                <span class="input-label__text">Discount Price, USD</span>
                <a
                    class="info-dialog ep-icon ep-icon_info"
                    data-message="Price with a discount, if set it will represent the intended price for sale."
                    data-title="What is: Discount Price?"
                    title="What is: Discount Price?"
                ></a>
            </label>
            <input
                <?php echo addQaUniqueIdentifier("items-my-add__discount-price")?>
                id="js-add-item-final-price"
                class="validate[custom[positive_number],<?php echo form_validation_rules($validation, 'final_price', 'min'); ?>,<?php echo form_validation_rules($validation, 'final_price', 'max'); ?>]"
                type="text"
                name="final_price"
                value="<?php echo $activePrice && isset($item['final_price']) && $item['final_price'] > 0 && $item['final_price'] < $item['price'] ? $item['final_price'] : null; ?>"
                placeholder="e.g. 10.55">
        </div>
        <div class="add-info-row__col add-info-row__col--130">
            <label class="input-label">
                Discount
                <a
                    class="info-dialog ep-icon ep-icon_info"
                    data-message="The Discount is auto-calculated based on your Price and Discount Price (Discount Price * 100 / Price)"
                    data-title="What is: Discount?"
                    title="What is: Discount?"
                ></a>
            </label>
            <div class="lh-40-md-min tac-md-min" id="js-add-item-discount"><?php if (isset($item['discount']) && $activePrice) { echo cleanOutput($item['discount']); } else { echo '0'; } ?>%</div>
        </div>
    </div>

    <div class="form-group">
        <div class="add-info-row">
            <div class="add-info-row__col">
                <label class="input-label <?php echo form_validation_label($validation, 'quantity', 'required'); ?>">Total Quantity in Stock</label>
                <input
                    <?php echo addQaUniqueIdentifier("items-my-add__quantity")?>
                    id="js-add-item-quantity"
                    class="half-input half-input--mw-305 <?php echo $activePrice ? $quantityValidate : ""; ?>"
                    data-validate-class="<?php echo $quantityValidate;?>"
                    type="number"
                    step="1"
                    min="0"
                    name="quantity"
                    value="<?php echo $activePrice && isset($item['quantity']) && $item['quantity'] > 0 ? cleanOutput($item['quantity']) : null; ?>"
                    placeholder="Total quantity"/>
            </div>
            <div class="add-info-row__col">
                <label class="input-label">
                    Out of Stock
                    <a class="info-dialog ep-icon ep-icon_info"
                        data-content="#js-info-dialog__stock-quantity"
                        data-title="Out of Stock"
                        title="Out of Stock"
                        href="#">
                    </a>
                    <div class="display-n" id="js-info-dialog__stock-quantity">
                        <p>Notify me when the stock quantity will be less than this number</p>
                    </div>
                </label>
                <input
                    <?php echo addQaUniqueIdentifier("items-my-add__out-of-stock-quantity")?>
                    id="js-add-item-out-of-stock"
                    class="half-input half-input--mw-305 validate[<?php echo form_validation_rules($validation, 'out_of_stock_quantity', 'min'); ?>,custom[positive_integer]]"
                    type="number"
                    step="1"
                    min="1"
                    name="out_of_stock_quantity"
                    value="<?php echo $activePrice && isset($item['out_of_stock_quantity']) && $item['out_of_stock_quantity'] > 0 ? cleanOutput($item['out_of_stock_quantity']) : null; ?>"
                    data-validate-class="validate[<?php echo form_validation_rules($validation, 'out_of_stock_quantity', 'min'); ?>,custom[positive_integer]]"
                    placeholder="Out of stock"/>
            </div>
            <div class="add-info-row__col add-info-row__col--130"></div>
        </div>
    </div>
</div>
