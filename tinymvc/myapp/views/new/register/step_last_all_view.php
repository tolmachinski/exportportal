<?php if ($simple_location ?? false) { ?>
    <?php views()->display('new/location/partials/inline_type_permissible_view'); ?>
<?php } else { ?>
    <?php views()->display('new/location/partials/inline_type_strict_view'); ?>
<?php } ?>

<div class="form-group">
    <label class="input-label"><?php echo translate('form_label_address');?></label>
    <input type="text" class="validate[required,minSize[3],maxSize[255]]" name="address" placeholder="<?php echo translate('register_type_address_placeholder', null, true); ?>" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-address")?>>
</div>

<div class="form-group">
    <label class="input-label"><?php echo translate('register_zip_code_label'); ?></label>
    <input type="text" class="w-50pr validate[required,custom[zip_code],maxSize[20]]" name="zip" placeholder="<?php echo translate('register_placeholder_zip', null, true); ?>" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-zip-code")?>>
</div>

<input type="hidden" name="company_type" value="<?php echo $company_type;?>">
