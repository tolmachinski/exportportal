<ul id="<?php echo cleanOutput($wrapper ?? 'location-form--wrappper'); ?>" class="js-location list-form-checked-info">
    <li class="list-form-checked-info__item">
        <label class="list-form-checked-info__label custom-radio" <?php echo addQaUniqueIdentifier("global-location__my-address")?>>
            <input type="radio"
                name="address_type"
                class="validate[required] js-address-radio js-saved-address"
                data-validation-template="validate[required]"
                value="stored"
                <?php echo empty($overrided_address) ? 'checked' : ''; ?>>
            <span class="list-form-checked-info__check-text custom-radio__text"><?php echo cleanOutput($texts['saved_location_label_text']); ?></span>
        </label>
        <p class="list-form-checked-info__data">
            <?php if (null !== $saved_address) { ?>
                <?php echo cleanOutput($saved_address); ?>
            <?php } else { ?>
                &mdash;
            <?php } ?>
        </p>
    </li>
    <li class="list-form-checked-info__item">
        <label class="list-form-checked-info__label custom-radio" <?php echo addQaUniqueIdentifier("global-location__different-address")?>>
            <input type="radio"
                name="address_type"
                class="validate[required] js-address-radio js-overrided-address"
                data-validation-template="validate[required]"
                value="custom"
                <?php echo !empty($overrided_address) ? 'checked' : ''; ?>>
            <span class="list-form-checked-info__check-text custom-radio__text"><?php echo cleanOutput($texts['overrided_location_label_text']); ?></span>
        </label>
        <div class="list-form-checked-info__data">
            <input type="text"
                <?php echo addQaUniqueIdentifier("global-location__different-address-add")?>
                class="js-address-input"
                placeholder="<?php echo cleanOutput($texts['overrided_location_placeholder_text']); ?>"
                data-validation-template="validate[required]"
                value="<?php echo cleanOutput($overrided_address ?? ''); ?>"
                readonly>
        </div>
    </li>
</ul>

<script><?php echo getPublicScriptContent('/plug/js/locations/partial.js', true); ?></script>
<script>
    $(function () {
		if (!('LocationPartialModule' in window)) {
			if (__debug_mode) {
				console.error(new SyntaxError("'LocationPartialModule' must be defined"))
			}

			return;
		}

		LocationPartialModule.default(
            <?php echo json_encode(array(
                'locationTemplate' => $template ?? null,
                'locationConfig'   => array(
                    'title'      => $title ?? 'Provide another address',
                    'address'    => $enable_address ?? false,
                    'postalCode' => $enable_postal_code ?? false,
                ),
                'location'         => $overrided_location ?? null,
                'selectors'     => array(
                    'addressesWrapper'    => $wrapper = $wrapper ?? '#location-form--wrappper',
                    'addressRadios'       => $types = $types ?? "{$wrapper} input[type=\"radio\"].js-address-radio",
                    'addressInput'        => $input ?? '.js-address-input',
                    'savedAddress'        => "{$types}.js-saved-address",
                    'overridedAddress'    => "{$types}.js-overrided-address",
                ),
            )); ?>
        );
	});
</script>
