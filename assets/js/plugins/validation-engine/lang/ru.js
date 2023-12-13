var validation_engine_i18n = {
    "required": { // Add your regex rules here, you can take telephone as an example
        "regex": "none",
        "alertText": "- Необходимо заполнить",
        "alertTextCheckboxMultiple": "* Вы должны выбрать вариант",
        "alertTextCheckboxe": "* Необходимо отметить",
        "alertTextDateRange": "* Требуются оба поля диапазона дат"
    },
    "requiredInFunction": {
        "func": function(field, rules, i, options){
            return (field.val() == "test") ? true : false;
        },
        "alertText": "- Значением поля должно быть test"
    },
    "dateRange": {
        "regex": "none",
        "alertText": "- Недействительный ",
        "alertText2": " Диапазон дат"
    },
    "dateTimeRange": {
        "regex": "none",
        "alertText": "- Недействительный ",
        "alertText2": " Временной диапазон даты"
    },
    "minSize": {
        "regex": "none",
        "alertText": "- Минимум ",
        "alertText2": " символа(ов)"
    },
    "maxSize": {
        "regex": "none",
        "alertText": "- Максимум ",
        "alertText2": " символа(ов)"
    },
    "groupRequired": {
        "regex": "none",
        "alertText": "- Вы должны заполнить одно из следующих полей"
    },
    "min": {
        "regex": "none",
        "alertText": "- Минимальное значение "
    },
    "max": {
        "regex": "none",
        "alertText": "- Максимальное значение "
    },
    "past": {
        "regex": "none",
        "alertText": "- Дата до"
    },
    "future": {
        "regex": "none",
        "alertText": "- Дата от "
    },
    "maxCheckbox": {
        "regex": "none",
        "alertText": "- Нельзя выбрать столько вариантов ",
        "alertText2": " Разрешенные варианты"
    },
    "minCheckbox": {
        "regex": "none",
        "alertText": "- Пожалуйста, выберите ",
        "alertText2": " опцию(ии)"
    },
    "equals": {
        "regex": "none",
        "alertText": "- Поля не совпадают"
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
        "alertText": "- Неверный номер кредитной карты"
    },
    "email": {
        // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
        // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
        "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
        "alertText": "- Неверный формат email"
    },
    "emails": {
        // http://emailregex.com/
        "regex": /^((([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))(\s*[,]{0,1}\s*))+$/,
        "alertText": "- Неверный формат email-ов"
    },
    "maxEmailsCount": {
        "regex": "none",
        "alertText": "- Не может содержать больше, чем ",
        "alertText2": " Адреса email-ов"
    },
    "integer": {
        "regex": /^$|^[\-\+]?\d+$/,
        "alertText": "- Не целое число"
    },
    "positive_integer": {
        "regex": /^$|^\d{1,10}$/,
        "alertText": "- Неправильное положительное число."
    },
    "natural": {
        "regex": /^[1-9][0-9]*$/,
        "alertText": "- Неправильное натуральное число."
    },
    "number": {
        // Number, including positive, negative, and floating decimal. credit: orefalo
        "regex": /^[\-\+]?((([0-9]{1,3})([,][0-9]{3})*)|([0-9]+))?([\.]([0-9]+))?$/,
        "alertText": "- Неправильное число с плавающей точкой"
    },
    "positive_number": {
        // Number, including positive, and floating decimal. credit: orefalo
        "regex": /^$|^(([0]{1})|([1-9]{1}[0-9]{0,11}))(\.([0-9]{1,2}))?$/,
        "alertText": "- Неправильное положительное плавающее / десятичное число"
    },
    "item_size": {
        // Max 9999.99 is working but min 0.01 is not, it also accepts 0 (be careful)
        "regex": /^$|^(([0]{1})|([1-9]{1}[0-9]{0,3}))(\.([0-9]{1,2}))?$/,
        "alertText": "- Неправильное положительное число с плавающей запятой, минимальное 0,01, не более 9999.99"
    },
    "zip_code": {
        "regex": /^$|^[0-9A-Za-z\-\. ]{3,20}$/,
        "alertText": "- Недействительный почтовый индекс"
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
        "alertText": "- Неправильная дата (должно быть в ГГГГ-MM-ДД формате)"
    },
    "ipv4": {
        "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
        "alertText": "- Неправильный IP-адрес"
    },
    "url": {
        "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
        "alertText": "- Неправильный URL"
    },
    "onlyNumberSp": {
        "regex": /^$|^[0-9\ ]+$/,
        "alertText": "- Только числа и пробелы"
    },
    "tariffNumber": {
        "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
        "alertText": "- Harmonized Tariff Number is not correct."
    },
    "onlyNumber": {
        "regex": /^$|^[0-9]$/,
        "alertText": "- Только числа"
    },
    "phoneNumber": {
        "regex": /^$|^[1-9]\d{0,24}$/,
        "alertText": "- Только числа, первый символ не может быть 0, и не может быть больше 25 символов."
    },
    "productTitle": {
        "regex": /^[A-Za-z0-9\/\+\-\_\.\,\:\ \'\;\(\)\#\%]+$/,
        "alertText": "- Разрешены только буквы, цифры, пробелы и символы \'/+-_.,:;()."
    },
    "companyTitle": {
        "regex": /^[A-Za-z0-9][0-9A-Za-z\-\_\.\,\ \'\&\(\)]+$/,
        "alertText": "- Разрешены только буквы, цифры, пробелы и символы -_.,\'&()."
    },
    "validUserName": {
        "regex": /^[a-zA-Z][a-zA-Z\ \'\-]{1,}$/,
        "alertText": "- Разрешены только буквы, пробелы и символы \'_."
    },
    "onlyLetterSp": {
        "regex": /^$|^[a-zA-Z\ \']+$/,
        "alertText": "- Только буквы"
    },
    "onlyLetterNumber": {
        "regex": /^[0-9a-zA-Z]+$/,
        "alertText": "- Запрещены специальные символы"
    },
    "onlyLetterNumberSp": {
        "regex": /^$|^[0-9a-zA-Z\ ]+$/,
        "alertText": "- Только буквы, цифры и пробел"
    },
    "alphaNumeric": {
        "regex": /^([A-Za-z0-9\'\"\-\.\s])+$/i,
        "alertText": "- Разрешены только буквы, цифры и символы\'.-\"."
    },
    "iframe": {
        "regex": /^<iframe.* src=\"(.*)\".*><\/iframe>$/,
        "alertText": "- Недействительный Iframe"
    },
    // --- CUSTOM RULES -- Those are specific to the demos, they can be removed or changed to your likings
    "companyLink": {
        "regex": /^[\w\d\-\_]*$/i,
        "alertText": "- Эта ссылка не соответствует требуемым символам или слово зарезервировано. Только буквы, цифры и \'- _\' символы разрешены. Не менее 5 символов и не более 30 символов."
    },
    "checkPassword": {
        "url": "validate_ajax_call/ajax_check_password",
        //"extraDataDynamic": ['#password'],
        "alertText": "- Этот пароль не защищен!",
        "alertTextOk": "Это надежный пароль.",
        "alertTextLoad": "* Подтверждение, пожалуйста, подождите."
    },
    "checkEmail": {
        "url": "validate_ajax_call/ajax_check_email",
        //"extraDataDynamic": ['#email'],
        "alertText": "- Этот почтовый адрес уже занят!",
        "alertTextOk": "Этот адрес электронной почты доступен.",
        "alertTextLoad": "* Подтверждение, пожалуйста, подождите."
    },
    "validate2fields": {
        "alertText": "- "
    },
    //tls warning:homegrown not fielded
    "dateFormat":{
        "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(?:(?:0?[1-9]|1[0-2])(\/|-)(?:0?[1-9]|1\d|2[0-8]))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(0?2(\/|-)29)(\/|-)(?:(?:0[48]00|[13579][26]00|[2468][048]00)|(?:\d\d)?(?:0[48]|[2468][048]|[13579][26]))$/,
        "alertText": "- Недействительная дата"
    },
    //tls warning:homegrown not fielded
    "dateTimeFormat": {
        "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
        "alertText": "- Недействительный формат даты или даты",
        "alertText2": "Ожидаемый формат: ",
        "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM или ",
        "alertText4": "yyyy-mm-dd hh:mm:ss AM|PM"
    },
    "variableName": {
        "regex": /^[a-z_]+$/,
        "alertText": "- Буквы в нижнем регистре и только \'_\'"
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
        "alertText": "- Неверный формат email"
    },
    "emailsWithWhitespaces": {
        "func": function(field) {
            var text = field.val() || '';
            if ('' === text) {
                return true;
            }

            return /^([\s]*)?((([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))(\s*[,]{0,1}\s*))+([\s]*)?$/.test(text);
        },
        "alertText": "- Неверный формат email-ов"
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
        "alertText": "- Минимальное значение ",
        "alertTextDefault": "- Минимальное значение "
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
        "alertText": "- Максимальное значение ",
        "alertTextDefault": "- Максимальное значение "
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
