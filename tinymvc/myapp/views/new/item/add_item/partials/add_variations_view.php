<div
    id="js-add-variations-wrapper"
    class="<?php echo $activeSpecificsPrice === 'variants' ? "active" : "display-n"; ?>"
>
    <label class="input-label txt-medium">
        Add Variations
        <span
            class="button-link call-action"
            data-js-action="price-variation-add-item-module:toggle-price-specifics"
            data-type="back"
            <?php echo addQaUniqueIdentifier("items-my-add__variations-tab-return")?>
        >Change</span>
    </label>

    <label class="input-label">
        Create variations for the items
        <span
            class="info-dialog ep-icon ep-icon_info"
            data-content="#js-info-dialog-item-variations"
            data-title="Add Item Variations"
        ></span>

        <div class="display-n" id="js-info-dialog-item-variations">
            <p>If your product comes in multiple variations, please create a list of variation types and their options.<br> The picture below shows you the way the added information will be displayed on the page. </p>
            <img src="<?php echo __IMG_URL; ?>public/img/products/info/product-variation.jpg" alt="Add Item Variations">
        </div>
    </label>

    <div class="info-alert-b">
        <i class="ep-icon ep-icon_info-stroke"></i>
        <div class="ep-tinymce-text">
            <strong>How to add item variations:</strong>

            <ol class="pb-5">
                <li>Add an available "Variation type" (Ex.: colors, sizes, forms);</li>
                <li>In "Variation options" write what kind of Color, Size, Form you have (Ex.: Blue, XL, or square form);</li>
                <li>Click "Add variation" button to make it public;</li>
                <li>A variation type cannot contain more than <?php echo $maxProperties['options'];?> options;</li>
                <li>One "Variation type" cannot be longer than <?php echo $maxProperties['propertyCharacters'];?> characters;</li>
                <li>One "Variation option" cannot be longer than <?php echo $maxProperties['optionCharacters'];?> characters.</li>
            </ol>
        </div>
    </div>

    <div>
        <div class="add-info-row">
            <div class="add-info-row__col">
                <label class="input-label">Variation type</label>
                <input
                    id="js-properties-title"
                    class="validate[maxSize[<?php echo $maxProperties['propertyCharacters'];?>],custom[onlyLetterNumberSp]]"
                    type="text"
                    placeholder="e.g. Color"
                    <?php echo addQaUniqueIdentifier("items-my-add__var-type")?>
                >
            </div>
            <div
                class="add-info-row__col"
                <?php echo addQaUniqueIdentifier("items-my-add__var-option")?>
            >
                <label class="input-label">Options</label>
                <input
                    id="js-properties-options"
                    class="w-100pr"
                >
                <div class="input-info-sub">Use ";" or press "Enter" to separate variations.</div>
            </div>
            <div class="add-info-row__col add-info-row__action-col add-info-row__col--130">
                <label class="input-label dn-md_i">&nbsp;</label>
                <button
                    class="btn btn-dark btn-block text-nowrap call-action"
                    data-js-action="price-variation-add-item-module:add-property"
                    type="button"
                    <?php echo addQaUniqueIdentifier("items-my-add__var-add-btn")?>
                >Add <span class="dn-md-min">variation</span></button>
            </div>
        </div>

        <div id="js-add-item-properties-wr" class="add-info-row-wr add-info-row-wr--mt15"></div>
    </div>

    <div
        id="js-add-item-variants"
        class="display-n"
    >
        <label class="input-label">
            Set a Price for Combination of Variations
            <span
                class="info-dialog ep-icon ep-icon_info"
                data-content="#js-info-dialog-price-for-variant"
                data-title="Set a Price for Combination of Variations"
            ></span>

            <div class="display-n" id="js-info-dialog-price-for-variant">
                <p>Based on the combination of options' variations you can add different prices.<br> The picture below shows you the way how the current price is displayed after choosing some specific combinations. </p>
                <img src="<?php echo __IMG_URL; ?>public/img/products/info/product-combination.jpg" alt="Set a Price for Combination of Variations">
            </div>
        </label>

        <div class="info-alert-b pr-20">
            <i class="ep-icon ep-icon_info-stroke"></i>
            <div class="ep-tinymce-text">
                <strong>How to set a price for Combination of Variations:</strong>

                <ol class="pb-5">
                    <li>Set per one combination of Variations (Ex.: choose the color and material type);</li>
                    <li>Set a price for combination;</li>
                    <li>Click the "Add combination" button;</li>
                    <li>Create more possible combinations and set the prices accordingly.</li>
                </ol>
            </div>
        </div>

        <label class="input-label">Create a combination</label>
        <div id="js-add-item-properties-select-wr" class="flex-w--w flex-jc--fs add-info-row"></div>

        <div class="add-info-row">
            <div class="add-info-row__col">
                <label class="input-label input-label--required">Image</label>
                <div
                    class="js-select-variant-images select-variant-images"
                    <?php echo addQaUniqueIdentifier("items-my-add__var-combination-image")?>
                >
                    <div class="select-variant-images__selected flex-card">
                        <div class="select-variant-images__selected-img flex-card__fixed image-card3">
                            <span class="link">
                                <img
                                    class="image js-add-item-change-main-photo"
                                    data-image="main"
                                    <?php if(!empty($photo_main)){?>
                                        src="<?php echo $photo_main['photo_url']; ?>"
                                    <?php }else{ ?>
                                        src="<?php echo __SITE_URL;?>public/img/no_image/group/main-image.svg"
                                    <?php } ?>
                                    alt="<?php echo cleanOutput($item['title']); ?>"
                                >
                            </span>
                        </div>
                        <div class="select-variant-images__selected-txt flex-card__float">Choose image</div>
                    </div>
                    <div class="select-variant-images__dropdown select-variant-images__dropdown--bottom">
                        <div class="select-variant-images__dropdown-inner">
                            <div
                                class="select-variant-images__option image-card3 active"
                                data-image="main"
                            >
                                <span class="link">
                                    <img
                                        class="image js-add-item-change-main-photo"
                                        <?php if(!empty($photo_main)){?>
                                            src="<?php echo $photo_main['photo_url']; ?>"
                                        <?php }else{ ?>
                                            src="<?php echo __SITE_URL; ?>public/img/no_image/group/main-image.svg"
                                        <?php } ?>
                                        alt="<?php echo cleanOutput($item['title']); ?>"
                                    >
                                </span>
                            </div>

                            <?php if(!empty($photos)){?>
                                <?php foreach ($photos as $photo_key => $photo_item) {?>
                                    <?php if ($photo_item['main_parent']) {continue;}?>
                                    <div
                                        class="select-variant-images__option image-card3"
                                        data-image="<?php echo $photo_item['photo_name']; ?>"
                                    >
                                        <span class="link">
                                            <img
                                                class="image"
                                                src="<?php echo $photo_item['photo_url']; ?>"
                                                alt="<?php echo cleanOutput($item['title']); ?>"
                                            >
                                        </span>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                        <button
                            <?php echo addQaUniqueIdentifier("items-my-add__var-combination-image-upload")?>
                            class="btn btn-light btn-block select-variant-images__btn-upload"
                            type="button"
                        >Upload other image</button>
                    </div>
                </div>
            </div>
            <div class="add-info-row__col">
                <label class="input-label input-label--required">Price, USD</label>

                <input
                    <?php echo addQaUniqueIdentifier("items-my-add__var-combination-price")?>
                    class="js-item-input-variant-prices validate[custom[positive_number],<?php echo form_validation_rules($validation, 'variant_price', 'min'); ?>,<?php echo form_validation_rules($validation, 'variant_price', 'max'); ?>] "
                    type="text"
                    min="0"
                    step="0.01"
                    placeholder="e.g. 10.55">
            </div>
            <div class="add-info-row__col add-info-row__col-empty add-info-row__action-col add-info-row__col--130"></div>
        </div>

        <div class="add-info-row pb-10-lg-min">
            <div class="add-info-row__col">
                <label class="input-label">
                    Discount Price, USD
                    <span
                        class="info-dialog ep-icon ep-icon_info"
                        data-message="Price with a discount, if set it will represent the intended price for sale."
                        data-title="What is: Discount Price?"
                        title="What is: Discount Price?"
                    ></span>
                </label>
                <input
                    <?php echo addQaUniqueIdentifier("items-my-add__var-combination-discount-price")?>
                    class="js-item-input-variant-discount-price half-input half-input--mw-305 validate[custom[positive_number], <?php echo form_validation_rules($validation, 'variant_price', 'min'); ?>,<?php echo form_validation_rules($validation, 'variant_price', 'max'); ?>]"
                    type="number"
                    step="1"
                    min="1"
                    placeholder="e.g. 10.55"/>
            </div>
            <div class="add-info-row__col">
                <label class="input-label input-label--required">Quantity</label>
                <input
                    <?php echo addQaUniqueIdentifier("items-my-add__var-combination-quantity")?>
                    class="js-item-input-variant-quantity half-input half-input--mw-305 validate[min[1],max[1000000000],custom[positive_integer]]"
                    type="number"
                    step="1"
                    min="0"
                    placeholder="Total Quantity"/>
            </div>
            <div class="add-info-row__col add-info-row__action-col add-info-row__col--130">
                <label class="input-label dn-md_i">&nbsp;</label>
                <button
                    class="btn btn-dark btn-block text-nowrap call-action"
                    data-js-action="price-variation-add-item-module:add-variant"
                    type="button"
                    <?php echo addQaUniqueIdentifier("items-my-add__var-combination-add-btn")?>
                >Add <span class="dn-md-min">combination</span></button>
            </div>
        </div>

        <label class="input-label">Check the created combination</label>
        <div id="js-add-item-variants-wr"></div>
    </div>
</div>
