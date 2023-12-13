<div class="age-verification">
    <div class="js-modal-flex wr-modal-flex inputs-40">
        <form id="js-age-verification" class="modal-flex__form validateModal" data-callback="ageVerification" data-js-action="popup:age-verification-submit">
            <div class="modal-flex__content mb-0">
                <label class="input-label input-label--required">Date of Birth</label>
                <div class="row ml-0 mr-0">
                    <div class="col-12 col-md-4 age-verification__day">
                        <select id="js-days-value" class="validate[required]" name="day">
                            <option selected disabled>Day</option>
                            <?php for ($i = 1; $i <= 31; $i++) { ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 age-verification__month">
                        <select id="js-months-value" class="validate[required]" name="month">
                            <option selected disabled>Month</option>
                            <?php foreach ($list_of_months as $k => $v) { ?>
                            <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 age-verification__year">
                        <select id="js-years-value" class="validate[required]" name="year">
                            <option selected disabled>Year</option>
                            <?php $to_year = (int)date('Y'); ?>
                            <?php $min_year = $to_year - 60; ?>
                            <?php for ($to_year; $to_year >= $min_year; $to_year--) { ?>
                            <option value="<?php echo $to_year; ?>"><?php echo $to_year; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="warning-alert-b mt-10 pb-10 display-n js-show-warning">
                    <i class="ep-icon ep-icon_warning-circle-stroke"></i>
                    <span>You are under 18 years of age and therefore cannot access this category.</span>
                </div>

                <label class="custom-checkbox mt-15">
                    <input id="js-terms-and-conditions-checkbox"
                            class="validate[required]"
                            name="checkbox_legal_name"
                            type="checkbox" <?php if (!empty($user['legal_name'])) echo "checked" ?>>
                    <span class="custom-checkbox__text-agreement">
                        By accessing this page you agree to the
                        <a class="txt-underline"
                            href="<?php echo __SITE_URL; ?>terms_and_conditions/tc_restricted_adult_items_policy"
                            target="_blank">Adult items policy</a>
                    </span>
                </label>
            </div>
        </form>
    </div>
</div>

<?php if(isset($webpackData)) { ?>
    <?php echo dispatchDynamicFragment("popup:age-verification"); ?>
<?php } else { ?>
    <script src="<?php echo fileModificationTime('public/plug/js/categories/age-verification.js'); ?>"></script>
<?php } ?>
