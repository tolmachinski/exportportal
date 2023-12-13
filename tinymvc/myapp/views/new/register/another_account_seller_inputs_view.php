<?php
    if(!isset($multipleselect_position_class)){
        $multipleselect_position_class = "";
    }
?>

<div class="form-group">
    <label class="input-label"><?php echo translate('register_label_company_name');?></label>
    <input
        type="text"
        class="validate[required,custom[companyTitle],minSize[3],maxSize[50]]"
        name="company_legal_name_additional_<?php echo $input_name;?>"
        placeholder="<?php echo translate('register_label_company_name_placeholder');?>"
        <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-additional-{$input_name}-company-name")?>>
</div>

<div class="form-group">
    <label class="input-label">
        <?php echo translate('register_label_company_displayed_name');?><a
            class="js-information-dialog ep-icon ep-icon_info"
            data-message="<?php echo translate('register_label_company_displayed_name_info');?>"
            data-title="<?php echo translate('register_label_company_displayed_name');?>"
            data-keep-modal="1"
            title="<?php echo translate('register_label_company_displayed_name');?>"
            href="#"></a>
    </label>
    <input
        class="validate[required,custom[companyTitle],minSize[3],maxSize[50]]"
        type="text"
        name="company_name_additional_<?php echo $input_name;?>"
        placeholder="<?php echo translate('register_label_company_displayed_name_placeholder');?>"
        <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-additional-{$input_name}-company-name-displayed")?>>
</div>

<div class="form-group">
    <label class="input-label"><?php echo translate("multiple_select_label_industries_categories")?></label>
    <div
        class="<?php echo $multipleselect_position_class;?>"
        <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-additional-{$input_name}-select-industries")?>
    >
        <?php
            widgetIndustriesMultiselect(
                array_merge($multipleselect_industries, [
                    "input_suffix" => "_additional_{$input_name}",
                ])
            );
        ?>
    </div>
</div>
