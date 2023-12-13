<div class="container-fluid-modal">
    <?php
        $activeSpecificsPrice = 'none';

        if (!empty($itemVariants['variants'])) {
            $activeSpecificsPrice = 'variants';
        } elseif (isset($item['price']) && $item['price'] > 0) {
            $activeSpecificsPrice = 'price';
        }
    ?>

    <div
        id="js-specifics-price-wrapper"
        class="specifics-price-wrapper<?php echo $activeSpecificsPrice !== 'none' ? " display-n" : ""; ?>"
    >
        <label class="input-label input-label--required">Price specifics</label>

        <div class="info-alert-b">
			<i class="ep-icon ep-icon_info-stroke"></i>
			<div class="ep-tinymce-text">
				<ol class="pt-0">
					<li>If your product is not variable and is fixed, like in the description, you can set the price and discount by clicking the "<strong>Add Price</strong>" button.</li>
					<li>If your product comes in multiple variations, please click on the "<strong>Add Variations</strong>" button to create a list of the different types and their options. After, you can set the price for each option separately.</li>
				</ol>
			</div>
		</div>

        <div class="specifics-price-wrapper__actions">
            <button
                class="btn btn-primary call-action"
                data-js-action="price-variation-add-item-module:toggle-price-specifics"
                data-type="price"
                type="button"
                <?php echo addQaUniqueIdentifier("items-my-add__price-tab-select")?>
            >Add Price</button>
            <button
                class="btn btn-primary call-action"
                data-js-action="price-variation-add-item-module:toggle-price-specifics"
                data-type="variation"
                type="button"
                <?php echo addQaUniqueIdentifier("items-my-add__variations-tab-select")?>
            >Add Variations</button>
        </div>
    </div>

    <?php
        views()->display("new/item/add_item/partials/add_price_view", ["activeSpecificsPrice" => $activeSpecificsPrice]);
        views()->display("new/item/add_item/partials/add_variations_view", ["activeSpecificsPrice" => $activeSpecificsPrice]);
    ?>
</div>

<?php
    echo dispatchDynamicFragment(
        "add-item:price-and-variation",
        [
            [
                'maxProperties'         => $maxProperties['elements'],
                'maxOptions'            => $maxProperties['options'],
                'maxOptionCharacters'   => $maxProperties['optionCharacters'],
                'itemVariants'          => !empty($itemVariants['variants']) ? json_encode($itemVariants) : '{}',
                'title'                 => cleanOutput($item['title']),
                'warning'               => translate('system_message_manage_item_final_price_lower_initial_price'),
                'atasRemoveBtn'         => addQaUniqueIdentifier("items-my-add__var-remove-btn"),
                'atasSelectVariant'     => addQaUniqueIdentifier("items-my-add__select-var-combination"),
                'atasRemoveVariant'     => addQaUniqueIdentifier("items-my-add__var-combination-remove-btn"),
            ]
        ],
        true
    );
?>
