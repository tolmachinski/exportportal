<div class="wr-modal-flex inputs-40 dwn-subsribe-form">
    <form class="modal-flex__form validateModal" id="dwn-register-form" data-js-action="user_register:submit">
        <div class="modal-flex__content">

            <div class="dwn-subsribe-form__title"> <?php echo translate('dwn_form_headline', ['{{TITLE}}' => $content['title']]) ?> </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('dwn_first_name') ?></label>
                <input type="text"
                       class="validate[required],custom[validUserName],minSize[2],maxSize[50]"
                       name="fname"
                       placeholder="<?php echo translate('dwn_first_name') ?>">
            </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('dwn_last_name') ?></label>
                <input type="text"
                       class="validate[required],custom[validUserName],minSize[2],maxSize[50]"
                       name="lname"
                       placeholder="<?php echo translate('dwn_last_name') ?>">
            </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('dwn_email') ?></label>
                <input type="text"
                       class="validate[required,minSize[3],maxSize[50],custom[noWhitespaces],custom[email]]"
                       name="email"
                       placeholder="<?php echo translate('dwn_email') ?>">
            </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('dwn_phone_number') ?></label>
                <div class="input-group">
                    <div class="input-group-prepend wr-select2-h50 select-country-code-group">
                        <div class="notranslate">
                            <select class="validate[required]" name="code" id="js-phone-code">
                                <?php /** @var \App\Common\Contracts\Entities\CountryCodeInterface|\App\Common\Contracts\Entities\Phone\PatternsAwareInterface $phone_code */ ?>
                                <?php foreach($phone_codes as $phone_code) { ?>
                                    <option
                                        value="<?php echo cleanOutput($phone_code->getId()); ?>"
                                        data-phone-mask="<?php echo cleanOutput($phone_code->getPattern(\App\Common\Contracts\Entities\Phone\PatternsAwareInterface::PATTERN_INTERNATIONAL_MASK)); ?>"
                                        data-country-flag="<?php echo cleanOutput(getCountryFlag($phone_code_country = $phone_code->getCountry()->getName())); ?>"
                                        data-country-name="<?php echo cleanOutput($phone_code_country); ?>"
                                        data-country="<?php echo cleanOutput($phone_code->getCountry()->getId()); ?>"
                                        data-code="<?php echo cleanOutput($phone_code->getName()); ?>"
                                        <?php if ($selected_phone_code && $selected_phone_code->getId() === $phone_code->getId()) { ?>selected<?php } ?>>
                                        <?php echo cleanOutput(trim("{$phone_code->getName()}")); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <input id="js-phone-number"
						   class="form-control validate[required, funcCall[checkPhoneMask]]"
						   type="text"
						   name="phone"
						   maxlength="25"
						   placeholder="<?php echo translate('dwn_phone_number') ?>">

                </div>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('dwn_country') ?></label>
                <div class="wr-select2-h50">
                    <div class="notranslate">
                        <select class="validate[required]" name="country">
                            <option value=""><?php echo translate('dwn_select_country') ?></option>
                            <?php foreach($countries as $country) { ?>
                                <option value="<?php echo cleanOutput($country['id']); ?>" >
                                    <?php echo cleanOutput(trim("{$country['country']}")); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>

            <input type="hidden" name="title" value="<?php echo $content['title'] ?>">
            <input type="hidden" name="slug" value="<?php echo $content['slug'] ?>">
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit"><?php echo translate('form_button_submit_text') ?></button>
            </div>
        </div>
    </form>
</div>

<?php
    echo dispatchDynamicFragment("user_register:phone_field", [translate('register_error_phone_mask')], true);
    echo dispatchDynamicFragment("user_register:download", null, true);
?>
