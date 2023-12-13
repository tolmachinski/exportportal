var validation_engine_i18n = {
    "required": { // Add your regex rules here, you can take telephone as an example
        "regex": "none",
        "alertText": "- 此处不可空白",
        "alertTextCheckboxMultiple": "* 请选择一个项目",
        "alertTextCheckboxe": "* 您必须钩选此栏",
        "alertTextDateRange": "* 日期范围不可空白"
    },
    "requiredInFunction": {
        "func": function(field, rules, i, options){
            return (field.val() == "test") ? true : false;
        },
        "alertText": "- Field must equal test"
    },
    "dateRange": {
        "regex": "none",
        "alertText": "- 无效的 ",
        "alertText2": " 日期范围"
    },
    "dateTimeRange": {
        "regex": "none",
        "alertText": "- 无效的 ",
        "alertText2": " 时间范围"
    },
    "minSize": {
        "regex": "none",
        "alertText": "- 最少 ",
        "alertText2": " 个字符"
    },
    "maxSize": {
        "regex": "none",
        "alertText": "- 最多 ",
        "alertText2": " 个字符"
    },
    "groupRequired": {
        "regex": "none",
        "alertText": "- 你必需选填其中一个栏位"
    },
    "min": {
        "regex": "none",
        "alertText": "- 最小值為 "
    },
    "max": {
        "regex": "none",
        "alertText": "- 最大值为 "
    },
    "past": {
        "regex": "none",
        "alertText": "- 日期必需早于"
    },
    "future": {
        "regex": "none",
        "alertText": "- 日期必需晚于 "
    },
    "maxCheckbox": {
        "regex": "none",
        "alertText": "- 最多选取 ",
        "alertText2": " 个项目"
    },
    "minCheckbox": {
        "regex": "none",
        "alertText": "- 请选择 ",
        "alertText2": " 个项目"
    },
    "equals": {
        "regex": "none",
        "alertText": "- 请输入与上面相同的密码"
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
        "alertText": "- 无效的信用卡号码"
    },
    "email": {
        // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
        // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
        "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
        "alertText": "- 无效的电话号码"
    },
    "emails": {
        // http://emailregex.com/
        "regex": /^((([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))(\s*[,]{0,1}\s*))+$/,
        "alertText": "- 一个或多个邮箱地址不可用"
    },
    "maxEmailsCount": {
        "regex": "none",
        "alertText": "- 无法超过 ",
        "alertText2": " 邮箱地址"
    },
    "integer": {
        "regex": /^$|^[\-\+]?\d+$/,
        "alertText": "- 不是有效的整数"
    },
    "positive_integer": {
        "regex": /^$|^\d{1,10}$/,
        "alertText": "- 无效正整数"
    },
    "natural": {
        "regex": /^[1-9][0-9]*$/,
        "alertText": "- 无效自然数"
    },
    "number": {
        // Number, including positive, negative, and floating decimal. credit: orefalo
        "regex": /^[\-\+]?((([0-9]{1,3})([,][0-9]{3})*)|([0-9]+))?([\.]([0-9]+))?$/,
        "alertText": "- 无效的数字"
    },
    "positive_number": {
        // Number, including positive, and floating decimal. credit: orefalo
        "regex": /^$|^(([0]{1})|([1-9]{1}[0-9]{0,11}))(\.([0-9]{1,2}))?$/,
        "alertText": "- 无效正浮动/十进制数字"
    },
    "item_size": {
        // Max 9999.99 is working but min 0.01 is not, it also accepts 0 (be careful)
        "regex": /^$|^(([0]{1})|([1-9]{1}[0-9]{0,3}))(\.([0-9]{1,2}))?$/,
        "alertText": "- 无效正浮动/十进制数字，数字需大于0.01，小于9999.99"
    },
    "zip_code": {
        "regex": /^$|^[0-9A-Za-z\-\. ]{3,20}$/,
        "alertText": "- 无效邮政编码"
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
        "alertText": "- 无效的日期，格式必需为 YYYY-MM-DD"
    },
    "ipv4": {
        "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
        "alertText": "- 无效的 IP 地址"
    },
    "url": {
        "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
        "alertText": "- 无效URL"
    },
    "onlyNumberSp": {
        "regex": /^$|^[0-9\ ]+$/,
        "alertText": "- 只能填数字"
    },
    "tariffNumber": {
        "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
        "alertText": "- Harmonized Tariff Number is not correct."
    },
    "onlyNumber": {
        "regex": /^$|^[0-9]$/,
        "alertText": "- 仅限数字"
    },
    "phoneNumber": {
        "regex": /^$|^[1-9]\d{0,24}$/,
        "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
    },
    "productTitle": {
        "regex": /^[A-Za-z0-9\/\+\-\_\.\,\:\ \'\;\(\)\#\%]+$/,
        "alertText": "- 仅限字母、数字、空格和\'/+-+.,:;()"
    },
    "companyTitle": {
        "regex": /^[A-Za-z0-9][0-9A-Za-z\-\_\.\,\ \'\&\(\)]+$/,
        "alertText": "- 仅限字母、数字、空格和-_.,\'&()"
    },
    "validUserName": {
        "regex": /^[a-zA-Z][a-zA-Z\ \'\-]{1,}$/,
        "alertText": "- 仅字母、数字、空格和\'_"
    },
    "onlyLetterSp": {
        "regex": /^$|^[a-zA-Z\ \']+$/,
        "alertText": "- 只接受英文字母大小写"
    },
    "onlyLetterNumber": {
        "regex": /^[0-9a-zA-Z]+$/,
        "alertText": "- 不接受特殊字符"
    },
    "onlyLetterNumberSp": {
        "regex": /^$|^[0-9a-zA-Z\ ]+$/,
        "alertText": "- 仅字母、数字和空格"
    },
    "alphaNumeric": {
        "regex": /^([A-Za-z0-9\'\"\-\.\s])+$/i,
        "alertText": "- 仅字母、数字和\'.-\""
    },
    "iframe": {
        "regex": /^<iframe.* src=\"(.*)\".*><\/iframe>$/,
        "alertText": "- Invalid Iframe"
    },
    // --- CUSTOM RULES -- Those are specific to the demos, they can be removed or changed to your likings
    "companyLink": {
        "regex": /^[\w\d\-\_]*$/i,
        "alertText": "- 该链接使用了无效字符或字符超过限制。请使用字母、数字和\'-_\'；并不少于5个字符，最多不超过30字符"
    },
    "checkPassword": {
        "url": "validate_ajax_call/ajax_check_password",
        //"extraDataDynamic": ['#password'],
        "alertText": "- 密码安全系数低",
        "alertTextOk": "密码安全系数强",
        "alertTextLoad": "* 认证中，请稍后"
    },
    "checkEmail": {
        "url": "validate_ajax_call/ajax_check_email",
        //"extraDataDynamic": ['#email'],
        "alertText": "- 该邮箱已被使用！",
        "alertTextOk": "该邮箱可用",
        "alertTextLoad": "* 认证中，请稍后"
    },
    "validate2fields": {
        "alertText": "- "
    },
    //tls warning:homegrown not fielded
    "dateFormat":{
        "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(?:(?:0?[1-9]|1[0-2])(\/|-)(?:0?[1-9]|1\d|2[0-8]))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(0?2(\/|-)29)(\/|-)(?:(?:0[48]00|[13579][26]00|[2468][048]00)|(?:\d\d)?(?:0[48]|[2468][048]|[13579][26]))$/,
        "alertText": "- 无效的日期格式"
    },
    //tls warning:homegrown not fielded
    "dateTimeFormat": {
        "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
        "alertText": "- 无效的日期或时间格式",
        "alertText2": "可接受的格式: ",
        "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM 或 ",
        "alertText4": "yyyy-mm-dd hh:mm:ss AM|PM"
    },
    "variableName": {
        "regex": /^[a-z_]+$/,
        "alertText": "- 仅小写字母和“_”"
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
        "alertText": "- 无效的电话号码"
    },
    "emailsWithWhitespaces": {
        "func": function(field) {
            var text = field.val() || '';
            if ('' === text) {
                return true;
            }

            return /^([\s]*)?((([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))(\s*[,]{0,1}\s*))+([\s]*)?$/.test(text);
        },
        "alertText": "- 一个或多个邮箱地址不可用"
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
        "alertText": "- 最小值為 ",
        "alertTextDefault": "- 最小值為 "
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
        "alertText": "- 最大值为 ",
        "alertTextDefault": "- 最大值为 "
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
