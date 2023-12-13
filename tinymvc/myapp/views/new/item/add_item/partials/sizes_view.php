
<div class="form-group">
    <label class="input-label <?php echo form_validation_label($validation, 'min_sale_quantity', 'required'); ?>">Units per Order</label>
    <div class="half-input half-input--mw-305">
        <div class="btn-group w-100pr">
            <input
                <?php echo addQaUniqueIdentifier("items-my-add__min-quantity")?>
                id="js-add-item-min-quantity"
                class="flex--1 mnw-1 validate[<?php echo form_validation_rules($validation, 'min_sale_quantity', 'required'); ?>,<?php echo form_validation_rules($validation, 'min_sale_quantity', 'min'); ?>,custom[positive_integer], max[1000000000]]"
                type="number"
                step="1"
                min="0"
                name="min_quantity"
                value="<?php echo cleanOutput($item['min_sale_q'] ?? null) ?: null; ?>"
                data-validate-class="validate[<?php echo form_validation_rules($validation, 'min_sale_quantity', 'required'); ?>,<?php echo form_validation_rules($validation, 'min_sale_quantity', 'min'); ?>,custom[positive_integer], max[1000000000]]"
                placeholder="Minimum"/>
            <span class="mnw-30 fs-17 tac lh-40">&minus;</span>
            <input
                <?php echo addQaUniqueIdentifier("items-my-add__max-quantity")?>
                id="js-add-item-max-quantity"
                class="flex--1 mnw-1 validate[<?php echo form_validation_rules($validation, 'max_sale_quantity', 'required'); ?>,<?php echo form_validation_rules($validation, 'max_sale_quantity', 'min'); ?>,custom[positive_integer], max[1000000000]]"
                type="number"
                step="1"
                min="0"
                name="max_quantity"
                value="<?php echo cleanOutput($item['max_sale_q'] ?? null) ?: null; ?>"
                data-validate-class="validate[<?php echo form_validation_rules($validation, 'max_sale_quantity', 'required'); ?>,<?php echo form_validation_rules($validation, 'max_sale_quantity', 'min'); ?>,custom[positive_integer], max[1000000000]]"
                placeholder="Maximum"/>
        </div>
    </div>
</div>

<div class="form-group">
    <label class="input-label <?php echo form_validation_label($validation, 'unit_type', 'required'); ?>">Classification of Each Unit</label>
    <select
        <?php echo addQaUniqueIdentifier("items-my-add__select-classification")?>
        class="half-input half-input--mw-305 validate[<?php echo form_validation_rules($validation, 'unit_type', 'required'); ?>]"
        name="unit_type"
    >
        <?php if (isset($item['unit_type'])) { ?>
            <?php $unit_type_name = 'Piece'; ?>
            <?php foreach ($u_types as $type) { ?>
                <?php if ($type['id'] == $item['unit_type']) { $unit_type_name = $type['unit_name']; } ?>
                <option value="<?php echo $type['id']; ?>" <?php echo selected($type['id'], $item['unit_type']); ?>><?php echo $type['unit_name']; ?></option>
            <?php } ?>
        <?php } else { ?>
            <?php foreach ($u_types as $type) { ?>
                <option value="<?php echo $type['id']; ?>" <?php echo selected($type['id'], 62); ?>><?php echo $type['unit_name']; ?></option>
            <?php } ?>
        <?php } ?>
    </select>
</div>

<div class="form-group">
    <label class="input-label <?php echo form_validation_label($validation, 'weight', 'required'); ?>">
        Real Weight in Kg for 1 Unit
    </label>

    <div class="input-info-right">
        <input
            <?php echo addQaUniqueIdentifier("items-my-add__item-weight")?>
            id="js-add-item-item-weight"
            class="half-input half-input--mw-305 validate[<?php echo form_validation_rules($validation, 'weight', 'required'); ?>,<?php echo form_validation_rules($validation, 'weight', 'min'); ?>,<?php echo form_validation_rules($validation, 'weight', 'max'); ?>"
            type="number"
            min="0"
            step="0.001"
            name="weight"
            value="<?php echo compareFloatNumbers($item['weight'] ?? 0, 0, '>', 0.001) ? cleanOutput($item['weight']) : null; ?>"
            placeholder="Weight per unit"/>
        <div class="input-info-right__txt">
            <span class="show-mn-767">For the correct calculation of the real weight use:</span>
            <a
                <?php echo addQaUniqueIdentifier("items-my-add__weight-calculator")?>
                class="call-function"
                data-callback="openModalAddItemWeightCalculator"
                data-href="<?php echo __SITE_URL;?>items/popup_forms/weight_calculator"
                title="Volumetric Weight Calculator"
                data-title="Volumetric Weight Calculator"
                href="#"
            >
                Weight Calculator
            </a>
        </div>
    </div>
</div>

