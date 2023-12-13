var validation_engine_i18n = {
    "required": { // Add your regex rules here, you can take telephone as an example
        "regex": "none",
        "alertText": "- هذا الحقل مطلوب",
        "alertTextCheckboxMultiple": "* برجاء إختيار إحدى الخيارات",
        "alertTextCheckboxe": "* هذا المربع الإختياري مطلوب",
        "alertTextDateRange": "* كلا حقلين نطاق التاريخ مطلوبة"
    },
    "requiredInFunction": {
        "func": function(field, rules, i, options){
            return (field.val() == "test") ? true : false;
        },
        "alertText": "- test الحقل يجب أن يساوى"
    },
    "dateRange": {
        "regex": "none",
        "alertText": "- غير صالح ",
        "alertText2": " نطاق التاريخ"
    },
    "dateTimeRange": {
        "regex": "none",
        "alertText": "- غير صالح ",
        "alertText2": " نطاق التاريخ والوقت"
    },
    "minSize": {
        "regex": "none",
        "alertText": "- على الأقل ",
        "alertText2": " حروف مطلوبة"
    },
    "maxSize": {
        "regex": "none",
        "alertText": "- على الأكثر ",
        "alertText2": " حروف مسموحة"
    },
    "groupRequired": {
        "regex": "none",
        "alertText": "- يجب عليك ملئ إحدى الحقول التالية"
    },
    "min": {
        "regex": "none",
        "alertText": "- الحد الأدنى للقيمة هو "
    },
    "max": {
        "regex": "none",
        "alertText": "- الحد الأقصى للقيمة هو "
    },
    "past": {
        "regex": "none",
        "alertText": "- التاريخ قبل"
    },
    "future": {
        "regex": "none",
        "alertText": "- التاريخ بعد "
    },
    "maxCheckbox": {
        "regex": "none",
        "alertText": "- على الأكثر ",
        "alertText2": " خيارات مسموحة"
    },
    "minCheckbox": {
        "regex": "none",
        "alertText": "- برجاء إختيار ",
        "alertText2": " خيارات"
    },
    "equals": {
        "regex": "none",
        "alertText": "- الحقول غير متساوية"
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
        "alertText": "- Must not have duplicate entries"
    },
    "creditCard": {
        "regex": "none",
        "alertText": "- رقم بطاقة الإتمان غير صالح"
    },
    "email": {
        // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
        // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
        "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
        "alertText": "- عنوان بريد إلكتروني غير صالح"
    },
    "emails": {
        // http://emailregex.com/
        "regex": /^((([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))(\s*[,]{0,1}\s*))+$/,
        "alertText": "- One or more email address(es) is not valid"
    },
    "maxEmailsCount": {
        "regex": "none",
        "alertText": "- Cannot contain more than ",
        "alertText2": " email address(es)"
    },
    "integer": {
        "regex": /^$|^[\-\+]?\d+$/,
        "alertText": "- هذا ليس عدد صحيح صالح"
    },
    "positive_integer": {
        "regex": /^$|^\d{1,10}$/,
        "alertText": "- Not a valid positive number."
    },
    "natural": {
        "regex": /^[1-9][0-9]*$/,
        "alertText": "- Numerical digits only."
    },
    "number": {
        // Number, including positive, negative, and floating decimal. credit: orefalo
        "regex": /^[\-\+]?((([0-9]{1,3})([,][0-9]{3})*)|([0-9]+))?([\.]([0-9]+))?$/,
        "alertText": "- عدد عشري غير صالح"
    },
    "positive_number": {
        // Number, including positive, and floating decimal. credit: orefalo
        "regex": /^$|^(([0]{1})|([1-9]{1}[0-9]{0,11}))(\.([0-9]{1,2}))?$/,
        "alertText": "- Not a valid positive floating/decimal number"
    },
    "item_size": {
        // Max 9999.99 is working but min 0.01 is not, it also accepts 0 (be careful)
        "regex": /^$|^(([0]{1})|([1-9]{1}[0-9]{0,3}))(\.([0-9]{1,2}))?$/,
        "alertText": "- Not a valid positive floating/decimal number, min 0.01, max 9999.99"
    },
    "zip_code": {
        "regex": /^$|^[0-9A-Za-z\-\. ]{3,20}$/,
        "alertText": "- Not a valid ZIP code"
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
        "alertText": "- YYYY-MM-DD تاريخ غير صالح، يجب أن يكون في هيئة"
    },
    "ipv4": {
        "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
        "alertText": "- عنوان IP غير صالح"
    },
    "url": {
        "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
        "alertText": "- عنوان إلكتروني غير صالح"
    },
    "onlyNumberSp": {
        "regex": /^$|^[0-9\ ]+$/,
        "alertText": "- أرقام فقط"
    },
    "tariffNumber": {
        "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
        "alertText": "- Harmonized Tariff Number is not correct."
    },
    "onlyNumber": {
        "regex": /^$|^[0-9]$/,
        "alertText": "- Numbers only"
    },
    "phoneNumber": {
        "regex": /^$|^[1-9]\d{0,24}$/,
        "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
    },
    "productTitle": {
        "regex": /^[A-Za-z0-9\/\+\-\_\.\,\:\ \'\;\(\)\#\%]+$/,
        "alertText": "- Only letters, numbers, spaces and \'/+-_.,:\';()#% symbols are allowed."
    },
    "companyTitle": {
        "regex": /^[A-Za-z0-9][0-9A-Za-z\-\_\.\,\ \'\&\(\)]+$/,
        "alertText": "- Only letters, numbers, spaces and -_.,\'&() symbols are allowed."
    },
    "validUserName": {
        "regex": /^[a-zA-Z][a-zA-Z\ \'\-]{1,}$/,
        "alertText": "- Only latin letters, spaces or \' and - characters are allowed."
    },
    "onlyLetterSp": {
        "regex": /^$|^[a-zA-Z\ \']+$/,
        "alertText": "- حروف فقط"
    },
    "onlyLetterNumber": {
        "regex": /^[0-9a-zA-Z]+$/,
        "alertText": "- غير مسموح بحروف خاصة"
    },
    "onlyLetterNumberSp": {
        "regex": /^$|^[0-9a-zA-Z\ ]+$/,
        "alertText": "- Only letters, numbers and space"
    },
    "alphaNumeric": {
        "regex": /^([A-Za-z0-9\'\"\-\.\s])+$/i,
        "alertText": "- Only letters, numbers and \'.-\" symbols are allowed."
    },
    "iframe": {
        "regex": /^<iframe.* src=\"(.*)\".*><\/iframe>$/,
        "alertText": "- Invalid Iframe"
    },
    // --- CUSTOM RULES -- Those are specific to the demos, they can be removed or changed to your likings
    "companyLink": {
        "regex": /^[\w\d\-\_]*$/i,
        "alertText": "- Only letters, numbers and \'- _\' symbols are allowed. Example: my-trade-company-ltd"
    },
    "checkPassword": {
        "url": "validate_ajax_call/ajax_check_password",
        //"extraDataDynamic": ['#password'],
        "alertText": "- This password is not secure!",
        "alertTextOk": "This is strong password.",
        "alertTextLoad": "* Validating, please wait."
    },
    "checkEmail": {
        "url": "validate_ajax_call/ajax_check_email",
        //"extraDataDynamic": ['#email'],
        "alertText": "- This email address is already in use!",
        "alertTextOk": "This email address is available.",
        "alertTextLoad": "* Validating, please wait."
    },
    "validate2fields": {
        "alertText": "- "
    },
    //tls warning:homegrown not fielded
    "dateFormat":{
        "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(?:(?:0?[1-9]|1[0-2])(\/|-)(?:0?[1-9]|1\d|2[0-8]))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(0?2(\/|-)29)(\/|-)(?:(?:0[48]00|[13579][26]00|[2468][048]00)|(?:\d\d)?(?:0[48]|[2468][048]|[13579][26]))$/,
        "alertText": "- تاريخ غير صالح"
    },
    //tls warning:homegrown not fielded
    "dateTimeFormat": {
        "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
        "alertText": "- التاريخ أو هيئة التاريخ غير صالحة",
        "alertText2": "الهيئة المتوقعة: ",
        "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM أو ",
        "alertText4": "yyyy-mm-dd hh:mm:ss AM|PM"
    },
    "variableName": {
        "regex": /^[a-z_]+$/,
        "alertText": "- Letters lower case and \'_\' only"
    },
    "facebook_link": {
        "regex": /^(?:https?:\/\/)?(?:www\.)?(mbasic\.facebook|m\.facebook|facebook|fb)\.(com|me)\/(?:(?:\w\.)*#!\/)?(?:pages\/)?(?:[\w\-\.]*\/)?(?:profile\.php\?id=(?=\d.*))?([\w\-\.]*)/i,
        "alertText": "- Invalid Facebook page link"
    },
    "twitter_link": {
        "regex": /^(?:https?:\/\/)?(?:www\.)?(twitter\.com)\/[a-z0-9\ _]+\/?(\/?(?:\?.*))?$/i,
        "alertText": "- Invalid Twitter page link"
    },
    "instagram_link": {
        "regex": /^(?:https?:\/\/)?(?:www\.)?(instagram\.com|instagr\.am)\/(p\/)?@?([a-z0-9_](?:(?:[a-z0-9_]|(?:\.(?!\.))){0,28}(?:[a-z0-9_]))?)\/?(\/?(?:\?.*))?$/i,
        "alertText": "- Invalid Instagram page link"
    },
    "linkedin_link": {
        "regex": /^(?:https?:\/\/)?(?:www\.)?(linkedin\.com)\/(in\/)?[a-z0-9\ _-]+\/?(\/?(?:\?.*))?$/i,
        "alertText": "- Invalid LinkedIn page link"
    },
    "noTrailingWhitespaces": {
        "func": function(field) {
            var text = field.val() || '';
            if ('' === text) {
                return true;
            }

            return /[ \t]+$/i.test(text) === false;
        },
        "alertText": "- No trailing whitespaces allowed"
    },
    "noWhitespaces": {
        "func": function(field) {
            var text = field.val() || '';
            if ('' === text) {
                return true;
            }

            return text.trim() === text;
        },
        "alertText": "- No leading or trailing whitespaces allowed."
    },
    "emailWithWhitespaces": {
        "func": function(field) {
            var text = field.val() || '';
            if ('' === text) {
                return true;
            }

            return /^([\s]*)?(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))([\s]*)?$/.test(text);
        },
        "alertText": "- عنوان بريد إلكتروني غير صالح"
    },
    "emailsWithWhitespaces": {
        "func": function(field) {
            var text = field.val() || '';
            if ('' === text) {
                return true;
            }

            return /^([\s]*)?((([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))(\s*[,]{0,1}\s*))+([\s]*)?$/.test(text);
        },
        "alertText": "- One or more email address(es) is not valid"
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
        "alertText": "- الحد الأدنى للقيمة هو ",
        "alertTextDefault": "- الحد الأدنى للقيمة هو "
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
        "alertText": "- الحد الأقصى للقيمة هو ",
        "alertTextDefault": "- الحد الأقصى للقيمة هو "
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
        "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
    },
}
