<?php
    encoreEntryLinkTags('b2b_request_reg_page');
    encoreEntryScriptTags('b2b_request_reg_page');
?>

<div id="js-b2b-request-form-wr" class="container-1420 dashboard-container">
    <div id="js-dashboard-heading" class="dashboard-heading">
        <h1 class="dashboard-heading__ttl">
            <?php echo !empty($request) ? translate('b2b_form_edit_request_title') : translate('b2b_form_add_request_title'); ?>
        </h1>
    </div>

    <form
        id="js-b2b-request-form"
        class="b2b-request-form validengine"
        data-js-action="b2b-form:submit"
        data-request-type="<?php echo !isset($request) ? 'register' : 'edit'; ?>"
    >
        <div class="info-alert-b info-alert-b--fs16">
            <i class="ep-icon ep-icon_info-stroke"></i>
            <span><?php echo translate('b2b_reg_description'); ?></span>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="ep-label input-label--required"><?php echo translate('b2b_form_title_label'); ?></label>
                    <input
                        class="ep-input validate[minSize[3], maxSize[255], required]"
                        type="text"
                        name="title"
                        placeholder="<?php echo translate('b2b_form_title_placeholder', null, true); ?>"
                        value="<?php echo $request['b2b_title']; ?>"
                    >
                </div>

                <div class="form-group">
                    <label class="ep-label input-label--info input-label--required">
                        <span class="input-label__text mr-0"><?php echo translate('b2b_form_tags_label'); ?></span>
                        <a
                            class="info-dialog"
                            data-content="#info-dialog__tags-on-product"
                            data-title="<?php echo cleanOutput($blockInfo['about_tag_info']['title_block']); ?>"
                            title="<?php echo cleanOutput($blockInfo['about_tag_info']['title_block']); ?>"
                            href="#"
                        >
                            <i class="ep-icon ep-icon_info"></i>
                        </a>
                        <div class="display-n" id="info-dialog__tags-on-product">
                            <?php echo $blockInfo['about_tag_info']['text_block']; ?>
                        </div>
                    </label>

                    <?php views('new/tags_rule_view'); ?>

                    <?php if (!empty($request['b2b_tags'])) {?>
                        <input
                            class="ep-input js-b2b-request-form-tags-input"
                            name="tags"
                            value="<?php echo $request['b2b_tags']; ?>"
                            type="text"
                            placeholder="<?php echo translate('b2b_form_tags_placeholder', null, true); ?>"
                        >
                    <?php } else { ?>
                        <input
                            class="ep-input js-b2b-request-form-tags-input"
                            name="tags"
                            type="text"
                            placeholder="<?php echo translate('b2b_form_tags_placeholder', null, true); ?>"
                        >
                    <?php } ?>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="ep-label input-label--required"><?php echo translate('b2b_form_description_label'); ?></label>
                    <textarea
                        id="js-b2b-request-form-description"
                        class="b2b-request-form__textarea ep-textarea validate[required,maxSize[3000]]"
                        name="message"
                        data-max="3000"
                        placeholder="<?php echo translate('b2b_form_description_placeholder', null, true); ?>"
                    ><?php echo $request['b2b_message']; ?></textarea>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-6">
                <div class="form-group">
                    <label class="ep-label input-label--required"><?php echo translate('b2b_form_partners_type_label'); ?></label>
                    <select class="ep-select validate[required]" name="p_type" >
                        <option value=""><?php echo translate('b2b_form_partners_type_placeholder'); ?></option>
                        <?php if (!empty($partnersType)) {?>
                        <optgroup label="Partner's type">
                            <?php foreach ($partnersType as $type) {?>
                                <option
                                    value="<?php echo $type['id_type']; ?>"
                                    <?php echo selected($request['id_type'], $type['id_type']); ?>
                                >
                                    <?php echo $type['name']; ?>
                                </option>
                            <?php }?>
                        </optgroup>
                        <?php }?>
                    </select>
                </div>
            </div>

            <div class="col-12 col-sm-6">
                <div class="form-group">
                    <label class="ep-label input-label--required"><?php echo translate('b2b_form_company_label'); ?></label>
                    <select
                        id="js-b2b-request-company-branch-select"
                        class="ep-select validate[required] call-action"
                        name="company_branch"
                        data-js-action="b2b-form:company-branch.click"
                    >
                        <option value="" disabled><?php echo translate('b2b_form_company_placeholder'); ?></option>
                        <optgroup label="Company">
                            <option
                                value="<?php echo my_company_id(); ?>"
                                <?php echo empty($request) ? 'selected' : selected($request['id_company'], my_company_id()); ?>
                            >
                                <?php echo my_company_name(); ?>
                            </option>
                        </optgroup>
                        <?php if (!empty($branches)) {?>
                            <optgroup label="Branches">
                            <?php foreach ($branches as $branch) {?>
                                <option
                                    value="<?php echo $branch['id_company']; ?>"
                                    <?php echo selected($request['id_company'], $branch['id_company']); ?>
                                >
                                    <?php echo $branch['name_company']; ?>
                                </option>
                            <?php }?>
                            </optgroup>
                        <?php }?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <label class="ep-label input-label--required"><?php echo translate('b2b_form_industries_label'); ?></label>

                    <div id="js-b2b-dynamic-industries">
                        <?php widgetIndustriesMultiselect([
                            'industries'              => arrayGet($multipleselectIndustries, 'industries', []),
                            'selected_industries'     => arrayGet($multipleselectIndustries, 'industries_selected', []),
                            'categories'              => arrayGet($multipleselectIndustries, 'categories', []),
                            'selected_categories'     => arrayGet($multipleselectIndustries, 'categories_selected_by_id', []),
                            'max_selected_industries' => arrayGet($multipleselectIndustries, 'max_industries', 0),
                            'show_disclaimer'         => true,
                            'disclaimer_text'         => translate('multiple_select_select_max_info', ['{{COUNT}}' => arrayGet($multipleselectIndustries, 'max_industries', 0)]),
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <label class="ep-label input-label--required"><?php echo translate('b2b_form_main_image_label'); ?></label>
                    <?php views('new/b2b/form/cropper_view', ['parameters' => $cropperParameters]); ?>
                </div>
            </div>
        </div>

        <?php views('new/b2b/form/additional_pictures_view', ['photos' => $request['photos'], 'title' => $request['title']]); ?>

        <div class="b2b-request-form__location-wr row">
            <div class="col-12 col-sm-6 call-action" data-js-action="b2b-form:locate-partner.click">
                <div class="form-group">
                    <label class="ep-label input-label--required"><?php echo translate('b2b_form_locate_partner_label'); ?></label>
                    <select
                        id="js-b2b-request-location-select"
                        class="ep-select validate[required]"
                        name="type_location"
                        data-message="<?php echo translate('b2b_form_locate_partner_change_confirm_message', null, true); ?>"
                        data-js-action="b2b-form:locate-partner.change"
                    >
                        <option value="" disabled<?php echo !isset($request['type_location']) ? ' selected' : ''; ?>>
                            <?php echo translate('b2b_form_locate_partner_placeholder'); ?>
                        </option>
                        <?php if(isset($locationTypes)) {
                            foreach($locationTypes as $locationType => $locationTypeLabel){
                                $selected = ($locationType === $request['type_location']->value) ? "selected='selected'" : '';
                                echo "<option value=\"$locationType\" $selected>$locationTypeLabel</option>";
                            }
                        }?>
                    </select>
                </div>
            </div>

            <div id="js-b2b-request-country-wrapper" class="col-12 col-sm-6 <?php echo ($request['type_location'] === \App\Common\Contracts\B2B\B2bRequestLocationType::COUNTRY()) ? '': 'display-n_i';?>">
                <div class="b2b-request-form__country-wr form-group">
                    <select
                        id="js-b2b-request-country-select"
                        class="ep-select <?php echo !isset($request['type_location']) ? ' validate[required]' : ''; ?>"
                        name="country"
                        <?php echo empty($request['countries']) ? 'disabled' : '';?>
                    >
                        <?php echo getCountrySelectOptions($portCountry); ?>
                    </select>

                    <button
                        class="btn btn-dark btn-new16 call-action"
                        data-js-action="b2b-form:countries.add"
                        type="button"
                    >
                        <?php echo getEpIconSvg('plus-circle', [22, 22]); ?>
                    </button>
                </div>
            </div>

            <div id="js-b2b-request-radius-wrapper" class="col-12 col-sm-6 <?php echo ($request['type_location'] === \App\Common\Contracts\B2B\B2bRequestLocationType::RADIUS()) ? '' : 'display-n_i';?>">
                <div class="b2b-request-form__country-wr form-group">
                    <input
                        id="js-b2b-request-radius-input"
                        class="ep-input validate[required,custom[positive_integer],min[1],max[3000]]"
                        type="text"
                        name="radius"
                        placeholder="e.g. 1000"
                        value="<?php echo $request['b2b_radius']; ?>"
                        <?php echo empty($request['b2b_radius']) ? 'disabled' : '';?>
                    >
                </div>
            </div>

            <div class="col-12">
                <div id="js-option-group-wr" class="option-group">
                    <?php if ($request['countries']) { ?>
                        <?php foreach ($request['countries'] as $country) { ?>
                            <div class="option-group__item" data-option="<?php echo $country['id']; ?>">
                                <p class="option-group__text">
                                    <span><?php echo $country['country']; ?></span>
                                </p>
                                <button class="option-group__btn btn btn-light btn-new16 call-action" data-js-action="option-group:delete" type="button">
                                    <i class="ep-icon ep-icon_trash-stroke"></i>
                                </button>
                                <input type="hidden" name="countries[]" value="<?php echo $country['id']; ?>">
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <label class="ep-label input-label--required"><?php echo translate('b2b_form_tc_label'); ?></label>
                    <label class="custom-checkbox">
                        <input
                            class="validate[required]"
                            type="checkbox"
                            name="accept_tc"
                            <?php echo checked($request['b2b_active'], 1); ?>
                        >
                        <span class="custom-checkbox__text-agreement"><?php echo translate('b2b_form_tc_text'); ?></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?php if (isset($request)) {?>
                    <input type="hidden" name="request" value="<?php echo $request['id_request']; ?>">
                <?php }?>

                <button class="btn btn-primary btn-new16 mnw-150 pull-right" type="submit"><?php echo translate('b2b_form_submit_btn'); ?></button>
            </div>
        </div>
    </form>
</div>
