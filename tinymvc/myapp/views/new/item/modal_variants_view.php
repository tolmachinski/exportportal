<?php if (!empty($itemVariant)) {?>
    <div class="form-group">
        <label class="input-label">Options</label>
        <div>
            <?php foreach ($variantOptions as $variantOption) {?>
                <span class="product__variant-selected">
                    <span class="product__variant-selected-name">
                        <?php echo $variantOption['propertyName'];?>:
                    </span>

                    <span class="product__variant-selected-param">
                        <?php echo $variantOption['name'];?>
                    </span>
                </span>
                <input type="hidden" name="variant[options][]" value="<?php echo $variantOption['id'];?>">
            <?php }?>

            <input type="hidden" name="variant[id]" value="<?php echo $itemVariant['id'];?>">
        </div>
    </div>
<?php }?>
