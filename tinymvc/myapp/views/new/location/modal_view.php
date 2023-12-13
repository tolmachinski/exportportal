<form class="js-global-location-form validateModal inputs-40">
	<label class="input-label input-label--required mt-0">Country</label>
	<select id="js-location-country" class="validate[required]" name="country" <?php echo addQaUniqueIdentifier("global-location-popup__select-country")?>>
		<?php echo getCountrySelectOptions($country, empty($id_country) ? 0 : $id_country); ?>
	</select>

	<label class="input-label input-label--required">State/Region</label>
	<select id="js-location-country-states" class="validate[required]" name="state" <?php echo addQaUniqueIdentifier("global-location-popup__select-region")?>>
		<option value="">Select your state or region</option>
		<?php if (!empty($states)) { ?>
			<?php foreach ($states as $state) { ?>
				<option value="<?php echo cleanOutput($state['id']); ?>" <?php echo selected($id_state, $state['id']);?>>
					<?php echo cleanOutput($state['state']); ?>
				</option>
			<?php } ?>
		<?php } ?>
	</select>

	<label class="input-label input-label--required">City</label>
	<div class="wr-select2-h35" <?php echo addQaUniqueIdentifier("global-location-popup__select-city")?>>
		<select id="js-location-port-city" class="validate[required]" name="city">
			<option value="">Select city</option>
			<?php if (!empty($city_selected)) { ?>
				<option value="<?php echo cleanOutput($city_selected['id']); ?>" selected>
					<?php echo cleanOutput($city_selected['city']); ?>
				</option>
			<?php } ?>
		</select>
	</div>

	<?php if (isset($postal_code_show)) { ?>
		<label class="input-label input-label--required">Zip Code</label>
		<input <?php echo addQaUniqueIdentifier("global-location-popup__zip-code")?> class="validate[required,maxSize[20]] half-input" type="text" name="postal_code" placeholder="e.g. 90001" value="<?php echo cleanOutput($postal_code ?? null); ?>">
	<?php } ?>

	<?php if (isset($address_show)) { ?>
		<label class="input-label input-label--required">Address</label>
		<input <?php echo addQaUniqueIdentifier("global-location-popup__address")?> class="validate[required,maxSize[250]]" type="text" name="address" value="<?php echo cleanOutput($address?? null); ?>">
	<?php } ?>
</form>