<div class="form-group">
    <div class="container-fluid-modal">
        <label class="input-label <?php echo form_validation_label($validation, 'size', 'required'); ?>">Size, cm (LxWxH)</label>
		<div class="add-info-row">
			<div class="add-info-row__col pb-15-md">
                <input
                    <?php echo addQaUniqueIdentifier("items-my-add__item-length")?>
					class="validate[<?php echo form_validation_rules($validation, 'size', 'required'); ?>,<?php echo form_validation_rules($validation, 'size', 'min'); ?>,<?php echo form_validation_rules($validation, 'size', 'max'); ?>"
					type="number"
					step="0.01"
					min="0.01"
					name="item_length"
					size="4"
					maxlength="7"
                    value="<?php echo compareFloatNumbers($item['item_length'] ?? 0, 0, '>') ? cleanOutput($item['item_length']) : null; ?>"
					placeholder="Length">
			</div>
			<div class="add-info-row__col pb-15-md">
                <input
                    <?php echo addQaUniqueIdentifier("items-my-add__item-width")?>
					class="validate[<?php echo form_validation_rules($validation, 'size', 'required'); ?>,<?php echo form_validation_rules($validation, 'size', 'min'); ?>,<?php echo form_validation_rules($validation, 'size', 'max'); ?>"
					type="number"
					step="0.01"
					min="0.01"
					name="item_width"
					size="4"
					maxlength="7"
                    value="<?php echo compareFloatNumbers($item['item_width'] ?? 0, 0, '>') ? cleanOutput($item['item_width']) : null; ?>"
					placeholder="Width">
			</div>
			<div class="add-info-row__col pb-15-md">
                <input
                    <?php echo addQaUniqueIdentifier("items-my-add__item-height")?>
					class="validate[<?php echo form_validation_rules($validation, 'size', 'required'); ?>,<?php echo form_validation_rules($validation, 'size', 'min'); ?>,<?php echo form_validation_rules($validation, 'size', 'max'); ?>"
					type="number"
					step="0.01"
					name="item_height"
					min="0.01"
					size="4"
					maxlength="7"
                    value="<?php echo compareFloatNumbers($item['item_height'] ?? 0, 0, '>') ? cleanOutput($item['item_height']) : null; ?>"
					placeholder="Height">
			</div>
		</div>
	</div>
</div>

<script>
$(function(){
    var $addItemQuantity = $('#js-add-item-quantity');
    var $addItemMinQuantity = $('#js-add-item-min-quantity');
    var $addItemMaxQuantity = $('#js-add-item-max-quantity');
    var $outOfStockQuantity = $('#js-add-item-out-of-stock');
    var addItemQuantityVal = parseInt($addItemQuantity.val());
    var addItemMinQuantityVal = parseInt($addItemMinQuantity.val());
    var addItemMaxQuantityVal = parseInt($addItemMaxQuantity.val());
    var templateValidateQantity = '<?php echo form_validation_rules($validation, 'quantity', 'required'); ?>';
    var templateValidateMinQantity = '<?php echo form_validation_rules($validation, 'min_sale_quantity', 'min'); ?>';
    var templateValidateMinOutOfStock = '<?php echo form_validation_rules($validation, 'out_of_stock_quantity', 'min'); ?>';

	$addItemQuantity.on('change', function (){
        var $this = $(this);
        var thisVal = $this.val();
        var outOfStockTemplate = 'validate[' + templateValidateMinOutOfStock + ', custom[positive_integer], max[' + thisVal + ']]';

        if($outOfStockQuantity.hasClass('validengine-border')){
            $outOfStockQuantity.removeClass('validengine-border').prev('.formError').remove();
        }

        $outOfStockQuantity
            .removeClass($outOfStockQuantity.data('validate-class'))
            .addClass(outOfStockTemplate)
            .data('validate-class', outOfStockTemplate);
	});

    $addItemMaxQuantity.on('change', function (){
        var $this = $(this);
        var thisVal = $this.val();
        var templateValidate = 'validate[' + templateValidateQantity + ', ' + templateValidateMinQantity + ', custom[positive_integer], max[' + thisVal + ']]';

        $addItemMinQuantity
            .removeClass($addItemMinQuantity.data('validate-class'))
            .addClass(templateValidate)
            .data('validate-class', templateValidate);

        setTimeout(function(){
            $addItemMinQuantity.validationEngine("validate");
        }, 200);
	});

    $addItemMinQuantity.on('focus', function (){
        var $this = $(this);
        var error = $this.prev('.formError');
        if (error.length && !error.hasClass("hide")) {
            error.css({"margin-top": -56}).addClass('show');
        }
	});

    if(addItemQuantityVal > 0){
        var outOfStockTemplate = 'validate[' + templateValidateMinOutOfStock + ', custom[positive_integer], max[' + addItemQuantityVal + ']]';

        $outOfStockQuantity
            .removeClass($outOfStockQuantity.data('validate-class'))
            .addClass(outOfStockTemplate)
            .data('validate-class', outOfStockTemplate);
    }
});

var openModalAddItemWeightCalculator = function ($this) {
	open_modal_dialog({
        title: $this.data('title'),
        isAjax: true,
        content: $this.data('href'),
        validate: true,
        onShownCallback: function() {
            $('.js-calculate-btn').attr('atas', 'items-my-add__weight-calculator__calculate-btn');
            $('.js-calculator-close-btn').attr('atas', 'items-my-add__weight-calculator__close-btn');
        },
        buttons: [
            {
                label: 'Calculate',
                cssClass: 'btn-primary mnw-130 js-calculate-btn',
                action: function(){
                    calculate_mortgage();
                }
            },
            {
                label: 'Close',
                cssClass: 'btn-light mnw-130 js-calculator-close-btn',
                action: function(dialogRef){
                    dialogRef.close();
                }
            }
        ]
    });
}
</script>
