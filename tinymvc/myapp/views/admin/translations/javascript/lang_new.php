// @ts-nocheck
/* eslint-disable semi */
/* eslint-disable no-undef */
/* eslint-disable no-unused-vars */
/* eslint-disable no-useless-escape */
/* eslint-disable quotes */
/* eslint-disable indent */
(function(){
    var domains = {
        bootstrap_tour: {
        <?php foreach ($langs as $lang) { ?>
    <?php echo $lang;?>: {
                close_ttl: '<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_tour_close_ttl"], "text", $lang));?>',
                close_txt: '<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_tour_close_txt"], "text", $lang));?>',
                btn_got: '<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_tour_btn_got"], "text", $lang));?>',
                steps_title: '<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_tour_steps_title"], "text", $lang));?>',
                steps_content: '<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_tour_steps_content"], "text", $lang));?>'
            },
        <?php } ?>},
        hideMaxListItems: {
        <?php foreach ($langs as $lang) { ?>
    <?php echo $lang;?>: {
                more_text: '<?php echo addslashes(translationFileKeyI18n($records["js_hide_max_list_more_text"], "text", $lang));?>',
                less_text: '<?php echo addslashes(translationFileKeyI18n($records["js_hide_max_list_less_text"], "text", $lang));?>',
                show_more: '<?php echo addslashes(translationFileKeyI18n($records["max_list_show_more"], "text", $lang));?>',
                show_less: '<?php echo addslashes(translationFileKeyI18n($records["max_list_show_less"], "text", $lang));?>',
            },
        <?php } ?>},
        textcounter: {
        <?php foreach ($langs as $lang) { ?>
    <?php echo $lang;?>: {
                count_down_text_before: '<?php echo addslashes(translationFileKeyI18n($records["js_textcounter_text_before"], "text", $lang));?>',
                count_down_text_after: '<?php echo addslashes(translationFileKeyI18n($records["js_textcounter_text_after"], "text", $lang));?>'
            },
        <?php } ?>},
        dtFilters: {
        <?php foreach ($langs as $lang) { ?>
    <?php echo $lang;?>: {
                btn_reset: '<?php echo addslashes(translationFileKeyI18n($records["js_dt_filters_btn_reset"], "text", $lang));?>',
                btn_apply: '<?php echo addslashes(translationFileKeyI18n($records["js_dt_filters_btn_apply"], "text", $lang));?>'
            },
        <?php } ?>},
        multipleSelect: {
        <?php foreach ($langs as $lang) { ?>
    <?php echo $lang;?>: {
                placeholder_users: '<?php echo addslashes(translationFileKeyI18n($records["js_multiple_select_placeholder_users"], "text", $lang));?>',
                placeholder_industries: '<?php echo addslashes(translationFileKeyI18n($records["js_multiple_select_placeholder_industries"], "text", $lang));?>',
                select_all_text: '<?php echo addslashes(translationFileKeyI18n($records["js_multiple_select_all_text"], "text", $lang));?>',
                all_selected: '<?php echo addslashes(translationFileKeyI18n($records["js_multiple_select_all_selected"], "text", $lang));?>',
                count_selected: '<?php echo addslashes(translationFileKeyI18n($records["js_multiple_select_count_selected"], "text", $lang));?>',
                no_matches_found: '<?php echo addslashes(translationFileKeyI18n($records["js_multiple_select_no_matches_found"], "text", $lang));?>',
            },
        <?php } ?>},
        fancybox: {
        <?php foreach ($langs as $lang) { ?>
    <?php echo $lang;?>: {
                next: '<?php echo addslashes(translationFileKeyI18n($records["js_fancybox_next"], "text", $lang));?>',
                prev: '<?php echo addslashes(translationFileKeyI18n($records["js_fancybox_prev"], "text", $lang));?>',
                close: '<?php echo addslashes(translationFileKeyI18n($records["js_fancybox_close"], "text", $lang));?>',
                close_message: '<?php echo addslashes(translationFileKeyI18n($records["js_fancybox_close_message"], "text", $lang));?>',
                error: '<?php echo addslashes(translationFileKeyI18n($records["js_fancybox_error"], "text", $lang));?>',
            },
        <?php } ?>},
        fancybox3: {
        <?php foreach ($langs as $lang) { ?>
    <?php echo $lang;?>: {
                close: '<?php echo addslashes(translationFileKeyI18n($records["js_fancybox_close"], "text", $lang));?>',
                close_message: '<?php echo addslashes(translationFileKeyI18n($records["js_fancybox_close_message"], "text", $lang));?>',
                cancel: '<?php echo addslashes(translationFileKeyI18n($records["js_fancybox_cancel"], "text", $lang));?>',
                go_to_ep: '<?php echo addslashes(translationFileKeyI18n($records["js_fancybox_go_to_ep"], "text", $lang));?>',
                go_to_epl: '<?php echo addslashes(translationFileKeyI18n($records["js_fancybox_go_to_epl"], "text", $lang));?>',
                items_droplist_popup_header: "<?php echo addslashes(translationFileKeyI18n($records["items_droplist_popup_header"], "text", $lang));?>",
                items_add_to_droplist_btn: "<?php echo addslashes(translationFileKeyI18n($records["items_add_to_droplist_btn"], "text", $lang));?>"
            },
        <?php } ?>},
        BootstrapDialog: {
        <?php foreach ($langs as $lang) { ?>
    <?php echo $lang;?>: {
                ok: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_ok"], "text", $lang));?>",
                cancel: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_cancel"], "text", $lang));?>",
                payment: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_payment"], "text", $lang));?>",
                view_order: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_view_order"], "text", $lang));?>",
                view_sample_order: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_view_sample_order"], "text", $lang));?>",
                continue_shopping: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_continue_shopping"], "text", $lang));?>",
                continue: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_continue"], "text", $lang));?>",
                close: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_close"], "text", $lang));?>",
                stay_certified: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_stay_certified"], "text", $lang));?>",
                confirm: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_confirm"], "text", $lang));?>",
                skip: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_skip"], "text", $lang));?>",
                apply: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_apply"], "text", $lang));?>",
                go_to_epl: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_go_to_epl"], "text", $lang));?>",
                contact_support: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_contact_support"], "text", $lang));?>",
                login: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_login"], "text", $lang));?>",
                contact_us: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_contact_us"], "text", $lang));?>",
                go_to_droplist: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_go_to_droplist"], "text", $lang));?>",
                items_remove_from_droplist_btn: "<?php echo addslashes(translationFileKeyI18n($records["items_remove_from_droplist_btn"], "text", $lang));?>",
                items_droplist_remove_ttl: "<?php echo addslashes(translationFileKeyI18n($records["items_droplist_remove_ttl"], "text", $lang));?>",
                items_droplist_remove_subttl: "<?php echo addslashes(translationFileKeyI18n($records["items_droplist_remove_subttl"], "text", $lang));?>",
                items_droplist_unavailable_ttl: "<?php echo addslashes(translationFileKeyI18n($records["items_droplist_unavailable_ttl"], "text", $lang));?>",
                items_droplist_unavailable_subttl: "<?php echo addslashes(translationFileKeyI18n($records["items_droplist_unavailable_subttl"], "text", $lang));?>",
                no: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_no"], "text", $lang));?>",
                yes: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_yes"], "text", $lang));?>",
                areYouSureToDeleteFromCalendar: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_are_you_sure_to_delete_from_calendar"], "text", $lang));?>",
                deleteFromCalendar: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_delete_from_calendar"], "text", $lang));?>",
            },
        <?php } ?>},
        pwstrength : {
        <?php foreach ($langs as $lang) { ?>
    <?php echo $lang;?> : {
                wordLowercase: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_word_lowercase"], "text", $lang));?>",
                wordUppercase: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_word_uppercase"], "text", $lang));?>",
                wordOneNumber: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_word_one_number"], "text", $lang));?>",
                wordMinLength: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_word_min_length"], "text", $lang));?>",
                wordMaxLength: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_word_max_length"], "text", $lang));?>",
                wordInvalidChar: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_word_invalid_char"], "text", $lang));?>",
                wordNotEmail: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_word_not_email"], "text", $lang));?>",
                wordSimilarToUsername: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_similar_username"], "text", $lang));?>",
                wordTwoCharacterClasses: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_two_character_classes"], "text", $lang));?>",
                wordRepetitions: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_repetitions"], "text", $lang));?>",
                wordSequences: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_sequences"], "text", $lang));?>",
                errorList: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_error_list"], "text", $lang));?>",
                veryWeak: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_very_weak"], "text", $lang));?>",
                weak: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_weak"], "text", $lang));?>",
                normal: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_normal"], "text", $lang));?>",
                medium: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_medium"], "text", $lang));?>",
                strong: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_strong"], "text", $lang));?>",
                veryStrong: "<?php echo addslashes(translationFileKeyI18n($records["js_pwstrength_very_strong"], "text", $lang));?>"
            },
        <?php } ?>},
        stripe: {
        <?php foreach ($langs as $lang) { ?>
    <?php echo $lang; ?> : {
                incomplete: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_incomplete"], 'text', $lang)); ?>",
                incomplete_au_bank_account_number: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_incomplete_au_bank_account_number"], 'text', $lang)); ?>",
                incomplete_au_bank_account_bsb: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_incomplete_au_bank_account_bsb"], 'text', $lang)); ?>",
                incomplete_cvc: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_incomplete_cvc"], 'text', $lang)); ?>",
                incomplete_expiry: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_incomplete_expiry"], 'text', $lang)); ?>",
                incomplete_iban: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_incomplete_iban"], 'text', $lang)); ?>",
                incomplete_number: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_incomplete_number"], 'text', $lang)); ?>",
                incomplete_zip: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_incomplete_zip"], 'text', $lang)); ?>",
                invalid_au_bank_account_bsb: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_invalid_au_bank_account_bsb"], 'text', $lang)); ?>",
                invalid_au_bank_account_number_testmode: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_invalid_au_bank_account_number_testmode"], 'text', $lang)); ?>",
                invalid_expiry_month: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_invalid_expiry_month"], 'text', $lang)); ?>",
                invalid_expiry_month_past: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_invalid_expiry_month_past"], 'text', $lang)); ?>",
                invalid_expiry_year: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_invalid_expiry_year"], 'text', $lang)); ?>",
                invalid_expiry_year_past: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_invalid_expiry_year_past"], 'text', $lang)); ?>",
                invalid_iban: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_invalid_iban"], 'text', $lang)); ?>",
                invalid_iban_country_code: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_invalid_iban_country_code"], 'text', $lang)); ?>",
                invalid_iban_start: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_invalid_iban_start"], 'text', $lang)); ?>",
                invalid_number: "<?php echo addslashes(translationFileKeyI18n($records["stripe_public_validation_erorr_invalid_number"], 'text', $lang)); ?>",
            },
        <?php } ?>},
        validationEngine: {
        <?php foreach ($langs as $lang) { ?>
    <?php echo $lang;?>: {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_required_alert_text"], "text", $lang));?>",
                    "alertTextCheckboxMultiple": "* <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_required_alert_text_checkbox_multiple"], "text", $lang));?>",
                    "alertTextCheckboxe": "* <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_required_alert_text_checkbox"], "text", $lang));?>",
                    "alertTextDateRange": "* <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_required_alert_text_date_range"], "text", $lang));?>"
                },
                "requiredInFunction": {
                    "func": function(field, rules, i, options){
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_required_function_alert_text"], "text", $lang));?>"
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_date_range_alert_text"], "text", $lang));?> ",
                    "alertText2": " <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_date_range_alert_text2"], "text", $lang));?>"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_date_time_range_alert_text"], "text", $lang));?> ",
                    "alertText2": " <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_date_time_range_alert_text2"], "text", $lang));?>"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_min_size_alert_text"], "text", $lang));?> ",
                    "alertText2": " <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_min_size_alert_text2"], "text", $lang));?>"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_max_size_alert_text"], "text", $lang));?> ",
                    "alertText2": " <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_max_size_alert_text2"], "text", $lang));?>"
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_group_required_alert_text"], "text", $lang));?>"
                },
                "min": {
                    "regex": "none",
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_min_alert_text"], "text", $lang));?> "
                },
                "max": {
                    "regex": "none",
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_max_alert_text"], "text", $lang));?> "
                },
                "past": {
                    "regex": "none",
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_past_alert_text"], "text", $lang));?>"
                },
                "future": {
                    "regex": "none",
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_future_alert_text"], "text", $lang));?> "
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_max_checkbox_alert_text"], "text", $lang));?> ",
                    "alertText2": " <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_max_checkbox_alert_text2"], "text", $lang));?>"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_min_checkbox_alert_text"], "text", $lang));?> ",
                    "alertText2": " <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_min_checkbox_alert_text2"], "text", $lang));?>"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_equals_alert_text"], "text", $lang));?>"
                },
                "noDuplicateValueByName": {
                    "func": function (field, rules, i, options) {
                        var key_vals = [];

                        $('input[name="' + field.attr('name') + '"]').each(function(key, input){
                            key_vals.push($(input).val());
                        });

                        key_vals = key_vals.filter(
                            function(index){
                                return index.length;
                            }
                        );

                        return key_vals.every(function (num, index) { return index === key_vals.lastIndexOf(num) });
                    },
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_no_duplicate_text"], "text", $lang));?>"
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_credit_card_alert_text"], "text", $lang));?>"
                },
                "email": {
                    // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
                    "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_email_alert_text"], "text", $lang));?>"
                },
                "emails": {
                    // http://emailregex.com/
                    "regex": /^((([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))(\s*[,]{0,1}\s*))+$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_emails_alert_text"], "text", $lang));?>"
                },
                "maxEmailsCount": {
                    "regex": "none",
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_max_emails_count_alert_text"], "text", $lang));?> ",
                    "alertText2": " <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_max_emails_count_alert_text2"], "text", $lang));?>"
                },
                "integer": {
                    "regex": /^$|^[\-\+]?\d+$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_integer_alert_text"], "text", $lang));?>"
                },
                "positive_integer": {
                    "regex": /^$|^\d{1,10}$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_positive_integer_alert_text"], "text", $lang));?>"
                },
                "natural": {
                    "regex": /^[1-9][0-9]*$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_natural_alert_text"], "text", $lang));?>"
                },
                "number": {
                    // Number, including positive, negative, and floating decimal. credit: orefalo
                    "regex": /^[\-\+]?((([0-9]{1,3})([,][0-9]{3})*)|([0-9]+))?([\.]([0-9]+))?$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_number_alert_text"], "text", $lang));?>"
                },
                "positive_number": {
                    // Number, including positive, and floating decimal. credit: orefalo
                    "regex": /^$|^(([0]{1})|([1-9]{1}[0-9]{0,11}))(\.([0-9]{1,2}))?$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_positive_number_alert_text"], "text", $lang));?>"
                },
                "item_size": {
                    // Max 9999.99 is working but min 0.01 is not, it also accepts 0 (be careful)
                    "regex": /^$|^(([0]{1})|([1-9]{1}[0-9]{0,3}))(\.([0-9]{1,2}))?$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_item_size_alert_text"], "text", $lang));?>"
                },
                "zip_code": {
                    "regex": /^$|^[0-9A-Za-z\-\. ]{3,20}$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_zip_code_alert_text"], "text", $lang));?>"
                },
                "date": {
                    "func": function (field) {
                        var pattern = new RegExp(/^(\d{4})[\/\-\.](0?[1-9]|1[012])[\/\-\.](0?[1-9]|[12][0-9]|3[01])$/);
                        var match = pattern.exec(field.val());
                        if (match == null){
                        return false;
                        }

                        var year = match[1];
                        var month = match[2]*1;
                        var day = match[3]*1;
                        var date = new Date(year, month - 1, day); // because months starts from 0.

                        return (date.getFullYear() == year && date.getMonth() == (month - 1) && date.getDate() == day);
                    },
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_date_alert_text"], "text", $lang));?>"
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_ipv4_alert_text"], "text", $lang));?>"
                },
                "url": {
                    "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_url_alert_text"], "text", $lang));?>"
                },
                "valid_url": {
                    "regex": /^$|^\b(?:(?:https?):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_url_alert_text"], "text", $lang));?>"
                },
                "onlyNumberSp": {
                    "regex": /^$|^[0-9\ ]+$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_only_number_sp_alert_text"], "text", $lang));?>"
                },
                "tariffNumber": {
                    "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_tariff_number_alert_text"], "text", $lang));?>"
                },
                "onlyNumber": {
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_only_number_alert_text"], "text", $lang));?>"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_phone_number_alert_text"], "text", $lang));?>"
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+\-\_\.\,\:\ \'\;\(\)\#\%]+$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_product_title_alert_text"], "text", $lang));?>"
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z\-\_\.\,\ \'\&\(\)]+$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_company_title_alert_text"], "text", $lang));?>"
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z\ \'\-]{1,}$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_valid_user_name_alert_text"], "text", $lang));?>"
                },
                "onlyLetterSp": {
                    "regex": /^$|^[a-zA-Z\ \']+$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_only_letter_sp_alert_text"], "text", $lang));?>"
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_only_letter_number_alert_text"], "text", $lang));?>"
                },
                "onlyLetterNumberSp": {
                    "regex": /^$|^[0-9a-zA-Z\ ]+$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_only_letter_number_sp_alert_text"], "text", $lang));?>"
                },
                "alphaNumeric": {
                    "regex": /^([A-Za-z0-9\'\"\-\.\s])+$/i,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_alpha_numeric_alert_text"], "text", $lang));?>"
                },
                "iframe": {
                    "regex": /^<iframe.* src=\"(.*)\".*><\/iframe>$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_iframe_alert_text"], "text", $lang));?>"
                },
                // --- CUSTOM RULES -- Those are specific to the demos, they can be removed or changed to your likings
                "companyLink": {
                    "regex": /^[\w\d\-\_]*$/i,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_check_company_link_alert_text"], "text", $lang));?>"
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    //"extraDataDynamic": ['#password'],
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_check_password_alert_text"], "text", $lang));?>",
                    "alertTextOk": "<?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_check_password_alert_text_ok"], "text", $lang));?>",
                    "alertTextLoad": "* <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_check_alert_text_load"], "text", $lang));?>"
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    //"extraDataDynamic": ['#email'],
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_check_email_alert_text"], "text", $lang));?>",
                    "alertTextOk": "<?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_check_email_alert_text_ok"], "text", $lang));?>",
                    "alertTextLoad": "* <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_check_alert_text_load"], "text", $lang));?>"
                },
                "validate2fields": {
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_validate2fields_alert_text"], "text", $lang));?>"
                },
                //tls warning:homegrown not fielded
                "dateFormat":{
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(?:(?:0?[1-9]|1[0-2])(\/|-)(?:0?[1-9]|1\d|2[0-8]))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(0?2(\/|-)29)(\/|-)(?:(?:0[48]00|[13579][26]00|[2468][048]00)|(?:\d\d)?(?:0[48]|[2468][048]|[13579][26]))$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_date_format_alert_text"], "text", $lang));?>"
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_date_time_format_alert_text"], "text", $lang));?>",
                    "alertText2": "<?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_date_time_format_alert_text2"], "text", $lang));?> ",
                    "alertText3": "<?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_date_time_format_alert_text3"], "text", $lang));?> ",
                    "alertText4": "yyyy-mm-dd hh:mm:ss AM|PM"
                },
                "variableName": {
                    "regex": /^[a-z_]+$/,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_variable_name_alert_text"], "text", $lang));?>"
                },
                "valid_facebook_link": {
                    "regex": /^$|(https?:)?\/\/(www\.)?(mbasic\.facebook|m\.facebook|facebook|fb)\.(com|me)\/(?:(?:\w\.)*#!\/)?(?:pages\/)?(?:[\w\-\.]*\/)?(?:profile\.php\?id=(?=\d.*))?([\w\-\.]*)/i,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_facebook_link_alert_text"], "text", $lang));?>"
                },
                "valid_twitter_link": {
                    "regex": /^$|(https?:)?\/\/(www\.)?twitter.com\/(#!\/)?([^\/ ].)+/i,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_twitter_link_alert_text"], "text", $lang));?>"
                },
                "valid_instagram_link": {
                    "regex": /^$|(?:https?:\/\/)?(?:www\.)?(instagram\.com|instagr\.am)\/(p\/)?@?([a-z0-9_](?:(?:[a-z0-9_]|(?:\.(?!\.))){0,28}(?:[a-z0-9_]))?)\/?(\/?(?:\?.*))?$/i,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_instagram_link_alert_text"], "text", $lang));?>"
                },
                "valid_linkedin_link": {
                    "regex": /^$|^https:\/\/[a-z]{2,3}\.linkedin\.com\/.*$/i,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_linkedin_link_alert_text"], "text", $lang));?>"
                },
                "valid_youtube_link": {
                    "regex": /^$|^https?:\/\/(www\.)?youtube\.com\/(channel\/UC[\w-]{21}[AQgw]|(c\/|user\/)?[\w-]+)$/i,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_youtube_link_alert_text"], "text", $lang));?>"
                },
                "valid_skype_name": {
                    "regex": /^$|^[a-zA-Z][a-zA-Z0-9\.,\-_]{5,31}$/i,
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_skype_name_alert_text"], "text", $lang));?>"
                },
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_possible_duns_alert_text"], "text", $lang));?>"
                },
                "noTrailingWhitespaces": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /[ \t]+$/i.test(text) === false;
                    },
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_no_trailing_spaces_alert_text"], "text", $lang));?>"
                },
                "noWhitespaces": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return text.trim() === text;
                    },
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_no_whitespaces_spaces_alert_text"], "text", $lang));?>"
                },
                "emailWithWhitespaces": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^([\s]*)?(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))([\s]*)?$/.test(text);
                    },
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_email_alert_text"], "text", $lang));?>"
                },
                "emailsWithWhitespaces": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^([\s]*)?((([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))(\s*[,]{0,1}\s*))+([\s]*)?$/.test(text);
                    },
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_emails_alert_text"], "text", $lang));?>"
                },
                "minField": {
                    "func": function(field, rules, i, options) {
                        var selector = rules[i + 2] || null;
                        if (null === selector || $(selector).val() === "") {
                            return true;
                        }

                        var min = parseFloat($(selector).val() || 0);
                        var len = parseFloat(field.val());
                        if (len < min) {
                            options.allrules.minField.alertText = options.allrules.minField.alertTextDefault + min;

                            return false;
                        }

                        return true;
                    },
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_min_alert_text"], "text", $lang));?> ",
                    "alertTextDefault": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_min_alert_text"], "text", $lang));?> "
                },
                "maxField": {
                    "func": function(field, rules, i, options) {
                        var selector = rules[i + 2] || null;
                        if (null === selector || $(selector).val() === "") {
                            return true;
                        }

                        var max = parseFloat($(selector).val() || 0);
                        var len = parseFloat(field.val());
                        if (len > max) {
                            options.allrules.maxField.alertText = options.allrules.maxField.alertTextDefault + max;

                            return false;
                        }

                        return true;
                    },
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_max_alert_text"], "text", $lang));?> ",
                    "alertTextDefault": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_max_alert_text"], "text", $lang));?> "
                },
                "validUserUnicodeName": {
                    "func": function(field, rules, i, options) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        try {
                            if (isIe()) {
                                throw new Error("This browser doesn't properly support Unicode properties in RexExp.");
                            }

                            return new RegExp('^([\\p{L}\\p{N}\\p{Sk}\\p{M}\\-_\\. \']+)$', 'u').test(text);
                        } catch (e) {
                            var isValid = true;
                            $.ajax({
                                url: "validate_ajax_call/ajax_check_name",
                                method: 'post',
                                async: false,
                                cache: true,
                                data: { name: text },
                                dataType: 'json'
                            }).done(function (response) {
                                isValid = 'success' === response.mess_type;
                            });

                            return isValid;
                        }
                    },
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_valid_user_name_unicode_alert_text"], "text", $lang));?>"
                },
                "ifIndustryNotEmpty": {
                    "func": function(field, rules, i, options) {
                        if ($(field).find("option").length > 0 &&
                            $(field).find("option:disabled").length < 4) {

                            return false;
                        }

                        return true;
                    },
                    "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["pre_registration_input_validate_required"], "text", $lang)); ?>"
                },
            },
        <?php } ?>},
        fileUploader: {
        <?php foreach ($langs as $lang) { ?>
    <?php echo $lang;?>: {
                error_exceeded_limit_text: "<?php echo addslashes(translationFileKeyI18n($records["js_fileuploader_error_exceeded_limit_text"], "text", $lang));?>",
                error_format_not_allowed: "<?php echo addslashes(translationFileKeyI18n($records["js_fileuploader_error_format_not_allowed"], "text", $lang));?>",
                error_category_required: "<?php echo addslashes(translationFileKeyI18n($records["js_fileuploader_error_category_required"], "text", $lang));?>",
                error_no_more_files: "<?php echo addslashes(translationFileKeyI18n($records["js_fileuploader_error_no_more_files"], "text", $lang));?>",
                error_malware_text: "<?php echo addslashes(translationFileKeyI18n($records["js_fileuploader_error_malware_text"], "text", $lang));?>",
                error_default: "<?php echo addslashes(translationFileKeyI18n($records["js_fileuploader_error_default"], "text", $lang));?>",
            },
        <?php } ?>},
        general_i18n: {
        <?php foreach ($langs as $lang) { ?>
    <?php echo $lang;?>: {
                form_button_done_text: "<?php echo addslashes(translationFileKeyI18n($records["form_button_done_text"], "text", $lang));?>",
                form_button_submit_text: "<?php echo addslashes(translationFileKeyI18n($records["form_button_submit_text"], "text", $lang));?>",
                form_button_delete_file_text: "<?php echo addslashes(translationFileKeyI18n($records["js_general_form_button_delete_file_text"], "text", $lang));?>",
                form_button_delete_file_title: "<?php echo addslashes(translationFileKeyI18n($records["js_general_form_button_delete_file_title"], "text", $lang));?>",
                form_button_delete_file_message: "<?php echo addslashes(translationFileKeyI18n($records["js_general_form_button_delete_file_message"], "text", $lang));?>",
                form_placeholder_select2_city: "<?php echo addslashes(translationFileKeyI18n($records["form_placeholder_select2_city"], "text", $lang));?>",
                form_placeholder_select2_state_first: "<?php echo addslashes(translationFileKeyI18n($records["form_placeholder_select2_state_first"], "text", $lang));?>",
                form_placeholder_select2_state: "<?php echo addslashes(translationFileKeyI18n($records["form_placeholder_select2_state"], "text", $lang));?>",
                form_placeholder_select2_state_only_first_state: "<?php echo addslashes(translationFileKeyI18n($records["form_placeholder_select2_only_first_state"], "text", $lang));?>",
                system_message_changes_will_come_soon: "<?php echo addslashes(translationFileKeyI18n($records["system_message_changes_will_come_soon"], "text", $lang));?>",
                system_message_server_error_text: "<?php echo addslashes(translationFileKeyI18n($records["js_http_server_error_text"], "text", $lang));?>",
                system_message_error_upload_main_image_text: "<?php echo addslashes(translationFileKeyI18n($records["system_message_error_upload_main_image_text"], "text", $lang));?>",
                system_message_client_error_text: "<?php echo addslashes(translationFileKeyI18n($records["js_http_client_error_text"], "text", $lang));?>",
                systmess_error_should_be_logout: "<?php echo addslashes(translationFileKeyI18n($records["systmess_error_should_be_logout"], "text", $lang));?>",
                systmess_error_should_be_logged_in: "<?php echo addslashes(translationFileKeyI18n($records["systmess_error_should_be_logged_in"], "text", $lang));?>",
                systmess_chat_max_selected_users: "<?php echo addslashes(translationFileKeyI18n($records["systmess_chat_max_selected_users"], "text", $lang));?>",
                systmess_chat_undefined_server_error: "<?php echo addslashes(translationFileKeyI18n($records["systmess_chat_undefined_server_error"], "text", $lang));?>",
                systmess_chat_connection_error: "<?php echo addslashes(translationFileKeyI18n($records["systmess_chat_connection_error"], "text", $lang));?>",
                systmess_chat_bad_request: "<?php echo addslashes(translationFileKeyI18n($records["systmess_chat_bad_request"], "text", $lang));?>",
                systmess_chat_permission_denied: "<?php echo addslashes(translationFileKeyI18n($records["systmess_chat_permission_denied"], "text", $lang));?>",
                systmess_chat_unknown_token: "<?php echo addslashes(translationFileKeyI18n($records["systmess_chat_unknown_token"], "text", $lang));?>",
                systmess_chat_consent_not_given: "<?php echo addslashes(translationFileKeyI18n($records["systmess_chat_consent_not_given"], "text", $lang));?>",
                systmess_chat_nothing_found: "<?php echo addslashes(translationFileKeyI18n($records["systmess_chat_nothing_found"], "text", $lang));?>",
                systmess_chat_limit_exceeded: "<?php echo addslashes(translationFileKeyI18n($records["systmess_chat_limit_exceeded"], "text", $lang));?>",
                systmess_chat_wrong_room_keys_version: "<?php echo addslashes(translationFileKeyI18n($records["systmess_chat_wrong_room_keys_version"], "text", $lang));?>",
                systmess_chat_too_large: "<?php echo addslashes(translationFileKeyI18n($records["systmess_chat_too_large"], "text", $lang));?>",
                systmess_chat_matrix_sdk_timeout: "<?php echo addslashes(translationFileKeyI18n($records["systmess_chat_matrix_sdk_timeout"], "text", $lang));?>",
                systmess_chat_matrix_sdk_missing_param: "<?php echo addslashes(translationFileKeyI18n($records["systmess_chat_matrix_sdk_missing_param"], "text", $lang));?>",
                systmess_chat_restore_backup_error_bad_key: "<?php echo addslashes(translationFileKeyI18n($records["systmess_chat_restore_backup_error_bad_key"], "text", $lang));?>",
                validate_error_message: "<?php echo addslashes(translationFileKeyI18n($records["validate_error_message"], "text", $lang));?>",
                multiple_select_count_selected_industries_placeholder: "<?php echo addslashes(translationFileKeyI18n($records["multiple_select_count_selected_industries_placeholder"], "text", $lang));?>",
                multiple_select_select_industries_and_categories_count_placeholder: "<?php echo addslashes(translationFileKeyI18n($records["multiple_select_select_industries_and_categories_count_placeholder"], "text", $lang));?>",
                multiple_select_select_industry_and_categories_count_placeholder: "<?php echo addslashes(translationFileKeyI18n($records["multiple_select_select_industry_and_categories_count_placeholder"], "text", $lang));?>",
                multiple_select_select_industry_and_category_count_placeholder: "<?php echo addslashes(translationFileKeyI18n($records["multiple_select_select_industry_and_category_count_placeholder"], "text", $lang));?>",
                multiple_select_max_industries: "<?php echo addslashes(translationFileKeyI18n($records["multiple_select_max_industries"], "text", $lang));?>",
                multiple_select_max_industries_by_categories: "<?php echo addslashes(translationFileKeyI18n($records["multiple_select_max_industries_by_categories"], "text", $lang));?>",
                multiple_select_industry_without_categories_msg: "<?php echo addslashes(translationFileKeyI18n($records["multiple_select_industry_without_categories_msg"], "text", $lang));?>",
                seller_home_page_sidebar_menu_dropdown_follow_user: "<?php echo addslashes(translationFileKeyI18n($records["seller_home_page_sidebar_menu_dropdown_follow_user"], "text", $lang));?>",
                seller_home_page_sidebar_menu_dropdown_favorite: "<?php echo addslashes(translationFileKeyI18n($records["seller_home_page_sidebar_menu_dropdown_favorite"], "text", $lang));?>",
                seller_home_page_sidebar_menu_dropdown_favorited: "<?php echo addslashes(translationFileKeyI18n($records["seller_home_page_sidebar_menu_dropdown_favorited"], "text", $lang));?>",
                pre_registration_input_placeholder_country_code: "<?php echo addslashes(translationFileKeyI18n($records["pre_registration_input_placeholder_country_code"], "text", $lang));?>",
                item_card_remove_from_favorites_tag_title: "<?php echo addslashes(translationFileKeyI18n($records["item_card_remove_from_favorites_tag_title"], "text", $lang));?>",
                item_card_add_to_favorites_tag_title: "<?php echo addslashes(translationFileKeyI18n($records["item_card_add_to_favorites_tag_title"], "text", $lang));?>",
                item_card_label_favorite: "<?php echo addslashes(translationFileKeyI18n($records["item_card_label_favorite"], "text", $lang));?>",
                item_card_label_favorited: "<?php echo addslashes(translationFileKeyI18n($records["item_card_label_favorited"], "text", $lang));?>",
                item_card_label_compare: "<?php echo addslashes(translationFileKeyI18n($records["item_card_label_compare"], "text", $lang));?>",
                item_card_label_in_compare: "<?php echo addslashes(translationFileKeyI18n($records["item_card_label_in_compare"], "text", $lang));?>",
                sending_message_form_loader: "<?php echo addslashes(translationFileKeyI18n($records["sending_message_form_loader"], "text", $lang));?>",
                sending_file_form_loader: "<?php echo addslashes(translationFileKeyI18n($records["sending_file_form_loader"], "text", $lang));?>",
                product_requests_success_dialog_title: "<?php echo addslashes(translationFileKeyI18n($records["product_requests_success_dialog_title"], "text", $lang));?>",
                register_label_country_code_placeholder: "<?php echo addslashes(translationFileKeyI18n($records["register_label_country_code_placeholder"], "text", $lang));?>",
                js_reset_password_title: "<?php echo addslashes(translationFileKeyI18n($records["js_reset_password_title"], "text", $lang));?>",
                js_reset_password_text_1: "<?php echo addslashes(translationFileKeyI18n($records["js_reset_password_text_1"], "text", $lang));?>",
                js_reset_text_contact: "<?php echo addslashes(translationFileKeyI18n($records["js_reset_text_contact"], "text", $lang));?>",
                js_reset_password_text_2: "<?php echo addslashes(translationFileKeyI18n($records["js_reset_password_text_2"], "text", $lang));?>",
                js_reset_password_button: "<?php echo addslashes(translationFileKeyI18n($records["js_reset_password_button"], "text", $lang));?>",
                login_add_another_account: "<?php echo addslashes(translationFileKeyI18n($records["login_add_another_account"], "text", $lang));?>",
                login_something_went_wrong_message: "<?php echo addslashes(translationFileKeyI18n($records["login_something_went_wrong_message"], "text", $lang));?>",
                login_changing_word: "<?php echo addslashes(translationFileKeyI18n($records["login_changing_word"], "text", $lang));?>",
                login_clean_session: "<?php echo addslashes(translationFileKeyI18n($records["login_clean_session"], "text", $lang));?>",
                community_word_hide: "<?php echo addslashes(translationFileKeyI18n($records["community_word_hide"], "text", $lang));?>",
                community_word_view: "<?php echo addslashes(translationFileKeyI18n($records["community_word_view"], "text", $lang));?>",
                community_be_the_first_text: "<?php echo addslashes(translationFileKeyI18n($records["community_be_the_first_text"], "text", $lang));?>",
                community_answers_word: "<?php echo addslashes(translationFileKeyI18n($records["community_answers_word"], "text", $lang));?>",
                js_bootstrap_dialog_view_inquiry: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_view_inquiry"], "text", $lang));?>",
                js_bootstrap_dialog_send_estimate: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_send_estimate"], "text", $lang));?>",
                js_bootstrap_dialog_send_offer: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_send_offer"], "text", $lang));?>",
                js_bootstrap_dialog_view_requests: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_view_requests"], "text", $lang));?>",
                js_bootstrap_dialog_view_info: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_view_info"], "text", $lang));?>",
                js_bootstrap_dialog_view_company: "<?php echo addslashes(translationFileKeyI18n($records["js_bootstrap_dialog_view_company"], "text", $lang));?>",
                popup_feedback_subtitle: "<?php echo addslashes(translationFileKeyI18n($records["popup_feedback_subtitle"], "text", $lang)); ?>",
                auth_captcha_not_enabled: "<?php echo addslashes(translationFileKeyI18n($records["auth_captcha_not_enabled"], "text", $lang));?>",
                seller_home_page_sidebar_menu_dropdown_unfollow_user: "<?php echo addslashes(translationFileKeyI18n($records["seller_home_page_sidebar_menu_dropdown_unfollow_user"], "text", $lang));?>",
                order_documents_process_form_recipient_empty_list_warning: "<?php echo addslashes(translationFileKeyI18n($records["order_documents_process_form_recipient_empty_list_warning"], "text", $lang));?>",
                order_documents_process_form_recipient_list_too_much_warning: "<?php echo addslashes(translationFileKeyI18n($records["order_documents_process_form_recipient_list_too_much_warning"], "text", $lang));?>",
                js_select_the_reason_placeholder: "<?php echo addslashes(translationFileKeyI18n($records["js_select_the_reason_placeholder"], "text", $lang));?>",
                js_subscribe_successfully_subscribed_message: "<?php echo addslashes(translationFileKeyI18n($records["js_subscribe_successfully_subscribed_message"], "text", $lang));?>",
                js_dm_successfully_subscribe_message: "<?php echo addslashes(translationFileKeyI18n($records["js_dm_successfully_subscribe_message"], "text", $lang));?>",
                order_documents_due_date_smaller_message: "<?php echo addslashes(translationFileKeyI18n($records["order_documents_due_date_smaller_message"], "text", $lang));?>",
                order_documents_due_date_greater_message: "<?php echo addslashes(translationFileKeyI18n($records["order_documents_due_date_greater_message"], "text", $lang));?>",
                order_documents_due_date_wrong_in_list_message: "<?php echo addslashes(translationFileKeyI18n($records["order_documents_due_date_wrong_in_list_message"], "text", $lang));?>",
                js_popup_certification_update_ttl: "<?php echo addslashes(translationFileKeyI18n($records["js_popup_certification_update_ttl"], "text", $lang));?>",
                js_about_feedback_success_message: "<?php echo addslashes(translationFileKeyI18n($records["js_about_feedback_success_message"], "text", $lang));?>",
                js_error_country_code: "<?php echo addslashes(translationFileKeyI18n($records["js_error_country_code"], "text", $lang));?>",
                js_error_phone_mask: "<?php echo addslashes(translationFileKeyI18n($records["js_error_phone_mask"], "text", $lang));?>",
                js_schedule_a_demo_popup_title: "<?php echo addslashes(translationFileKeyI18n($records["js_schedule_a_demo_popup_title"], "text", $lang));?>",
                js_schedule_a_demo_popup_subtitle: "<?php echo addslashes(translationFileKeyI18n($records["js_schedule_a_demo_popup_subtitle"], "text", $lang));?>",
                add_items_variants_max_options: "<?php echo addslashes(translationFileKeyI18n($records["add_items_variants_max_options"], "text", $lang));?>",
                add_items_variants_max_properties: "<?php echo addslashes(translationFileKeyI18n($records["add_items_variants_max_properties"], "text", $lang));?>",
                add_items_variants_variation_type_required: "<?php echo addslashes(translationFileKeyI18n($records["add_items_variants_variation_type_required"], "text", $lang));?>",
                add_items_variants_options_required: "<?php echo addslashes(translationFileKeyI18n($records["add_items_variants_options_required"], "text", $lang));?>",
                add_items_variants_updated: "<?php echo addslashes(translationFileKeyI18n($records["add_items_variants_updated"], "text", $lang));?>",
                add_items_variants_clear: "<?php echo addslashes(translationFileKeyI18n($records["add_items_variants_clear"], "text", $lang));?>",
                add_items_variants_add_option: "<?php echo addslashes(translationFileKeyI18n($records["add_items_variants_add_option"], "text", $lang));?>",
                add_items_variants_add_valid_price: "<?php echo addslashes(translationFileKeyI18n($records["add_items_variants_add_valid_price"], "text", $lang));?>",
                add_items_variants_add_valid_discount: "<?php echo addslashes(translationFileKeyI18n($records["add_items_variants_add_valid_discount"], "text", $lang));?>",
                add_items_variants_add_valid_quantity: "<?php echo addslashes(translationFileKeyI18n($records["add_items_variants_add_valid_quantity"], "text", $lang));?>",
                add_items_variants_all_options_included: "<?php echo addslashes(translationFileKeyI18n($records["add_items_variants_all_options_included"], "text", $lang));?>",
                add_items_variants_already_included: "<?php echo addslashes(translationFileKeyI18n($records["add_items_variants_already_included"], "text", $lang));?>",
                js_click_to_call_popup_title: "<?php echo addslashes(translationFileKeyI18n($records["js_click_to_call_popup_title"], "text", $lang));?>",
                js_click_to_call_popup_subtitle: "<?php echo addslashes(translationFileKeyI18n($records["js_click_to_call_popup_subtitle"], "text", $lang));?>",
                popup_success_type_default_title: "<?php echo addslashes(translationFileKeyI18n($records["popup_success_type_default_title"], "text", $lang));?>",
                show_more: "<?php echo addslashes(translationFileKeyI18n($records["max_list_show_more"], "text", $lang));?>",
                show_less: "<?php echo addslashes(translationFileKeyI18n($records["max_list_show_less"], "text", $lang));?>",
                read_more: "<?php echo addslashes(translationFileKeyI18n($records["max_text_read_more"], "text", $lang));?>",
                read_less: "<?php echo addslashes(translationFileKeyI18n($records["max_text_read_less"], "text", $lang));?>",
                request_products_success_message: "<?php echo addslashes(translationFileKeyI18n($records["request_products_success_message"], "text", $lang));?>",
                system_message_trying_to_start_chat_while_is_loaded: "<?php echo addslashes(translationFileKeyI18n($records["system_message_trying_to_start_chat_while_is_loaded"], "text", $lang));?>",
                systmess_validation_edit_calendar_notifications_have_dauplicates: "<?php echo addslashes(translationFileKeyI18n($records["systmess_validation_edit_calendar_notifications_have_dauplicates"], "text", $lang));?>",
                blog_search_empty: "<?php echo addslashes(translationFileKeyI18n($records["blog_search_empty"], "text", $lang));?>",
                comments_tree_item_not_found: "<?php echo addslashes(translationFileKeyI18n($records["comments_tree_item_not_found"], "text", $lang));?>",
                comments_tree_button_more: "<?php echo addslashes(translationFileKeyI18n($records["comments_tree_button_more"], "text", $lang));?>",
                js_b2b_popup_register_button: "<?php echo addslashes(translationFileKeyI18n($records["js_b2b_popup_register_button"], "text", $lang));?>",
                js_b2b_popup_sign_in_button: "<?php echo addslashes(translationFileKeyI18n($records["js_b2b_popup_sign_in_button"], "text", $lang));?>",
                calendar_notifacation_settings_choose_type: "<?php echo addslashes(translationFileKeyI18n($records["calendar_notifacation_settings_choose_type"], "text", $lang));?>",
                calendar_notifacation_settings_notification: "<?php echo addslashes(translationFileKeyI18n($records["calendar_notifacation_settings_notification"], "text", $lang));?>",
                calendar_notifacation_settings_email: "<?php echo addslashes(translationFileKeyI18n($records["calendar_notifacation_settings_email"], "text", $lang));?>",
                calendar_notifacation_settings_days_before: "<?php echo addslashes(translationFileKeyI18n($records["calendar_notifacation_settings_days_before"], "text", $lang));?>",
                ep_events_sidebar_go_to_calendar: "<?php echo addslashes(translationFileKeyI18n($records["ep_events_sidebar_go_to_calendar"], "text", $lang));?>",
            },
        <?php } ?>},
        jquery_validation: {
        <?php foreach ($langs as $lang) { ?>
    <?php echo $lang;?>: {
                js_jquery_validation_required_field: "<?php echo addslashes(translationFileKeyI18n($records["js_jquery_validation_required_field"], "text", $lang));?>",
                js_jquery_validation_fields_not_match: "<?php echo addslashes(translationFileKeyI18n($records["js_jquery_validation_fields_not_match"], "text", $lang));?>",
                js_jquery_validation_max_value: "<?php echo addslashes(translationFileKeyI18n($records["js_jquery_validation_max_value"], "text", $lang));?>",
                js_jquery_validation_user_name: "<?php echo addslashes(translationFileKeyI18n($records["js_jquery_validation_user_name"], "text", $lang));?>",
                js_jquery_validation_select_phone_mask: "<?php echo addslashes(translationFileKeyI18n($records["js_jquery_validation_select_phone_mask"], "text", $lang));?>",
                js_jquery_validation_complete_phone_mask: "<?php echo addslashes(translationFileKeyI18n($records["js_jquery_validation_complete_phone_mask"], "text", $lang));?>",
                js_jquery_validation_phone_number: "<?php echo addslashes(translationFileKeyI18n($records["js_jquery_validation_phone_number"], "text", $lang));?>",
                js_jquery_validation_min_size: "<?php echo addslashes(translationFileKeyI18n($records["js_jquery_validation_min_size"], "text", $lang));?>",
                js_jquery_validation_max_size: "<?php echo addslashes(translationFileKeyI18n($records["js_jquery_validation_max_size"], "text", $lang));?>",
                js_jquery_validation_no_whitespaces: "<?php echo addslashes(translationFileKeyI18n($records["js_jquery_validation_no_whitespaces"], "text", $lang));?>",
                js_jquery_validation_email_with_whitespaces: "<?php echo addslashes(translationFileKeyI18n($records["js_jquery_validation_email_with_whitespaces"], "text", $lang));?>",
                js_jquery_validation_company_title: "<?php echo addslashes(translationFileKeyI18n($records["js_jquery_validation_company_title"], "text", $lang));?>",
                js_jquery_validation_natural_number: "<?php echo addslashes(translationFileKeyI18n($records["js_jquery_validation_natural_number"], "text", $lang));?>",
                js_jquery_validation_zip_code: "<?php echo addslashes(translationFileKeyI18n($records["js_jquery_validation_zip_code"], "text", $lang));?>",
            },
        <?php } ?>},
    };
    //translate_js_lang({plug:'bootstrap_tour'});
    //translate_js_one({plug:'bootstrap_tour'});
    //translate_js({plug:'bootstrap_tour', text: 'close_ttl'});
    Object.defineProperty(window, 'translate_js', { writable: false, value: function (names) {
        var plug = names.plug;
        var text = names.text;
        var lang = Object.assign({}, domains[plug][__site_lang]);

        if(lang == undefined || lang == ""){
            lang = domains[plug]['en'];
        }

        if(typeof names.replaces !== 'undefined'){
            for (var prop in names.replaces) {
                lang[text] = lang[text].replace(prop, names.replaces[prop]);
            }
        }

        return lang[text];
    }});
    Object.defineProperty(window, 'translate_js_one', { writable: false, value: function (names) {
        var plug = names.plug;
        var lang = domains[plug];

        if(lang == undefined || lang == ""){
            lang = domains[plug];
        }

        return lang;
    }});
    Object.defineProperty(window, 'translate_js_lang', { writable: false, value: function (names) {
        var plug = names.plug;
        var lang = domains[plug][__site_lang];

        if(lang == undefined || lang == ""){
            lang = domains[plug]['en'];
        }

        return lang;
    }});
    window.__i18n_vocabulary=void 0!==window.__i18n_vocabulary?Object.assign({},window.__i18n_vocabulary||{},domains):domains;
})();
