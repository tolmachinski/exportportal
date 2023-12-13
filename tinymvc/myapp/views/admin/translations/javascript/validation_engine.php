(function(){var d={validationEngine:{
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

            return key_vals.every(num => key_vals.indexOf(num) === key_vals.lastIndexOf(num));
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
        "regex": /^$|^[0-9]$/,
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
        "regex": /^$|(?:https?:\/\/)?(?:www\.)?(mbasic\.facebook|m\.facebook|facebook|fb)\.(com|me)\/(?:(?:\w\.)*#!\/)?(?:pages\/)?(?:[\w\-\.]*\/)?(?:profile\.php\?id=(?=\d.*))?([\w\-\.]*)/i,
        "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_facebook_link_alert_text"], "text", $lang));?>"
    },
    "valid_twitter_link": {
        "regex": /^$|(?:https?:\/\/)?(?:www\.)?(twitter\.com)\/[a-z0-9\ _]+\/?(\/?(?:\?.*))?$/i,
        "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_twitter_link_alert_text"], "text", $lang));?>"
    },
    "valid_instagram_link": {
        "regex": /^$|(?:https?:\/\/)?(?:www\.)?(instagram\.com|instagr\.am)\/(p\/)?@?([a-z0-9_](?:(?:[a-z0-9_]|(?:\.(?!\.))){0,28}(?:[a-z0-9_]))?)\/?(\/?(?:\?.*))?$/i,
        "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_instagram_link_alert_text"], "text", $lang));?>"
    },
    "valid_linkedin_link": {
        "regex": /^$|(?:https?:\/\/)?(?:www\.)?(linkedin\.com)\/(in\/)?[a-z0-9\ _-]+\/?(\/?(?:\?.*))?$/i,
        "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_linkedin_link_alert_text"], "text", $lang));?>"
    },
    "valid_youtube_link": {
        "regex": /^$|(?:https?:\/\/)?(www|m).youtube.com\/((channel|c)\/)?(?!feed|user\/|watch\?)([a-zA-Z0-9-_.])*.*?$/i,
        "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_youtube_link_alert_text"], "text", $lang));?>"
    },
    "valid_skype_name": {
        "regex": /^$|^[a-zA-Z][a-zA-Z0-9\.,\-_]{5,31}$/i,
        "alertText": "- <?php echo addslashes(translationFileKeyI18n($records["js_validation_engine_skype_name_alert_text"], "text", $lang));?>"
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
            if (null === selector) {
                return;
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
                return;
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
}};window.__i18n_vocabulary=void 0!==window.__i18n_vocabulary?Object.assign({},window.__i18n_vocabulary||{},d):d;})();
