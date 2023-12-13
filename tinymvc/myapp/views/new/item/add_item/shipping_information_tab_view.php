<div class="container-fluid-modal">
    <div class="form-group">
        <label class="input-label <?php echo form_validation_label($validation, 'hts', 'required'); ?>">
            Harmonized Tariff Schedule
        </label>

        <div class="input-info-right">
            <input
                <?php echo addQaUniqueIdentifier("items-my-add__harmonized-tariff-schedule")?>
                class="half-input half-input--mw-305 validate[<?php echo form_validation_rules($validation, 'hts', 'required'); ?>,custom[tariffNumber]]"
                data-prompt-position="bottomLeft:0"
                type="text"
                maxlength="13"
                name="hs_tariff_number"
                value="<?php echo !empty($item['hs_tariff_number']) ? cleanOutput($item['hs_tariff_number']) : (!empty($cat_option['hs_tariff_number']) ? $cat_option['hs_tariff_number'] : null); ?>"
                placeholder="Product code"/>

            <div class="input-info-right__txt">
                <span class="show-mn-767">The first six digit code for your item as specified by </span>
                <a href="https://hts.usitc.gov" target="_blank" title="https://hts.usitc.gov" <?php echo addQaUniqueIdentifier("items-my-add__harmonized-tariff-link")?>>Harmonized system for tariffs</a>
            </div>
        </div>
    </div>

    <?php app()->view->display('new/item/add_item/partials/sizes_view'); ?>

    <div class="form-group">
        <label class="input-label <?php echo form_validation_label($validation, 'country', 'required'); ?>">Item location</label>
        <?php widgetLocationBlock(
            new \Symfony\Component\HttpFoundation\ParameterBag(array(
                'saved'              => $address ?? null,
                'overrided'          => $other_address_input_value ?? null,
                'overrided_location' => $other_location ?? null,
            )),
            new \Symfony\Component\HttpFoundation\ParameterBag(array('address' => false, 'postal_code' => true)),
            null,
            null,
            "{{country}}, {{state}}, {{city}}, {{postal_code}}"
        ); ?>
    </div>
</div>
