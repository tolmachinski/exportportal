function translate_js(names){
    var plug = names.plug;
    var text = names.text;
    var lang = Object.assign({}, translate_js_txt[plug][__site_lang]);

    if(lang == undefined || lang == ""){
        lang = translate_js_txt[plug]['en'];
    }

    if(typeof names.replaces !== 'undefined'){
        for (var prop in names.replaces) {
            lang[text] = lang[text].replace(prop, names.replaces[prop]);
        }
    }

    return lang[text];
}

function translate_js_one(names){
    var plug = names.plug;
    var lang = translate_js_txt[plug];

    if(lang == undefined || lang == ""){
        lang = translate_js_txt[plug];
    }

    return lang;
}

function translate_js_lang(names){
    var plug = names.plug;
    var lang = translate_js_txt[plug][__site_lang];

    if(lang == undefined || lang == ""){
        lang = translate_js_txt[plug]['en'];
    }

    return lang;
}

//translate_js_lang({plug:'bootstrap_tour'});
//translate_js_one({plug:'bootstrap_tour'});
//translate_js({plug:'bootstrap_tour', text: 'close_ttl'});

var translate_js_txt = {
    bootstrap_tour: {
                en : {
                close_ttl: 'Close',
                close_txt: 'Are you sure you want to close this window?',
                btn_got: 'Got it',
                steps_title: 'View video tour',
                steps_content: 'Click here to view step by step actions for order.'
            },
                cn : {
                close_ttl: '关闭',
                close_txt: '确认关闭窗口？',
                btn_got: '确认',
                steps_title: '观看视频之旅',
                steps_content: '点击观看操作流程'
            },
                es : {
                close_ttl: 'Close',
                close_txt: 'Are you sure you want to close this window?',
                btn_got: 'Got it',
                steps_title: 'View video tour',
                steps_content: 'Click here to view step by step actions for order.'
            },
                ru : {
                close_ttl: 'Закрыть',
                close_txt: 'Вы действительно хотите закрыть это окно?',
                btn_got: 'Понял',
                steps_title: 'Посмотреть видео-тур',
                steps_content: 'Нажмите здесь, чтобы просмотреть пошаговые действия для заказа.'
            },
                fr : {
                close_ttl: 'Close',
                close_txt: 'Are you sure you want to close this window?',
                btn_got: 'Got it',
                steps_title: 'View video tour',
                steps_content: 'Click here to view step by step actions for order.'
            },
                de : {
                close_ttl: 'Schließen',
                close_txt: 'Möchten Sie dieses Fenster wirklich schließen?',
                btn_got: 'Verstanden',
                steps_title: 'Lernvideo ansehen',
                steps_content: 'Klicken Sie hier, um die Aktionen der Bestellung Schritt für Schritt anzusehen.'
            },
                it : {
                close_ttl: 'Close',
                close_txt: 'Are you sure you want to close this window?',
                btn_got: 'Got it',
                steps_title: 'View video tour',
                steps_content: 'Click here to view step by step actions for order.'
            },
                ro : {
                close_ttl: 'Close',
                close_txt: 'Are you sure you want to close this window?',
                btn_got: 'Got it',
                steps_title: 'View video tour',
                steps_content: 'Click here to view step by step actions for order.'
            },
                hi : {
                close_ttl: 'Close',
                close_txt: 'Are you sure you want to close this window?',
                btn_got: 'Got it',
                steps_title: 'View video tour',
                steps_content: 'Click here to view step by step actions for order.'
            },
                iw : {
                close_ttl: 'Close',
                close_txt: 'Are you sure you want to close this window?',
                btn_got: 'Got it',
                steps_title: 'View video tour',
                steps_content: 'Click here to view step by step actions for order.'
            },
                vi : {
                close_ttl: 'Close',
                close_txt: 'Are you sure you want to close this window?',
                btn_got: 'Got it',
                steps_title: 'View video tour',
                steps_content: 'Click here to view step by step actions for order.'
            },
                si : {
                close_ttl: 'Close',
                close_txt: 'Are you sure you want to close this window?',
                btn_got: 'Got it',
                steps_title: 'View video tour',
                steps_content: 'Click here to view step by step actions for order.'
            },
                ta : {
                close_ttl: 'Close',
                close_txt: 'Are you sure you want to close this window?',
                btn_got: 'Got it',
                steps_title: 'View video tour',
                steps_content: 'Click here to view step by step actions for order.'
            },
                sq : {
                close_ttl: 'Close',
                close_txt: 'Are you sure you want to close this window?',
                btn_got: 'Got it',
                steps_title: 'View video tour',
                steps_content: 'Click here to view step by step actions for order.'
            },
                ar : {
                close_ttl: 'Close',
                close_txt: 'Are you sure you want to close this window?',
                btn_got: 'Got it',
                steps_title: 'View video tour',
                steps_content: 'Click here to view step by step actions for order.'
            },
                pt : {
                close_ttl: 'Close',
                close_txt: 'Are you sure you want to close this window?',
                btn_got: 'Got it',
                steps_title: 'View video tour',
                steps_content: 'Click here to view step by step actions for order.'
            },
                zh_tw : {
                close_ttl: 'Close',
                close_txt: 'Are you sure you want to close this window?',
                btn_got: 'Got it',
                steps_title: 'View video tour',
                steps_content: 'Click here to view step by step actions for order.'
            },
            },
    hideMaxListItems: {
                en : {
                more_text: 'More >',
                less_text: 'Less >'
            },
                cn : {
                more_text: '多于',
                less_text: '少于'
            },
                es : {
                more_text: 'More >',
                less_text: 'Less >'
            },
                ru : {
                more_text: 'Больше >',
                less_text: 'Меньше >'
            },
                fr : {
                more_text: 'More >',
                less_text: 'Less >'
            },
                de : {
                more_text: 'Mehr dazu >',
                less_text: 'Weniger >'
            },
                it : {
                more_text: 'More >',
                less_text: 'Less >'
            },
                ro : {
                more_text: 'More >',
                less_text: 'Less >'
            },
                hi : {
                more_text: 'More >',
                less_text: 'Less >'
            },
                iw : {
                more_text: 'More >',
                less_text: 'Less >'
            },
                vi : {
                more_text: 'More >',
                less_text: 'Less >'
            },
                si : {
                more_text: 'More >',
                less_text: 'Less >'
            },
                ta : {
                more_text: 'More >',
                less_text: 'Less >'
            },
                sq : {
                more_text: 'More >',
                less_text: 'Less >'
            },
                ar : {
                more_text: 'More >',
                less_text: 'Less >'
            },
                pt : {
                more_text: 'More >',
                less_text: 'Less >'
            },
                zh_tw : {
                more_text: 'More >',
                less_text: 'Less >'
            },
            },
    textcounter: {
                en : {
                count_down_text_before: 'Remaining:',
                count_down_text_after: 'characters'
            },
                cn : {
                count_down_text_before: '剩余',
                count_down_text_after: '字符'
            },
                es : {
                count_down_text_before: 'Remaining:',
                count_down_text_after: 'characters'
            },
                ru : {
                count_down_text_before: 'Осталось:',
                count_down_text_after: 'символов'
            },
                fr : {
                count_down_text_before: 'Remaining:',
                count_down_text_after: 'characters'
            },
                de : {
                count_down_text_before: 'Verbleibende Zeit:',
                count_down_text_after: 'Zeichen'
            },
                it : {
                count_down_text_before: 'Remaining:',
                count_down_text_after: 'characters'
            },
                ro : {
                count_down_text_before: 'Remaining:',
                count_down_text_after: 'characters'
            },
                hi : {
                count_down_text_before: 'Remaining:',
                count_down_text_after: 'characters'
            },
                iw : {
                count_down_text_before: 'Remaining:',
                count_down_text_after: 'characters'
            },
                vi : {
                count_down_text_before: 'Remaining:',
                count_down_text_after: 'characters'
            },
                si : {
                count_down_text_before: 'Remaining:',
                count_down_text_after: 'characters'
            },
                ta : {
                count_down_text_before: 'Remaining:',
                count_down_text_after: 'characters'
            },
                sq : {
                count_down_text_before: 'Remaining:',
                count_down_text_after: 'characters'
            },
                ar : {
                count_down_text_before: 'Remaining:',
                count_down_text_after: 'characters'
            },
                pt : {
                count_down_text_before: 'Remaining:',
                count_down_text_after: 'characters'
            },
                zh_tw : {
                count_down_text_before: 'Remaining:',
                count_down_text_after: 'characters'
            },
            },
    dtFilters: {
                en : {
                btn_reset: 'Reset',
                btn_apply: 'Apply'
            },
                cn : {
                btn_reset: '重置',
                btn_apply: '应用'
            },
                es : {
                btn_reset: 'Reset',
                btn_apply: 'Apply'
            },
                ru : {
                btn_reset: 'Сброс',
                btn_apply: 'Применить'
            },
                fr : {
                btn_reset: 'Reset',
                btn_apply: 'Apply'
            },
                de : {
                btn_reset: 'Zurückstellen',
                btn_apply: 'Sich anwenden'
            },
                it : {
                btn_reset: 'Reset',
                btn_apply: 'Apply'
            },
                ro : {
                btn_reset: 'Reset',
                btn_apply: 'Apply'
            },
                hi : {
                btn_reset: 'Reset',
                btn_apply: 'Apply'
            },
                iw : {
                btn_reset: 'Reset',
                btn_apply: 'Apply'
            },
                vi : {
                btn_reset: 'Reset',
                btn_apply: 'Apply'
            },
                si : {
                btn_reset: 'Reset',
                btn_apply: 'Apply'
            },
                ta : {
                btn_reset: 'Reset',
                btn_apply: 'Apply'
            },
                sq : {
                btn_reset: 'Reset',
                btn_apply: 'Apply'
            },
                ar : {
                btn_reset: 'Reset',
                btn_apply: 'Apply'
            },
                pt : {
                btn_reset: 'Reset',
                btn_apply: 'Apply'
            },
                zh_tw : {
                btn_reset: 'Reset',
                btn_apply: 'Apply'
            },
            },
    multipleSelect: {
                en : {
                placeholder_users: 'Select users',
                placeholder_industries: 'Select industries',
                select_all_text: 'Select all',
                all_selected: 'All selected',
                count_selected: '# of % selected',
                no_matches_found: 'No matches found',
            },
                cn : {
                placeholder_users: '选择用户',
                placeholder_industries: '选择产业',
                select_all_text: '选择全部',
                all_selected: '全选',
                count_selected: '% 已选择',
                no_matches_found: '无匹配结果',
            },
                es : {
                placeholder_users: 'Select users',
                placeholder_industries: 'Select industries',
                select_all_text: 'Select all',
                all_selected: 'All selected',
                count_selected: '# of % selected',
                no_matches_found: 'No matches found',
            },
                ru : {
                placeholder_users: 'Выбрать пользователей',
                placeholder_industries: 'Выберите отрасль промышленности',
                select_all_text: 'Выбрать все',
                all_selected: 'Выбраны все',
                count_selected: '# из % выбраных',
                no_matches_found: 'Совпадений не найдено',
            },
                fr : {
                placeholder_users: 'Select users',
                placeholder_industries: 'Select industries',
                select_all_text: 'Select all',
                all_selected: 'All selected',
                count_selected: '# de % sélectionné',
                no_matches_found: 'Aucun résultat',
            },
                de : {
                placeholder_users: 'Wählen Sie Benutzer aus',
                placeholder_industries: 'Wählen Sie die Branchen aus',
                select_all_text: 'Alles auswählen',
                all_selected: 'Alles ausgewählt',
                count_selected: '# von% ausgewählt',
                no_matches_found: 'Nichts gefunden',
            },
                it : {
                placeholder_users: 'Select users',
                placeholder_industries: 'Select industries',
                select_all_text: 'Select all',
                all_selected: 'All selected',
                count_selected: '# of % selected',
                no_matches_found: 'No matches found',
            },
                ro : {
                placeholder_users: 'Select users',
                placeholder_industries: 'Select industries',
                select_all_text: 'Select all',
                all_selected: 'All selected',
                count_selected: '# of % selected',
                no_matches_found: 'No matches found',
            },
                hi : {
                placeholder_users: 'Select users',
                placeholder_industries: 'Select industries',
                select_all_text: 'Select all',
                all_selected: 'All selected',
                count_selected: '# of % selected',
                no_matches_found: 'No matches found',
            },
                iw : {
                placeholder_users: 'Select users',
                placeholder_industries: 'Select industries',
                select_all_text: 'Select all',
                all_selected: 'All selected',
                count_selected: '# of % selected',
                no_matches_found: 'No matches found',
            },
                vi : {
                placeholder_users: 'Select users',
                placeholder_industries: 'Select industries',
                select_all_text: 'Select all',
                all_selected: 'All selected',
                count_selected: '# of % selected',
                no_matches_found: 'No matches found',
            },
                si : {
                placeholder_users: 'Select users',
                placeholder_industries: 'Select industries',
                select_all_text: 'Select all',
                all_selected: 'All selected',
                count_selected: '# of % selected',
                no_matches_found: 'No matches found',
            },
                ta : {
                placeholder_users: 'Select users',
                placeholder_industries: 'Select industries',
                select_all_text: 'Select all',
                all_selected: 'All selected',
                count_selected: '# of % selected',
                no_matches_found: 'No matches found',
            },
                sq : {
                placeholder_users: 'Select users',
                placeholder_industries: 'Select industries',
                select_all_text: 'Select all',
                all_selected: 'All selected',
                count_selected: '# of % selected',
                no_matches_found: 'No matches found',
            },
                ar : {
                placeholder_users: 'اختر المستخدم',
                placeholder_industries: 'اختر الصناعات',
                select_all_text: 'اختيار الكل',
                all_selected: 'الكل مختار',
                count_selected: '#من %مختار',
                no_matches_found: 'لم يتم العثور على تطابق',
            },
                pt : {
                placeholder_users: 'Selecionar usuários',
                placeholder_industries: 'Selecionar indústrias',
                select_all_text: 'Selecionar tudo',
                all_selected: 'Todos selecionados',
                count_selected: '# de % selecionado',
                no_matches_found: 'Nenhuma equivalência encontrada',
            },
                zh_tw : {
                placeholder_users: 'Select users',
                placeholder_industries: 'Select industries',
                select_all_text: 'Select all',
                all_selected: 'All selected',
                count_selected: '# of % selected',
                no_matches_found: 'No matches found',
            },
            },
    fancybox : {
                en : {
                next: 'Next',
                prev: 'Previous',
                close: 'Close',
                close_message: 'Are you sure you want to close this window?',
                error: 'The requested content cannot be loaded.<br/>Please try again later.',
            },
                cn : {
                next: '下一步',
                prev: '后退',
                close: '关闭',
                close_message: '确认关闭窗口？',
                error: '内容无法载入',
            },
                es : {
                next: 'Next',
                prev: 'Previous',
                close: 'Close',
                close_message: 'Are you sure you want to close this window?',
                error: 'The requested content cannot be loaded.<br/>Please try again later.',
            },
                ru : {
                next: 'Следующий',
                prev: 'Предыдущий',
                close: 'Закрыть',
                close_message: 'Вы уверены что хотите закрыть это окно?',
                error: 'Запрошенный контент не может быть загружен.<br/>Пожалуйста, повторите попытку позже.',
            },
                fr : {
                next: 'Prochain',
                prev: 'Précédent',
                close: 'Ferme',
                close_message: 'Êtes-vous sûr de vouloir fermer cette vitrine?',
                error: 'The requested content cannot be loaded.<br/>Please try again later.',
            },
                de : {
                next: 'Nächste',
                prev: 'Früher',
                close: 'Schließen',
                close_message: 'Möchten Sie dieses Fenster wirklich schließen?',
                error: 'Der angeforderte Inhalt kann nicht geladen werden. <br/> Bitte versuchen Sie es noch einmal später.',
            },
                it : {
                next: 'Next',
                prev: 'Previous',
                close: 'Close',
                close_message: 'Are you sure you want to close this window?',
                error: 'The requested content cannot be loaded.<br/>Please try again later.',
            },
                ro : {
                next: 'Next',
                prev: 'Previous',
                close: 'Close',
                close_message: 'Are you sure you want to close this window?',
                error: 'The requested content cannot be loaded.<br/>Please try again later.',
            },
                hi : {
                next: 'Next',
                prev: 'Previous',
                close: 'Close',
                close_message: 'Are you sure you want to close this window?',
                error: 'The requested content cannot be loaded.<br/>Please try again later.',
            },
                iw : {
                next: 'Next',
                prev: 'Previous',
                close: 'Close',
                close_message: 'Are you sure you want to close this window?',
                error: 'The requested content cannot be loaded.<br/>Please try again later.',
            },
                vi : {
                next: 'Next',
                prev: 'Previous',
                close: 'Close',
                close_message: 'Are you sure you want to close this window?',
                error: 'The requested content cannot be loaded.<br/>Please try again later.',
            },
                si : {
                next: 'Next',
                prev: 'Previous',
                close: 'Close',
                close_message: 'Are you sure you want to close this window?',
                error: 'The requested content cannot be loaded.<br/>Please try again later.',
            },
                ta : {
                next: 'Next',
                prev: 'Previous',
                close: 'Close',
                close_message: 'Are you sure you want to close this window?',
                error: 'The requested content cannot be loaded.<br/>Please try again later.',
            },
                sq : {
                next: 'Next',
                prev: 'Previous',
                close: 'Close',
                close_message: 'Are you sure you want to close this window?',
                error: 'The requested content cannot be loaded.<br/>Please try again later.',
            },
                ar : {
                next: 'Next',
                prev: 'Previous',
                close: 'Close',
                close_message: 'Are you sure you want to close this window?',
                error: 'The requested content cannot be loaded.<br/>Please try again later.',
            },
                pt : {
                next: 'Next',
                prev: 'Previous',
                close: 'Close',
                close_message: 'Are you sure you want to close this window?',
                error: 'The requested content cannot be loaded.<br/>Please try again later.',
            },
                zh_tw : {
                next: 'Next',
                prev: 'Previous',
                close: 'Close',
                close_message: 'Are you sure you want to close this window?',
                error: 'The requested content cannot be loaded.<br/>Please try again later.',
            },
            },
    BootstrapDialog : {
                en : {
                ok: "Ok",
                cancel: "Cancel",
                payment: "Yes, continue payment",
                view_order: "View order",
                close: "Close",
            },
                cn : {
                ok: "确认",
                cancel: "取消",
                payment: "是，继续支付",
                view_order: "View order",
                close: "Close",
            },
                es : {
                ok: "Ok",
                cancel: "Cancel",
                payment: "Yes, continue payment",
                view_order: "View order",
                close: "Close",
            },
                ru : {
                ok: "Да",
                cancel: "Отмена",
                payment: "Да, продолжить оплату",
                view_order: "View order",
                close: "Close",
            },
                fr : {
                ok: "Ok",
                cancel: "Annuler",
                payment: "Oui, continuez le paiement",
                view_order: "View order",
                close: "Close",
            },
                de : {
                ok: "Ok",
                cancel: "Stornieren",
                payment: "Ja, die Zahlung fortsetzen",
                view_order: "View order",
                close: "Close",
            },
                it : {
                ok: "Ok",
                cancel: "Cancel",
                payment: "Yes, continue payment",
                view_order: "View order",
                close: "Close",
            },
                ro : {
                ok: "Ok",
                cancel: "Cancel",
                payment: "Yes, continue payment",
                view_order: "View order",
                close: "Close",
            },
                hi : {
                ok: "Ok",
                cancel: "Cancel",
                payment: "Yes, continue payment",
                view_order: "View order",
                close: "Close",
            },
                iw : {
                ok: "Ok",
                cancel: "Cancel",
                payment: "Yes, continue payment",
                view_order: "View order",
                close: "Close",
            },
                vi : {
                ok: "Ok",
                cancel: "Cancel",
                payment: "Yes, continue payment",
                view_order: "View order",
                close: "Close",
            },
                si : {
                ok: "Ok",
                cancel: "Cancel",
                payment: "Yes, continue payment",
                view_order: "View order",
                close: "Close",
            },
                ta : {
                ok: "Ok",
                cancel: "Cancel",
                payment: "Yes, continue payment",
                view_order: "View order",
                close: "Close",
            },
                sq : {
                ok: "Ok",
                cancel: "Cancel",
                payment: "Yes, continue payment",
                view_order: "View order",
                close: "Close",
            },
                ar : {
                ok: "Ok",
                cancel: "Cancel",
                payment: "Yes, continue payment",
                view_order: "View order",
                close: "Close",
            },
                pt : {
                ok: "Ok",
                cancel: "Cancel",
                payment: "Yes, continue payment",
                view_order: "View order",
                close: "Close",
            },
                zh_tw : {
                ok: "Ok",
                cancel: "Cancel",
                payment: "Yes, continue payment",
                view_order: "View order",
                close: "Close",
            },
            },
    pwstrength : {
                en : {
                wordLowercase: "At least one lowercase character",
                wordUppercase: "At least one uppercase character;",
                wordOneNumber: "At least one number",
                wordMinLength: "Your password is too short",
                wordMaxLength: "Your password is too long",
                wordInvalidChar: "Your password contains an invalid character",
                wordNotEmail: "Do not use your email as your password",
                wordSimilarToUsername: "Your password cannot contain your username",
                wordTwoCharacterClasses: "Use different character classes",
                wordRepetitions: "Too many repetitions",
                wordSequences: "Your password contains sequences",
                errorList: "Errors:",
                veryWeak: "Very Weak",
                weak: "Weak",
                normal: "Normal",
                medium: "Medium",
                strong: "Strong",
                veryStrong: "Very Strong"
            },
                cn : {
                wordLowercase: "至少一个小写字母",
                wordUppercase: "至少一个大写字母",
                wordOneNumber: "至少一个数字",
                wordMinLength: "您设置的密码过短",
                wordMaxLength: "您设置的密码过长",
                wordInvalidChar: "您的密码设置了不可使用的字符",
                wordNotEmail: "请勿将您的邮箱地址设为密码",
                wordSimilarToUsername: "密码不可包含用户名",
                wordTwoCharacterClasses: "请包含大小写字母",
                wordRepetitions: "操作次数过多",
                wordSequences: "您的密码包含序列数字",
                errorList: "错误",
                veryWeak: "太弱",
                weak: "弱",
                normal: "一般",
                medium: "中等",
                strong: "强",
                veryStrong: "非常强"
            },
                es : {
                wordLowercase: "At least one lowercase character",
                wordUppercase: "At least one uppercase character;",
                wordOneNumber: "At least one number",
                wordMinLength: "Your password is too short",
                wordMaxLength: "Your password is too long",
                wordInvalidChar: "Your password contains an invalid character",
                wordNotEmail: "Do not use your email as your password",
                wordSimilarToUsername: "Your password cannot contain your username",
                wordTwoCharacterClasses: "Use different character classes",
                wordRepetitions: "Too many repetitions",
                wordSequences: "Your password contains sequences",
                errorList: "Errors:",
                veryWeak: "Very Weak",
                weak: "Weak",
                normal: "Normal",
                medium: "Medium",
                strong: "Strong",
                veryStrong: "Very Strong"
            },
                ru : {
                wordLowercase: "Kак минимум одну строчную букву",
                wordUppercase: "Kак минимум одну заглавную букву",
                wordOneNumber: "Kак минимум одну цифру",
                wordMinLength: "Слишком короткий пароль",
                wordMaxLength: "Ваш пароль слишком длинный",
                wordInvalidChar: "Ваш пароль содержит недопустимый символ",
                wordNotEmail: "Не используйте e-mail в качестве пароля",
                wordSimilarToUsername: "Пароль не должен содержать логин",
                wordTwoCharacterClasses: "Используйте разные классы символов",
                wordRepetitions: "Слишком много повторений",
                wordSequences: "Пароль содержит последовательности",
                errorList: "Ошибки:",
                veryWeak: "Очень слабый",
                weak: "Слабый",
                normal: "Нормальный",
                medium: "Средний",
                strong: "Сильный",
                veryStrong: "Очень сильный"
            },
                fr : {
                wordLowercase: "Au moins un caractère minuscule",
                wordUppercase: "Au moins un caractère majuscule;",
                wordOneNumber: "Au moins un numéro",
                wordMinLength: "Votre mot de passe est trop court",
                wordMaxLength: "Votre mot de passe est trop long",
                wordInvalidChar: "Votre mot de passe contient un caractère invalide",
                wordNotEmail: "Do not use your email as your password",
                wordSimilarToUsername: "Your password cannot contain your username",
                wordTwoCharacterClasses: "Utilise différentes classes de caractères",
                wordRepetitions: "Trop de répétitions",
                wordSequences: "Votre mot de passe contient des séquences",
                errorList: "Les erreurs:",
                veryWeak: "Très faible",
                weak: "Weak",
                normal: "Ordinaire",
                medium: "Médium",
                strong: "Fort",
                veryStrong: "Très fort"
            },
                de : {
                wordLowercase: "Mindestens ein Kleinbuchstabe",
                wordUppercase: "Mindestens ein Großbuchstabe;",
                wordOneNumber: "Mindestens eine Nummer",
                wordMinLength: "Ihr Kennwort ist zu kurz",
                wordMaxLength: "Ihr Kennwort ist zu lang",
                wordInvalidChar: "Ihr Kennwort enthält ein ungültiges Zeichen",
                wordNotEmail: "Benutzen Sie Ihre E-Mail nicht als Ihr Kennwort",
                wordSimilarToUsername: "Ihr Kennwort darf Ihren Nutzernamen nicht enthalten",
                wordTwoCharacterClasses: "Verwenden Sie unterschiedliche Zeichenklassen",
                wordRepetitions: "Zu viele Wiederholungen",
                wordSequences: "Ihr Kennwort enthält Reihenfolge",
                errorList: "Fehler:",
                veryWeak: "Sehr schwach",
                weak: "Schwach",
                normal: "Normal",
                medium: "Medium",
                strong: "Stark",
                veryStrong: "Sehr stark"
            },
                it : {
                wordLowercase: "At least one lowercase character",
                wordUppercase: "At least one uppercase character;",
                wordOneNumber: "At least one number",
                wordMinLength: "Your password is too short",
                wordMaxLength: "Your password is too long",
                wordInvalidChar: "Your password contains an invalid character",
                wordNotEmail: "Do not use your email as your password",
                wordSimilarToUsername: "Your password cannot contain your username",
                wordTwoCharacterClasses: "Use different character classes",
                wordRepetitions: "Too many repetitions",
                wordSequences: "Your password contains sequences",
                errorList: "Errors:",
                veryWeak: "Very Weak",
                weak: "Weak",
                normal: "Normal",
                medium: "Medium",
                strong: "Strong",
                veryStrong: "Very Strong"
            },
                ro : {
                wordLowercase: "At least one lowercase character",
                wordUppercase: "At least one uppercase character;",
                wordOneNumber: "At least one number",
                wordMinLength: "Your password is too short",
                wordMaxLength: "Your password is too long",
                wordInvalidChar: "Your password contains an invalid character",
                wordNotEmail: "Do not use your email as your password",
                wordSimilarToUsername: "Your password cannot contain your username",
                wordTwoCharacterClasses: "Use different character classes",
                wordRepetitions: "Too many repetitions",
                wordSequences: "Your password contains sequences",
                errorList: "Errors:",
                veryWeak: "Very Weak",
                weak: "Weak",
                normal: "Normal",
                medium: "Medium",
                strong: "Strong",
                veryStrong: "Very Strong"
            },
                hi : {
                wordLowercase: "At least one lowercase character",
                wordUppercase: "At least one uppercase character;",
                wordOneNumber: "At least one number",
                wordMinLength: "Your password is too short",
                wordMaxLength: "Your password is too long",
                wordInvalidChar: "Your password contains an invalid character",
                wordNotEmail: "Do not use your email as your password",
                wordSimilarToUsername: "Your password cannot contain your username",
                wordTwoCharacterClasses: "Use different character classes",
                wordRepetitions: "Too many repetitions",
                wordSequences: "Your password contains sequences",
                errorList: "Errors:",
                veryWeak: "Very Weak",
                weak: "Weak",
                normal: "Normal",
                medium: "Medium",
                strong: "Strong",
                veryStrong: "Very Strong"
            },
                iw : {
                wordLowercase: "At least one lowercase character",
                wordUppercase: "At least one uppercase character;",
                wordOneNumber: "At least one number",
                wordMinLength: "Your password is too short",
                wordMaxLength: "Your password is too long",
                wordInvalidChar: "Your password contains an invalid character",
                wordNotEmail: "Do not use your email as your password",
                wordSimilarToUsername: "Your password cannot contain your username",
                wordTwoCharacterClasses: "Use different character classes",
                wordRepetitions: "Too many repetitions",
                wordSequences: "Your password contains sequences",
                errorList: "Errors:",
                veryWeak: "Very Weak",
                weak: "Weak",
                normal: "Normal",
                medium: "Medium",
                strong: "Strong",
                veryStrong: "Very Strong"
            },
                vi : {
                wordLowercase: "At least one lowercase character",
                wordUppercase: "At least one uppercase character;",
                wordOneNumber: "At least one number",
                wordMinLength: "Your password is too short",
                wordMaxLength: "Your password is too long",
                wordInvalidChar: "Your password contains an invalid character",
                wordNotEmail: "Do not use your email as your password",
                wordSimilarToUsername: "Your password cannot contain your username",
                wordTwoCharacterClasses: "Use different character classes",
                wordRepetitions: "Too many repetitions",
                wordSequences: "Your password contains sequences",
                errorList: "Errors:",
                veryWeak: "Very Weak",
                weak: "Weak",
                normal: "Normal",
                medium: "Medium",
                strong: "Strong",
                veryStrong: "Very Strong"
            },
                si : {
                wordLowercase: "At least one lowercase character",
                wordUppercase: "At least one uppercase character;",
                wordOneNumber: "At least one number",
                wordMinLength: "Your password is too short",
                wordMaxLength: "Your password is too long",
                wordInvalidChar: "Your password contains an invalid character",
                wordNotEmail: "Do not use your email as your password",
                wordSimilarToUsername: "Your password cannot contain your username",
                wordTwoCharacterClasses: "Use different character classes",
                wordRepetitions: "Too many repetitions",
                wordSequences: "Your password contains sequences",
                errorList: "Errors:",
                veryWeak: "Very Weak",
                weak: "Weak",
                normal: "Normal",
                medium: "Medium",
                strong: "Strong",
                veryStrong: "Very Strong"
            },
                ta : {
                wordLowercase: "At least one lowercase character",
                wordUppercase: "At least one uppercase character;",
                wordOneNumber: "At least one number",
                wordMinLength: "Your password is too short",
                wordMaxLength: "Your password is too long",
                wordInvalidChar: "Your password contains an invalid character",
                wordNotEmail: "Do not use your email as your password",
                wordSimilarToUsername: "Your password cannot contain your username",
                wordTwoCharacterClasses: "Use different character classes",
                wordRepetitions: "Too many repetitions",
                wordSequences: "Your password contains sequences",
                errorList: "Errors:",
                veryWeak: "Very Weak",
                weak: "Weak",
                normal: "Normal",
                medium: "Medium",
                strong: "Strong",
                veryStrong: "Very Strong"
            },
                sq : {
                wordLowercase: "At least one lowercase character",
                wordUppercase: "At least one uppercase character;",
                wordOneNumber: "At least one number",
                wordMinLength: "Your password is too short",
                wordMaxLength: "Your password is too long",
                wordInvalidChar: "Your password contains an invalid character",
                wordNotEmail: "Do not use your email as your password",
                wordSimilarToUsername: "Your password cannot contain your username",
                wordTwoCharacterClasses: "Use different character classes",
                wordRepetitions: "Too many repetitions",
                wordSequences: "Your password contains sequences",
                errorList: "Errors:",
                veryWeak: "Very Weak",
                weak: "Weak",
                normal: "Normal",
                medium: "Medium",
                strong: "Strong",
                veryStrong: "Very Strong"
            },
                ar : {
                wordLowercase: "At least one lowercase character",
                wordUppercase: "At least one uppercase character;",
                wordOneNumber: "At least one number",
                wordMinLength: "Your password is too short",
                wordMaxLength: "Your password is too long",
                wordInvalidChar: "Your password contains an invalid character",
                wordNotEmail: "Do not use your email as your password",
                wordSimilarToUsername: "Your password cannot contain your username",
                wordTwoCharacterClasses: "Use different character classes",
                wordRepetitions: "Too many repetitions",
                wordSequences: "Your password contains sequences",
                errorList: "Errors:",
                veryWeak: "Very Weak",
                weak: "Weak",
                normal: "Normal",
                medium: "Medium",
                strong: "Strong",
                veryStrong: "Very Strong"
            },
                pt : {
                wordLowercase: "At least one lowercase character",
                wordUppercase: "At least one uppercase character;",
                wordOneNumber: "At least one number",
                wordMinLength: "Your password is too short",
                wordMaxLength: "Your password is too long",
                wordInvalidChar: "Your password contains an invalid character",
                wordNotEmail: "Do not use your email as your password",
                wordSimilarToUsername: "Your password cannot contain your username",
                wordTwoCharacterClasses: "Use different character classes",
                wordRepetitions: "Too many repetitions",
                wordSequences: "Your password contains sequences",
                errorList: "Errors:",
                veryWeak: "Very Weak",
                weak: "Weak",
                normal: "Normal",
                medium: "Medium",
                strong: "Strong",
                veryStrong: "Very Strong"
            },
                zh_tw : {
                wordLowercase: "At least one lowercase character",
                wordUppercase: "At least one uppercase character;",
                wordOneNumber: "At least one number",
                wordMinLength: "Your password is too short",
                wordMaxLength: "Your password is too long",
                wordInvalidChar: "Your password contains an invalid character",
                wordNotEmail: "Do not use your email as your password",
                wordSimilarToUsername: "Your password cannot contain your username",
                wordTwoCharacterClasses: "Use different character classes",
                wordRepetitions: "Too many repetitions",
                wordSequences: "Your password contains sequences",
                errorList: "Errors:",
                veryWeak: "Very Weak",
                weak: "Weak",
                normal: "Normal",
                medium: "Medium",
                strong: "Strong",
                veryStrong: "Very Strong"
            },
            },
    validationEngine : {
                    en : {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "- This field is required",
                    "alertTextCheckboxMultiple": "* Please select an option",
                    "alertTextCheckboxe": "* This option is required",
                    "alertTextDateRange": "* Both date range fields are required"
                },
                "requiredInFunction": {
                    "func": function(field, rules, i, options){
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": "- Field must equal test"
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Range"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Time Range"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "- Require minimum ",
                    "alertText2": " characters"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " characters allowed"
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": "- You must fill one of the following fields"
                },
                "min": {
                    "regex": "none",
                    "alertText": "- Minimum value is "
                },
                "max": {
                    "regex": "none",
                    "alertText": "- Maximum value is "
                },
                "past": {
                    "regex": "none",
                    "alertText": "- Date prior to"
                },
                "future": {
                    "regex": "none",
                    "alertText": "- Date past "
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " options allowed"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "- Please select ",
                    "alertText2": " options"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "- Fields do not match"
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
                    "alertText": "- Must not have duplicate entries"
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": "- Credit card number is not valid"
                },
                "email": {
                    // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
                    "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Not a valid number."
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
                    "alertText": "- Invalid floating/decimal number"
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
                    "alertText": "- Invalid date, must be in YYYY-MM-DD format"
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "- Invalid IP address"
                },
                "url": {
                    "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
                    "alertText": "- The link you provided does not seem as valid. Example: https://www.example.com"
                },
                "onlyNumberSp": {
                    "regex": /^$|^[0-9\ ]+$/,
                    "alertText": "- Numbers and spaces only"
                },
                "tariffNumber": {
                    "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
                    "alertText": "- Harmonized Tariff Number is not correct."
                },
                "onlyNumber": {
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Numbers only"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Only letters, numbers, spaces and \'/+-_.,:\';()#% symbols are allowed."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Only letters, numbers, spaces and -_.,\'&() symbols are allowed."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
                    "alertText": "- Only latin letters, spaces or \' and - characters are allowed."
                },
                "onlyLetterSp": {
                    "regex": /^$|^[a-zA-Z\ \']+$/,
                    "alertText": "- Letters only"
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "- No special characters allowed"
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
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- This link does not match the required characters or the domain link is already taken. Only letters, numbers and \'- _ .\' symbols are allowed. Characters should be more than 5 and less than 30. <br>Example: my-trade-company-ltd",
                    "alertTextOk": "This link is good.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- This password is not secure!",
                    "alertTextOk": "This is strong password.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
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
                    "alertText": "- Invalid Date"
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "- Invalid Date or Date Format",
                    "alertText2": "Expected Format: ",
                    "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM or ",
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Minimum value is ",
                    "alertTextDefault": "- Minimum value is "
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
                    "alertText": "- Maximum value is ",
                    "alertTextDefault": "- Maximum value is "
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    cn : {
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

                        return key_vals.every(function (num, index) { return index === key_vals.lastIndexOf(num) });
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
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- 仅限数字"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- 仅限字母、数字、空格和\'/+-+.,:;()"
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- 仅限字母、数字、空格和-_.,\'&()"
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
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
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- 该链接使用了无效字符或字符超过限制。请使用字母、数字和\'-_.,\'；并不少于5个字符，最多不超过30字符",
                    "alertTextOk": "链接可用",
                    "alertTextLoad": "* 认证中，请稍后"
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- 密码安全系数低",
                    "alertTextOk": "密码安全系数强",
                    "alertTextLoad": "* 认证中，请稍后"
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    es : {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "- Este campo es obligatorio",
                    "alertTextCheckboxMultiple": "* Por favor seleccione una opción",
                    "alertTextCheckboxe": "* Este checkbox es obligatorio",
                    "alertTextDateRange": "* Both date range fields are required"
                },
                "requiredInFunction": {
                    "func": function(field, rules, i, options){
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": "- Field must equal test"
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Range"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Time Range"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "- Mínimo de ",
                    "alertText2": " caracteres autorizados"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " characters allowed"
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": "- Debe de rellenar al menos uno de los siguientes campos"
                },
                "min": {
                    "regex": "none",
                    "alertText": "- El valor mínimo es "
                },
                "max": {
                    "regex": "none",
                    "alertText": "- El valor máximo es "
                },
                "past": {
                    "regex": "none",
                    "alertText": "- Fecha anterior a"
                },
                "future": {
                    "regex": "none",
                    "alertText": "- Fecha posterior a "
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " Se ha excedido el número de opciones permitidas"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "- Por favor seleccione ",
                    "alertText2": " opciones"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "- Los campos no coinciden"
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
                    "alertText": "- Must not have duplicate entries"
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": "- La tarjeta de crédito no es válida"
                },
                "email": {
                    // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
                    "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": "- Correo inválido"
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
                    "alertText": "- No es un valor entero válido"
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
                    "alertText": "- No es un valor decimal válido"
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
                    "alertText": "- Fecha inválida, por favor utilize el formato DD/MM/AAAA"
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "- Direccion IP inválida"
                },
                "url": {
                    "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
                    "alertText": "- URL Inválida"
                },
                "onlyNumberSp": {
                    "regex": /^$|^[0-9\ ]+$/,
                    "alertText": "- Sólo números"
                },
                "tariffNumber": {
                    "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
                    "alertText": "- Harmonized Tariff Number is not correct."
                },
                "onlyNumber": {
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Numbers only"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Only letters, numbers, spaces and \'/+-_.,:\';()#% symbols are allowed."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Only letters, numbers, spaces and -_.,\'&() symbols are allowed."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
                    "alertText": "- Only latin letters, spaces or \' and - characters are allowed."
                },
                "onlyLetterSp": {
                    "regex": /^$|^[a-zA-Z\ \']+$/,
                    "alertText": "- Sólo letras"
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "- No se permiten caracteres especiales"
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
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- This link does not match the required characters or the domain link is already taken. Only letters, numbers and \'- _ .\' symbols are allowed. Characters should be more than 5 and less than 30. <br>Example: my-trade-company-ltd",
                    "alertTextOk": "This link is good.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- This password is not secure!",
                    "alertTextOk": "This is strong password.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
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
                    "alertText": "- Invalid Date"
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "- Invalid Date or Date Format",
                    "alertText2": "Expected Format: ",
                    "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM or ",
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Correo inválido"
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
                    "alertText": "- El valor mínimo es ",
                    "alertTextDefault": "- El valor mínimo es "
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
                    "alertText": "- El valor máximo es ",
                    "alertTextDefault": "- El valor máximo es "
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    ru : {
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

                        return key_vals.every(function (num, index) { return index === key_vals.lastIndexOf(num) });
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
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Только числа"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Только числа, первый символ не может быть 0, и не может быть больше 25 символов."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Разрешены только буквы, цифры, пробелы и символы \'/+-_.,:;()."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Разрешены только буквы, цифры, пробелы и символы -_.,\'&()."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
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
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- Эта ссылка не соответствует требуемым символам или слово зарезервировано. Только буквы, цифры и \'- _.\' символы разрешены. Не менее 5 символов и не более 30 символов.",
                    "alertTextOk": "Эта ссылка хороша.",
                    "alertTextLoad": "* Подтверждение, пожалуйста, подождите."
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- Этот пароль не защищен!",
                    "alertTextOk": "Это надежный пароль.",
                    "alertTextLoad": "* Подтверждение, пожалуйста, подождите."
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    fr : {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "- This field is required",
                    "alertTextCheckboxMultiple": "* Please select an option",
                    "alertTextCheckboxe": "* This option is required",
                    "alertTextDateRange": "* Both date range fields are required"
                },
                "requiredInFunction": {
                    "func": function(field, rules, i, options){
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": "- Field must equal test"
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": "- Non valide ",
                    "alertText2": " Date Range"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": "- Non valide ",
                    "alertText2": " Date Time Range"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "- Require minimum ",
                    "alertText2": " characters"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " characters allowed"
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": "- You must fill one of the following fields"
                },
                "min": {
                    "regex": "none",
                    "alertText": "- Minimum value is "
                },
                "max": {
                    "regex": "none",
                    "alertText": "- Maximum value is "
                },
                "past": {
                    "regex": "none",
                    "alertText": "- Date prior to"
                },
                "future": {
                    "regex": "none",
                    "alertText": "- Date past "
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " options allowed"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "- Please select ",
                    "alertText2": " options"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "- Fields do not match"
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
                    "alertText": "- Must not have duplicate entries"
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": "- Credit card number is not valid"
                },
                "email": {
                    // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
                    "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Not a valid number."
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
                    "alertText": "- Invalid floating/decimal number"
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
                    "alertText": "- Invalid date, must be in YYYY-MM-DD format"
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "- Invalid IP address"
                },
                "url": {
                    "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
                    "alertText": "- The link you provided does not seem as valid. Example: https://www.example.com"
                },
                "onlyNumberSp": {
                    "regex": /^$|^[0-9\ ]+$/,
                    "alertText": "- Numbers and spaces only"
                },
                "tariffNumber": {
                    "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
                    "alertText": "- Harmonized Tariff Number is not correct."
                },
                "onlyNumber": {
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Seulement les chiffres"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Only letters, numbers, spaces and \'/+-_.,:\';()#% symbols are allowed."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Only letters, numbers, spaces and -_.,\'&() symbols are allowed."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
                    "alertText": "- Only latin letters, spaces or \' and - characters are allowed."
                },
                "onlyLetterSp": {
                    "regex": /^$|^[a-zA-Z\ \']+$/,
                    "alertText": "- Seulement des lettres"
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "- Pas de caractères spéciaux autorisés"
                },
                "onlyLetterNumberSp": {
                    "regex": /^$|^[0-9a-zA-Z\ ]+$/,
                    "alertText": "- Seulement des lettres, des chiffres et des espaces"
                },
                "alphaNumeric": {
                    "regex": /^([A-Za-z0-9\'\"\-\.\s])+$/i,
                    "alertText": "- Only letters, numbers and \'.-\" symbols are allowed."
                },
                "iframe": {
                    "regex": /^<iframe.* src=\"(.*)\".*><\/iframe>$/,
                    "alertText": "- Iframe invalide"
                },
                // --- CUSTOM RULES -- Those are specific to the demos, they can be removed or changed to your likings
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- This link does not match the required characters or the domain link is already taken. Only letters, numbers and \'- _ .\' symbols are allowed. Characters should be more than 5 and less than 30. <br>Example: my-trade-company-ltd",
                    "alertTextOk": "Ce lien est bon.",
                    "alertTextLoad": "* C\'est valider, s\'il vous plaît, attendez."
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- Ce mot de passe n\'est pas sécurisé!",
                    "alertTextOk": "C\'est un mot de passe fort.",
                    "alertTextLoad": "* C\'est valider, s\'il vous plaît, attendez."
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
                    "alertText": "- Cette adresse email est déjà utilisée!",
                    "alertTextOk": "Cette adresse email est disponible.",
                    "alertTextLoad": "* C\'est valider, s\'il vous plaît, attendez."
                },
                "validate2fields": {
                    "alertText": "- "
                },
                //tls warning:homegrown not fielded
                "dateFormat":{
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(?:(?:0?[1-9]|1[0-2])(\/|-)(?:0?[1-9]|1\d|2[0-8]))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(0?2(\/|-)29)(\/|-)(?:(?:0[48]00|[13579][26]00|[2468][048]00)|(?:\d\d)?(?:0[48]|[2468][048]|[13579][26]))$/,
                    "alertText": "- Date invalide"
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "- Format de date ou le date est invalide",
                    "alertText2": "Format attendu: ",
                    "alertText3": "mm / jj / aaaa hh: mm: ss AM | PM ou ",
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Minimum value is ",
                    "alertTextDefault": "- Minimum value is "
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
                    "alertText": "- Maximum value is ",
                    "alertTextDefault": "- Maximum value is "
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    de : {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "- Dieses Feld ist erforderlich",
                    "alertTextCheckboxMultiple": "* Bitte wählen Sie eine Option",
                    "alertTextCheckboxe": "* Diese Option ist erforderlich",
                    "alertTextDateRange": "* Beide Datumsbereichsfelder sind erforderlich"
                },
                "requiredInFunction": {
                    "func": function(field, rules, i, options){
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": "- Das Feld muss dem Kriterium entsprechen"
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": "- Ungültig ",
                    "alertText2": " Datumsbereich"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": "- Ungültig ",
                    "alertText2": " Datumszeitbereich"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "- Minimum erfordern ",
                    "alertText2": " Zeichen"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " Zeichen erlaubt"
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": "- Sie müssen eines der folgenden Felder ausfüllen"
                },
                "min": {
                    "regex": "none",
                    "alertText": "- Mindestwert ist "
                },
                "max": {
                    "regex": "none",
                    "alertText": "- Maximalwert ist "
                },
                "past": {
                    "regex": "none",
                    "alertText": "- Datum vor"
                },
                "future": {
                    "regex": "none",
                    "alertText": "- Datum nach "
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " Optionen erlaubt"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "- Bitte auswählen ",
                    "alertText2": " Optionen"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "- Felder stimmen nicht"
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
                    "alertText": "- Must not have duplicate entries"
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": "- Die Kreditkartennummer ist nicht gültig"
                },
                "email": {
                    // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
                    "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": "- Email Adresse ist nicht gültig"
                },
                "emails": {
                    // http://emailregex.com/
                    "regex": /^((([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))(\s*[,]{0,1}\s*))+$/,
                    "alertText": "- Eine (oder mehr) E-Mail Adresse ist nicht gültig"
                },
                "maxEmailsCount": {
                    "regex": "none",
                    "alertText": "- Kann nicht mehr als ",
                    "alertText2": " E-Mail Adresse enthalten"
                },
                "integer": {
                    "regex": /^$|^[\-\+]?\d+$/,
                    "alertText": "- Keine gültige Nummer."
                },
                "positive_integer": {
                    "regex": /^$|^\d{1,10}$/,
                    "alertText": "- Keine gültige positive Zahl."
                },
                "natural": {
                    "regex": /^[1-9][0-9]*$/,
                    "alertText": "- Keine gültige natürliche Nummer."
                },
                "number": {
                    // Number, including positive, negative, and floating decimal. credit: orefalo
                    "regex": /^[\-\+]?((([0-9]{1,3})([,][0-9]{3})*)|([0-9]+))?([\.]([0-9]+))?$/,
                    "alertText": "- Ungültige Gleitkomma- / Dezimalzahl"
                },
                "positive_number": {
                    // Number, including positive, and floating decimal. credit: orefalo
                    "regex": /^$|^(([0]{1})|([1-9]{1}[0-9]{0,11}))(\.([0-9]{1,2}))?$/,
                    "alertText": "- Ungültige positive Gleitkomma- / Dezimalzahl"
                },
                "item_size": {
                    // Max 9999.99 is working but min 0.01 is not, it also accepts 0 (be careful)
                    "regex": /^$|^(([0]{1})|([1-9]{1}[0-9]{0,3}))(\.([0-9]{1,2}))?$/,
                    "alertText": "- Keine gültige positive Gleit- / Dezimalzahl, min. 0,01, max. 9999,99"
                },
                "zip_code": {
                    "regex": /^$|^[0-9A-Za-z\-\. ]{3,20}$/,
                    "alertText": "- Ungültige Postleitzahl"
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
                    "alertText": "- Ungültiges Datum. Es muss im Format JJJJ-MM-TT sein"
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "- Ungültige IP-Adresse"
                },
                "url": {
                    "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
                    "alertText": "- Ungültige URL"
                },
                "onlyNumberSp": {
                    "regex": /^$|^[0-9\ ]+$/,
                    "alertText": "- Nur Zahlen und Lehrzeichen"
                },
                "tariffNumber": {
                    "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
                    "alertText": "- Harmonized Tariff Number is not correct."
                },
                "onlyNumber": {
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Nur Zahlen"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Nur Zahlen, erstes Symbol darf nicht 0 sein, und es darf nicht mehr als 25 Zeichen sein."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Nur Buchstaben, Zahlen, Leerzeichen und \'/ + -_.,:; () Symbole sind erlaubt."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Nur Buchstaben, Zahlen, Leerzeichen und -_., \'& () Symbole sind erlaubt."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
                    "alertText": "- Nur Buchstaben, Leerzeichen und \'_ Symbole sind erlaubt."
                },
                "onlyLetterSp": {
                    "regex": /^$|^[a-zA-Z\ \']+$/,
                    "alertText": "- Nur Buchstaben"
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "- Keine Sonderzeichen erlaubt"
                },
                "onlyLetterNumberSp": {
                    "regex": /^$|^[0-9a-zA-Z\ ]+$/,
                    "alertText": "- Nur Buchstaben, Zahlen und Leerzeichen"
                },
                "alphaNumeric": {
                    "regex": /^([A-Za-z0-9\'\"\-\.\s])+$/i,
                    "alertText": "- Nur Buchstaben, Zahlen und \".-\" Symbole sind erlaubt."
                },
                "iframe": {
                    "regex": /^<iframe.* src=\"(.*)\".*><\/iframe>$/,
                    "alertText": "- Ungültiger Iframe"
                },
                // --- CUSTOM RULES -- Those are specific to the demos, they can be removed or changed to your likings
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- Dieser Link stimmt nicht mit den erforderlichen Zeichen. Nur Buchstaben, Zahlen und \'- _.\' Symbole sind erlaubt. Kann nicht weniger als 5 Zeichen und mehr als 30 Zeichen lang sein.",
                    "alertTextOk": "Dieser Link ist gut.",
                    "alertTextLoad": "* Es wird bestätigt, bitte warten."
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- Dieses Kennwort ist nicht sicher!",
                    "alertTextOk": "Das ist ein starkes Kennwort.",
                    "alertTextLoad": "* Es wird bestätigt, bitte warten."
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
                    "alertText": "- Diese E-Mail-Adresse ist nicht verfügbar!",
                    "alertTextOk": "Diese E-Mail-Adresse ist verfügbar.",
                    "alertTextLoad": "* Es wird bestätigt, bitte warten."
                },
                "validate2fields": {
                    "alertText": "- "
                },
                //tls warning:homegrown not fielded
                "dateFormat":{
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(?:(?:0?[1-9]|1[0-2])(\/|-)(?:0?[1-9]|1\d|2[0-8]))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(0?2(\/|-)29)(\/|-)(?:(?:0[48]00|[13579][26]00|[2468][048]00)|(?:\d\d)?(?:0[48]|[2468][048]|[13579][26]))$/,
                    "alertText": "- Ungültiges Datum"
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "- Ungültiges Datum oder Format",
                    "alertText2": "Erforderliches Format: ",
                    "alertText3": "MM / TT / JJJJ hh: mm: ss AM | PM oder ",
                    "alertText4": "yyyy-mm-dd hh:mm:ss AM|PM"
                },
                "variableName": {
                    "regex": /^[a-z_]+$/,
                    "alertText": "- Nur Kleinbuchstaben und \"_\""
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Email Adresse ist nicht gültig"
                },
                "emailsWithWhitespaces": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^([\s]*)?((([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))(\s*[,]{0,1}\s*))+([\s]*)?$/.test(text);
                    },
                    "alertText": "- Eine (oder mehr) E-Mail Adresse ist nicht gültig"
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
                    "alertText": "- Mindestwert ist ",
                    "alertTextDefault": "- Mindestwert ist "
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
                    "alertText": "- Maximalwert ist ",
                    "alertTextDefault": "- Maximalwert ist "
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    it : {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "- This field is required",
                    "alertTextCheckboxMultiple": "* Please select an option",
                    "alertTextCheckboxe": "* This option is required",
                    "alertTextDateRange": "* Both date range fields are required"
                },
                "requiredInFunction": {
                    "func": function(field, rules, i, options){
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": "- Field must equal test"
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Range"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Time Range"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "- Require minimum ",
                    "alertText2": " characters"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " characters allowed"
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": "- You must fill one of the following fields"
                },
                "min": {
                    "regex": "none",
                    "alertText": "- Minimum value is "
                },
                "max": {
                    "regex": "none",
                    "alertText": "- Maximum value is "
                },
                "past": {
                    "regex": "none",
                    "alertText": "- Date prior to"
                },
                "future": {
                    "regex": "none",
                    "alertText": "- Date past "
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " options allowed"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "- Please select ",
                    "alertText2": " options"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "- Fields do not match"
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
                    "alertText": "- Must not have duplicate entries"
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": "- Credit card number is not valid"
                },
                "email": {
                    // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
                    "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Not a valid number."
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
                    "alertText": "- Invalid floating/decimal number"
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
                    "alertText": "- Invalid date, must be in YYYY-MM-DD format"
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "- Invalid IP address"
                },
                "url": {
                    "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
                    "alertText": "- The link you provided does not seem as valid. Example: https://www.example.com"
                },
                "onlyNumberSp": {
                    "regex": /^$|^[0-9\ ]+$/,
                    "alertText": "- Numbers and spaces only"
                },
                "tariffNumber": {
                    "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
                    "alertText": "- Harmonized Tariff Number is not correct."
                },
                "onlyNumber": {
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Numbers only"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Only letters, numbers, spaces and \'/+-_.,:\';()#% symbols are allowed."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Only letters, numbers, spaces and -_.,\'&() symbols are allowed."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
                    "alertText": "- Only latin letters, spaces or \' and - characters are allowed."
                },
                "onlyLetterSp": {
                    "regex": /^$|^[a-zA-Z\ \']+$/,
                    "alertText": "- Letters only"
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "- No special characters allowed"
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
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- This link does not match the required characters or the domain link is already taken. Only letters, numbers and \'- _ .\' symbols are allowed. Characters should be more than 5 and less than 30. <br>Example: my-trade-company-ltd",
                    "alertTextOk": "This link is good.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- This password is not secure!",
                    "alertTextOk": "This is strong password.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
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
                    "alertText": "- Invalid Date"
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "- Invalid Date or Date Format",
                    "alertText2": "Expected Format: ",
                    "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM or ",
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Minimum value is ",
                    "alertTextDefault": "- Minimum value is "
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
                    "alertText": "- Maximum value is ",
                    "alertTextDefault": "- Maximum value is "
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    ro : {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "- This field is required",
                    "alertTextCheckboxMultiple": "* Please select an option",
                    "alertTextCheckboxe": "* This option is required",
                    "alertTextDateRange": "* Both date range fields are required"
                },
                "requiredInFunction": {
                    "func": function(field, rules, i, options){
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": "- Field must equal test"
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Range"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Time Range"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "- Require minimum ",
                    "alertText2": " characters"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " characters allowed"
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": "- You must fill one of the following fields"
                },
                "min": {
                    "regex": "none",
                    "alertText": "- Minimum value is "
                },
                "max": {
                    "regex": "none",
                    "alertText": "- Maximum value is "
                },
                "past": {
                    "regex": "none",
                    "alertText": "- Date prior to"
                },
                "future": {
                    "regex": "none",
                    "alertText": "- Date past "
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " options allowed"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "- Please select ",
                    "alertText2": " options"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "- Fields do not match"
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
                    "alertText": "- Must not have duplicate entries"
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": "- Credit card number is not valid"
                },
                "email": {
                    // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
                    "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Not a valid number."
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
                    "alertText": "- Invalid floating/decimal number"
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
                    "alertText": "- Invalid date, must be in YYYY-MM-DD format"
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "- Invalid IP address"
                },
                "url": {
                    "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
                    "alertText": "- The link you provided does not seem as valid. Example: https://www.example.com"
                },
                "onlyNumberSp": {
                    "regex": /^$|^[0-9\ ]+$/,
                    "alertText": "- Numbers and spaces only"
                },
                "tariffNumber": {
                    "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
                    "alertText": "- Harmonized Tariff Number is not correct."
                },
                "onlyNumber": {
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Numbers only"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Only letters, numbers, spaces and \'/+-_.,:\';()#% symbols are allowed."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Only letters, numbers, spaces and -_.,\'&() symbols are allowed."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
                    "alertText": "- Only latin letters, spaces or \' and - characters are allowed."
                },
                "onlyLetterSp": {
                    "regex": /^$|^[a-zA-Z\ \']+$/,
                    "alertText": "- Letters only"
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "- No special characters allowed"
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
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- This link does not match the required characters or the domain link is already taken. Only letters, numbers and \'- _ .\' symbols are allowed. Characters should be more than 5 and less than 30. <br>Example: my-trade-company-ltd",
                    "alertTextOk": "This link is good.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- This password is not secure!",
                    "alertTextOk": "This is strong password.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
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
                    "alertText": "- Invalid Date"
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "- Invalid Date or Date Format",
                    "alertText2": "Expected Format: ",
                    "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM or ",
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Minimum value is ",
                    "alertTextDefault": "- Minimum value is "
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
                    "alertText": "- Maximum value is ",
                    "alertTextDefault": "- Maximum value is "
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    hi : {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "- This field is required",
                    "alertTextCheckboxMultiple": "* Please select an option",
                    "alertTextCheckboxe": "* This option is required",
                    "alertTextDateRange": "* Both date range fields are required"
                },
                "requiredInFunction": {
                    "func": function(field, rules, i, options){
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": "- Field must equal test"
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Range"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Time Range"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "- Require minimum ",
                    "alertText2": " characters"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " characters allowed"
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": "- You must fill one of the following fields"
                },
                "min": {
                    "regex": "none",
                    "alertText": "- Minimum value is "
                },
                "max": {
                    "regex": "none",
                    "alertText": "- Maximum value is "
                },
                "past": {
                    "regex": "none",
                    "alertText": "- Date prior to"
                },
                "future": {
                    "regex": "none",
                    "alertText": "- Date past "
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " options allowed"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "- Please select ",
                    "alertText2": " options"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "- Fields do not match"
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
                    "alertText": "- Must not have duplicate entries"
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": "- Credit card number is not valid"
                },
                "email": {
                    // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
                    "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Not a valid number."
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
                    "alertText": "- Invalid floating/decimal number"
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
                    "alertText": "- Invalid date, must be in YYYY-MM-DD format"
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "- Invalid IP address"
                },
                "url": {
                    "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
                    "alertText": "- The link you provided does not seem as valid. Example: https://www.example.com"
                },
                "onlyNumberSp": {
                    "regex": /^$|^[0-9\ ]+$/,
                    "alertText": "- Numbers and spaces only"
                },
                "tariffNumber": {
                    "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
                    "alertText": "- Harmonized Tariff Number is not correct."
                },
                "onlyNumber": {
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Numbers only"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Only letters, numbers, spaces and \'/+-_.,:\';()#% symbols are allowed."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Only letters, numbers, spaces and -_.,\'&() symbols are allowed."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
                    "alertText": "- Only latin letters, spaces or \' and - characters are allowed."
                },
                "onlyLetterSp": {
                    "regex": /^$|^[a-zA-Z\ \']+$/,
                    "alertText": "- Letters only"
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "- No special characters allowed"
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
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- This link does not match the required characters or the domain link is already taken. Only letters, numbers and \'- _ .\' symbols are allowed. Characters should be more than 5 and less than 30. <br>Example: my-trade-company-ltd",
                    "alertTextOk": "This link is good.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- This password is not secure!",
                    "alertTextOk": "This is strong password.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
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
                    "alertText": "- Invalid Date"
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "- Invalid Date or Date Format",
                    "alertText2": "Expected Format: ",
                    "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM or ",
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Minimum value is ",
                    "alertTextDefault": "- Minimum value is "
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
                    "alertText": "- Maximum value is ",
                    "alertTextDefault": "- Maximum value is "
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    iw : {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "- This field is required",
                    "alertTextCheckboxMultiple": "* Please select an option",
                    "alertTextCheckboxe": "* This option is required",
                    "alertTextDateRange": "* Both date range fields are required"
                },
                "requiredInFunction": {
                    "func": function(field, rules, i, options){
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": "- Field must equal test"
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Range"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Time Range"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "- Require minimum ",
                    "alertText2": " characters"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " characters allowed"
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": "- You must fill one of the following fields"
                },
                "min": {
                    "regex": "none",
                    "alertText": "- Minimum value is "
                },
                "max": {
                    "regex": "none",
                    "alertText": "- Maximum value is "
                },
                "past": {
                    "regex": "none",
                    "alertText": "- Date prior to"
                },
                "future": {
                    "regex": "none",
                    "alertText": "- Date past "
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " options allowed"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "- Please select ",
                    "alertText2": " options"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "- Fields do not match"
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
                    "alertText": "- Must not have duplicate entries"
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": "- Credit card number is not valid"
                },
                "email": {
                    // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
                    "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Not a valid number."
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
                    "alertText": "- Invalid floating/decimal number"
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
                    "alertText": "- Invalid date, must be in YYYY-MM-DD format"
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "- Invalid IP address"
                },
                "url": {
                    "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
                    "alertText": "- The link you provided does not seem as valid. Example: https://www.example.com"
                },
                "onlyNumberSp": {
                    "regex": /^$|^[0-9\ ]+$/,
                    "alertText": "- Numbers and spaces only"
                },
                "tariffNumber": {
                    "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
                    "alertText": "- Harmonized Tariff Number is not correct."
                },
                "onlyNumber": {
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Numbers only"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Only letters, numbers, spaces and \'/+-_.,:\';()#% symbols are allowed."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Only letters, numbers, spaces and -_.,\'&() symbols are allowed."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
                    "alertText": "- Only latin letters, spaces or \' and - characters are allowed."
                },
                "onlyLetterSp": {
                    "regex": /^$|^[a-zA-Z\ \']+$/,
                    "alertText": "- Letters only"
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "- No special characters allowed"
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
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- This link does not match the required characters or the domain link is already taken. Only letters, numbers and \'- _ .\' symbols are allowed. Characters should be more than 5 and less than 30. <br>Example: my-trade-company-ltd",
                    "alertTextOk": "This link is good.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- This password is not secure!",
                    "alertTextOk": "This is strong password.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
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
                    "alertText": "- Invalid Date"
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "- Invalid Date or Date Format",
                    "alertText2": "Expected Format: ",
                    "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM or ",
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Minimum value is ",
                    "alertTextDefault": "- Minimum value is "
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
                    "alertText": "- Maximum value is ",
                    "alertTextDefault": "- Maximum value is "
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    vi : {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "- Trường này bắt buộc",
                    "alertTextCheckboxMultiple": "* Vui lòng chọn một tùy chọn",
                    "alertTextCheckboxe": "* Checkbox này bắt buộc",
                    "alertTextDateRange": "* Cả hai trường ngày tháng đều bắt buộc"
                },
                "requiredInFunction": {
                    "func": function(field, rules, i, options){
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": "- Giá trị của trường phải là test"
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": "- Không đúng ",
                    "alertText2": " Khoảng ngày tháng"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": "- Không đúng ",
                    "alertText2": " Khoảng thời gian"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "- Tối thiểu ",
                    "alertText2": " số ký tự được cho phép"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "- Tối đa ",
                    "alertText2": " số ký tự được cho phép"
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": "- Bạn phải điền một trong những trường sau"
                },
                "min": {
                    "regex": "none",
                    "alertText": "- Giá trị nhỏ nhất là "
                },
                "max": {
                    "regex": "none",
                    "alertText": "- Giá trị lớn nhất là "
                },
                "past": {
                    "regex": "none",
                    "alertText": "- Ngày kéo dài tới"
                },
                "future": {
                    "regex": "none",
                    "alertText": "- Ngày đã qua "
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "- Tối đa ",
                    "alertText2": " số tùy chọn được cho phép"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "- Vui lòng chọn ",
                    "alertText2": " các tùy chọn"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "- Giá trị các trường không giống nhau"
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
                    "alertText": "- Must not have duplicate entries"
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": "- Số thẻ tín dụng sai"
                },
                "email": {
                    // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
                    "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": "- Địa chỉ thư điện tử sai"
                },
                "emails": {
                    // http://emailregex.com/
                    "regex": /^((([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))(\s*[,]{0,1}\s*))+$/,
                    "alertText": "- Địa chỉ email không khả dụng"
                },
                "maxEmailsCount": {
                    "regex": "none",
                    "alertText": "- Không thể chứa nhiều hơn ",
                    "alertText2": " địa chỉ email"
                },
                "integer": {
                    "regex": /^$|^[\-\+]?\d+$/,
                    "alertText": "- Không đúng là số nguyên."
                },
                "positive_integer": {
                    "regex": /^$|^\d{1,10}$/,
                    "alertText": "- Không phải số dương hợp lệ"
                },
                "natural": {
                    "regex": /^[1-9][0-9]*$/,
                    "alertText": "- Không phải số tự nhiên hợp lệ"
                },
                "number": {
                    // Number, including positive, negative, and floating decimal. credit: orefalo
                    "regex": /^[\-\+]?((([0-9]{1,3})([,][0-9]{3})*)|([0-9]+))?([\.]([0-9]+))?$/,
                    "alertText": "- Không đúng là số thập phân"
                },
                "positive_number": {
                    // Number, including positive, and floating decimal. credit: orefalo
                    "regex": /^$|^(([0]{1})|([1-9]{1}[0-9]{0,11}))(\.([0-9]{1,2}))?$/,
                    "alertText": "- Không phải là số thập phân / số thập phân dương hợp lệ"
                },
                "item_size": {
                    // Max 9999.99 is working but min 0.01 is not, it also accepts 0 (be careful)
                    "regex": /^$|^(([0]{1})|([1-9]{1}[0-9]{0,3}))(\.([0-9]{1,2}))?$/,
                    "alertText": "- Không phải là số thực / số thập phân dương hợp lệ, tối thiểu 0,01, tối đa 9999,99"
                },
                "zip_code": {
                    "regex": /^$|^[0-9A-Za-z\-\. ]{3,20}$/,
                    "alertText": "- Không phải mã zip hợp lệ"
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
                    "alertText": "- Ngày sai, phải có định dạng YYYY-MM-DD"
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "- Địa chỉ IP sai"
                },
                "url": {
                    "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
                    "alertText": "- URL không hợp lệ"
                },
                "onlyNumberSp": {
                    "regex": /^$|^[0-9\ ]+$/,
                    "alertText": "- Chỉ điền số"
                },
                "tariffNumber": {
                    "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
                    "alertText": "- Harmonized Tariff Number is not correct."
                },
                "onlyNumber": {
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Chỉ có số"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Chỉ có số, ký hiệu đầu tiên không thể là 0, và không thể có nhiều hơn 25 ký tự."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Chỉ cho phép các ký tự, số, dấu cách và ký hiệu \'/ + -_.,:; ()."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Chỉ cho phép các ký tự, số, dấu cách và ký hiệu  -_.,\'&()."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
                    "alertText": "- Chỉ cho phép các chữ cái, dấu cách và ký hiệu \'_."
                },
                "onlyLetterSp": {
                    "regex": /^$|^[a-zA-Z\ \']+$/,
                    "alertText": "- Chỉ điền chữ"
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "- Không được chứa ký tự đặc biệt"
                },
                "onlyLetterNumberSp": {
                    "regex": /^$|^[0-9a-zA-Z\ ]+$/,
                    "alertText": "- Chỉ cho phép các chữ cái, số và dấu cách"
                },
                "alphaNumeric": {
                    "regex": /^([A-Za-z0-9\'\"\-\.\s])+$/i,
                    "alertText": "- Chỉ cho phép ký tự, chữ số và ký hiệu \'.-\""
                },
                "iframe": {
                    "regex": /^<iframe.* src=\"(.*)\".*><\/iframe>$/,
                    "alertText": "- Khung không hợp lệ"
                },
                // --- CUSTOM RULES -- Those are specific to the demos, they can be removed or changed to your likings
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- Liên kết này không khớp với các ký tự bắt buộc hoặc từ được đặt trước. Chỉ chữ cái, số và \'- _.\' biểu tượng được cho phép. Không được nhỏ hơn 5 ký tự và lớn hơn 30 ký tự",
                    "alertTextOk": "Liên kết tốt",
                    "alertTextLoad": "* Đang xác thực, vui lòng đợi"
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- Mật khẩu không đảm bảo",
                    "alertTextOk": "Mật khẩu mạnh",
                    "alertTextLoad": "* Đang xác thực, vui lòng đợi"
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
                    "alertText": "- Địa chỉ email đã được sử dụng",
                    "alertTextOk": "Địa chỉ email khả dụng",
                    "alertTextLoad": "* Đang xác thực, vui lòng đợi"
                },
                "validate2fields": {
                    "alertText": "- "
                },
                //tls warning:homegrown not fielded
                "dateFormat":{
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(?:(?:0?[1-9]|1[0-2])(\/|-)(?:0?[1-9]|1\d|2[0-8]))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(0?2(\/|-)29)(\/|-)(?:(?:0[48]00|[13579][26]00|[2468][048]00)|(?:\d\d)?(?:0[48]|[2468][048]|[13579][26]))$/,
                    "alertText": "- Ngày sai"
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "- Ngày sai hoặc định dạng ngày sai",
                    "alertText2": "Định dạng đúng là: ",
                    "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM hay ",
                    "alertText4": "yyyy-mm-dd hh:mm:ss AM|PM"
                },
                "variableName": {
                    "regex": /^[a-z_]+$/,
                    "alertText": "- Chỉ chữ cái thường và \'_\'"
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Địa chỉ thư điện tử sai"
                },
                "emailsWithWhitespaces": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^([\s]*)?((([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))(\s*[,]{0,1}\s*))+([\s]*)?$/.test(text);
                    },
                    "alertText": "- Địa chỉ email không khả dụng"
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
                    "alertText": "- Giá trị nhỏ nhất là ",
                    "alertTextDefault": "- Giá trị nhỏ nhất là "
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
                    "alertText": "- Giá trị lớn nhất là ",
                    "alertTextDefault": "- Giá trị lớn nhất là "
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    si : {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "- This field is required",
                    "alertTextCheckboxMultiple": "* Please select an option",
                    "alertTextCheckboxe": "* This option is required",
                    "alertTextDateRange": "* Both date range fields are required"
                },
                "requiredInFunction": {
                    "func": function(field, rules, i, options){
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": "- Field must equal test"
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Range"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Time Range"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "- Require minimum ",
                    "alertText2": " characters"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " characters allowed"
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": "- You must fill one of the following fields"
                },
                "min": {
                    "regex": "none",
                    "alertText": "- Minimum value is "
                },
                "max": {
                    "regex": "none",
                    "alertText": "- Maximum value is "
                },
                "past": {
                    "regex": "none",
                    "alertText": "- Date prior to"
                },
                "future": {
                    "regex": "none",
                    "alertText": "- Date past "
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " options allowed"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "- Please select ",
                    "alertText2": " options"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "- Fields do not match"
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
                    "alertText": "- Must not have duplicate entries"
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": "- Credit card number is not valid"
                },
                "email": {
                    // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
                    "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Not a valid number."
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
                    "alertText": "- Invalid floating/decimal number"
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
                    "alertText": "- Invalid date, must be in YYYY-MM-DD format"
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "- Invalid IP address"
                },
                "url": {
                    "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
                    "alertText": "- The link you provided does not seem as valid. Example: https://www.example.com"
                },
                "onlyNumberSp": {
                    "regex": /^$|^[0-9\ ]+$/,
                    "alertText": "- Numbers and spaces only"
                },
                "tariffNumber": {
                    "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
                    "alertText": "- Harmonized Tariff Number is not correct."
                },
                "onlyNumber": {
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Numbers only"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Only letters, numbers, spaces and \'/+-_.,:\';()#% symbols are allowed."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Only letters, numbers, spaces and -_.,\'&() symbols are allowed."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
                    "alertText": "- Only latin letters, spaces or \' and - characters are allowed."
                },
                "onlyLetterSp": {
                    "regex": /^$|^[a-zA-Z\ \']+$/,
                    "alertText": "- Letters only"
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "- No special characters allowed"
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
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- This link does not match the required characters or the domain link is already taken. Only letters, numbers and \'- _ .\' symbols are allowed. Characters should be more than 5 and less than 30. <br>Example: my-trade-company-ltd",
                    "alertTextOk": "This link is good.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- This password is not secure!",
                    "alertTextOk": "This is strong password.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
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
                    "alertText": "- Invalid Date"
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "- Invalid Date or Date Format",
                    "alertText2": "Expected Format: ",
                    "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM or ",
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Minimum value is ",
                    "alertTextDefault": "- Minimum value is "
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
                    "alertText": "- Maximum value is ",
                    "alertTextDefault": "- Maximum value is "
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    ta : {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "- This field is required",
                    "alertTextCheckboxMultiple": "* Please select an option",
                    "alertTextCheckboxe": "* This option is required",
                    "alertTextDateRange": "* Both date range fields are required"
                },
                "requiredInFunction": {
                    "func": function(field, rules, i, options){
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": "- Field must equal test"
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Range"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Time Range"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "- Require minimum ",
                    "alertText2": " characters"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " characters allowed"
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": "- You must fill one of the following fields"
                },
                "min": {
                    "regex": "none",
                    "alertText": "- Minimum value is "
                },
                "max": {
                    "regex": "none",
                    "alertText": "- Maximum value is "
                },
                "past": {
                    "regex": "none",
                    "alertText": "- Date prior to"
                },
                "future": {
                    "regex": "none",
                    "alertText": "- Date past "
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " options allowed"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "- Please select ",
                    "alertText2": " options"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "- Fields do not match"
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
                    "alertText": "- Must not have duplicate entries"
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": "- Credit card number is not valid"
                },
                "email": {
                    // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
                    "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Not a valid number."
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
                    "alertText": "- Invalid floating/decimal number"
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
                    "alertText": "- Invalid date, must be in YYYY-MM-DD format"
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "- Invalid IP address"
                },
                "url": {
                    "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
                    "alertText": "- The link you provided does not seem as valid. Example: https://www.example.com"
                },
                "onlyNumberSp": {
                    "regex": /^$|^[0-9\ ]+$/,
                    "alertText": "- Numbers and spaces only"
                },
                "tariffNumber": {
                    "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
                    "alertText": "- Harmonized Tariff Number is not correct."
                },
                "onlyNumber": {
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Numbers only"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Only letters, numbers, spaces and \'/+-_.,:\';()#% symbols are allowed."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Only letters, numbers, spaces and -_.,\'&() symbols are allowed."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
                    "alertText": "- Only latin letters, spaces or \' and - characters are allowed."
                },
                "onlyLetterSp": {
                    "regex": /^$|^[a-zA-Z\ \']+$/,
                    "alertText": "- Letters only"
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "- No special characters allowed"
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
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- This link does not match the required characters or the domain link is already taken. Only letters, numbers and \'- _ .\' symbols are allowed. Characters should be more than 5 and less than 30. <br>Example: my-trade-company-ltd",
                    "alertTextOk": "This link is good.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- This password is not secure!",
                    "alertTextOk": "This is strong password.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
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
                    "alertText": "- Invalid Date"
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "- Invalid Date or Date Format",
                    "alertText2": "Expected Format: ",
                    "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM or ",
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Minimum value is ",
                    "alertTextDefault": "- Minimum value is "
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
                    "alertText": "- Maximum value is ",
                    "alertTextDefault": "- Maximum value is "
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    sq : {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "- This field is required",
                    "alertTextCheckboxMultiple": "* Please select an option",
                    "alertTextCheckboxe": "* This option is required",
                    "alertTextDateRange": "* Both date range fields are required"
                },
                "requiredInFunction": {
                    "func": function(field, rules, i, options){
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": "- Field must equal test"
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Range"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Time Range"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "- Require minimum ",
                    "alertText2": " characters"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " characters allowed"
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": "- You must fill one of the following fields"
                },
                "min": {
                    "regex": "none",
                    "alertText": "- Minimum value is "
                },
                "max": {
                    "regex": "none",
                    "alertText": "- Maximum value is "
                },
                "past": {
                    "regex": "none",
                    "alertText": "- Date prior to"
                },
                "future": {
                    "regex": "none",
                    "alertText": "- Date past "
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " options allowed"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "- Please select ",
                    "alertText2": " options"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "- Fields do not match"
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
                    "alertText": "- Must not have duplicate entries"
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": "- Credit card number is not valid"
                },
                "email": {
                    // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
                    "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Not a valid number."
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
                    "alertText": "- Invalid floating/decimal number"
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
                    "alertText": "- Invalid date, must be in YYYY-MM-DD format"
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "- Invalid IP address"
                },
                "url": {
                    "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
                    "alertText": "- The link you provided does not seem as valid. Example: https://www.example.com"
                },
                "onlyNumberSp": {
                    "regex": /^$|^[0-9\ ]+$/,
                    "alertText": "- Numbers and spaces only"
                },
                "tariffNumber": {
                    "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
                    "alertText": "- Harmonized Tariff Number is not correct."
                },
                "onlyNumber": {
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Numbers only"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Only letters, numbers, spaces and \'/+-_.,:\';()#% symbols are allowed."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Only letters, numbers, spaces and -_.,\'&() symbols are allowed."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
                    "alertText": "- Only latin letters, spaces or \' and - characters are allowed."
                },
                "onlyLetterSp": {
                    "regex": /^$|^[a-zA-Z\ \']+$/,
                    "alertText": "- Letters only"
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "- No special characters allowed"
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
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- This link does not match the required characters or the domain link is already taken. Only letters, numbers and \'- _ .\' symbols are allowed. Characters should be more than 5 and less than 30. <br>Example: my-trade-company-ltd",
                    "alertTextOk": "This link is good.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- This password is not secure!",
                    "alertTextOk": "This is strong password.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
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
                    "alertText": "- Invalid Date"
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "- Invalid Date or Date Format",
                    "alertText2": "Expected Format: ",
                    "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM or ",
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Minimum value is ",
                    "alertTextDefault": "- Minimum value is "
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
                    "alertText": "- Maximum value is ",
                    "alertTextDefault": "- Maximum value is "
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    ar : {
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

                        return key_vals.every(function (num, index) { return index === key_vals.lastIndexOf(num) });
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
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Numbers only"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Only letters, numbers, spaces and \'/+-_.,:\';()#% symbols are allowed."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Only letters, numbers, spaces and -_.,\'&() symbols are allowed."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
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
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- This link does not match the required characters or the domain link is already taken. Only letters, numbers and \'- _ .\' symbols are allowed. Characters should be more than 5 and less than 30. <br>Example: my-trade-company-ltd",
                    "alertTextOk": "This link is good.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- This password is not secure!",
                    "alertTextOk": "This is strong password.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    pt : {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "- Campo obrigatório",
                    "alertTextCheckboxMultiple": "* Selecione uma opção",
                    "alertTextCheckboxe": "* Assinale a caixa de seleção",
                    "alertTextDateRange": "* Ambos os campos de datas são obrigatórios"
                },
                "requiredInFunction": {
                    "func": function(field, rules, i, options){
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": "- Field must equal test"
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": "- Inválido ",
                    "alertText2": " Date Range"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": "- Inválido ",
                    "alertText2": " Intervalo de tempo da data"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "- Mínimo ",
                    "alertText2": " carateres permitidos"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "- Máximo ",
                    "alertText2": " carateres permitidos"
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": "- Tem de preencher um dos seguintes campos"
                },
                "min": {
                    "regex": "none",
                    "alertText": "- O valor mínimo é "
                },
                "max": {
                    "regex": "none",
                    "alertText": "- O valor máximo é "
                },
                "past": {
                    "regex": "none",
                    "alertText": "- Data anterior a"
                },
                "future": {
                    "regex": "none",
                    "alertText": "- Data posterior a "
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "- O número máximo ",
                    "alertText2": " de escolhas foi ultrapassado"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "- Selecione ",
                    "alertText2": " opções"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "- Os campos não correspondem"
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
                    "alertText": "- Must not have duplicate entries"
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": "- Número do cartão de crédito inválido"
                },
                "email": {
                    // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
                    "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": "- Endereço de email inválido"
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
                    "alertText": "- Não é um número inteiro"
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
                    "alertText": "- Não é um número decimal"
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
                    "alertText": "- Data inválida, o formato deve de ser AAAA-MM-DD (ex.2012-12-31)"
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "- Número IP inválido"
                },
                "url": {
                    "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
                    "alertText": "- Endereço URL inválido"
                },
                "onlyNumberSp": {
                    "regex": /^$|^[0-9\ ]+$/,
                    "alertText": "- Só é permitido números"
                },
                "tariffNumber": {
                    "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
                    "alertText": "- Harmonized Tariff Number is not correct."
                },
                "onlyNumber": {
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Numbers only"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Only letters, numbers, spaces and \'/+-_.,:\';()#% symbols are allowed."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Only letters, numbers, spaces and -_.,\'&() symbols are allowed."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
                    "alertText": "- Only latin letters, spaces or \' and - characters are allowed."
                },
                "onlyLetterSp": {
                    "regex": /^$|^[a-zA-Z\ \']+$/,
                    "alertText": "- Só é permitido letras"
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "- Só são permitidos letras e números"
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
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- This link does not match the required characters or the domain link is already taken. Only letters, numbers and \'- _ .\' symbols are allowed. Characters should be more than 5 and less than 30. <br>Example: my-trade-company-ltd",
                    "alertTextOk": "This link is good.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- This password is not secure!",
                    "alertTextOk": "This is strong password.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
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
                    "alertText": "- Data inválida"
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "- Data inválida ou mal formatada",
                    "alertText2": "Formato esperado: ",
                    "alertText3": "mm/dd/aaaa hh:mm:ss AM|PM ou ",
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Endereço de email inválido"
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
                    "alertText": "- O valor mínimo é ",
                    "alertTextDefault": "- O valor mínimo é "
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
                    "alertText": "- O valor máximo é ",
                    "alertTextDefault": "- O valor máximo é "
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
                    zh_tw : {
                "required": { // Add your regex rules here, you can take telephone as an example
                    "regex": "none",
                    "alertText": "- This field is required",
                    "alertTextCheckboxMultiple": "* Please select an option",
                    "alertTextCheckboxe": "* This option is required",
                    "alertTextDateRange": "* Both date range fields are required"
                },
                "requiredInFunction": {
                    "func": function(field, rules, i, options){
                        return (field.val() == "test") ? true : false;
                    },
                    "alertText": "- Field must equal test"
                },
                "dateRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Range"
                },
                "dateTimeRange": {
                    "regex": "none",
                    "alertText": "- Invalid ",
                    "alertText2": " Date Time Range"
                },
                "minSize": {
                    "regex": "none",
                    "alertText": "- Require minimum ",
                    "alertText2": " characters"
                },
                "maxSize": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " characters allowed"
                },
                "groupRequired": {
                    "regex": "none",
                    "alertText": "- You must fill one of the following fields"
                },
                "min": {
                    "regex": "none",
                    "alertText": "- Minimum value is "
                },
                "max": {
                    "regex": "none",
                    "alertText": "- Maximum value is "
                },
                "past": {
                    "regex": "none",
                    "alertText": "- Date prior to"
                },
                "future": {
                    "regex": "none",
                    "alertText": "- Date past "
                },
                "maxCheckbox": {
                    "regex": "none",
                    "alertText": "- Maximum ",
                    "alertText2": " options allowed"
                },
                "minCheckbox": {
                    "regex": "none",
                    "alertText": "- Please select ",
                    "alertText2": " options"
                },
                "equals": {
                    "regex": "none",
                    "alertText": "- Fields do not match"
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
                    "alertText": "- Must not have duplicate entries"
                },
                "creditCard": {
                    "regex": "none",
                    "alertText": "- Credit card number is not valid"
                },
                "email": {
                    // HTML5 compatible emails regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
                    // "regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@([a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+))*$/,
                    "regex": /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Not a valid number."
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
                    "alertText": "- Invalid floating/decimal number"
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
                    "alertText": "- Invalid date, must be in YYYY-MM-DD format"
                },
                "ipv4": {
                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
                    "alertText": "- Invalid IP address"
                },
                "url": {
                    "regex": /^$|^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
                    "alertText": "- The link you provided does not seem as valid. Example: https://www.example.com"
                },
                "onlyNumberSp": {
                    "regex": /^$|^[0-9\ ]+$/,
                    "alertText": "- Numbers and spaces only"
                },
                "tariffNumber": {
                    "regex": /^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/,
                    "alertText": "- Harmonized Tariff Number is not correct."
                },
                "onlyNumber": {
                    "regex": /^$|^[0-9]+$$/,
                    "alertText": "- Numbers only"
                },
                "phoneNumber": {
                    "regex": /^$|^[1-9]\d{0,24}$/,
                    "alertText": "- Numbers only, first symbol cannot be 0, and there cannot be more than 25 characters."
                },
                "productTitle": {
                    "regex": /^[A-Za-z0-9\/\+-_.,: ';()#%]+$/,
                    "alertText": "- Only letters, numbers, spaces and \'/+-_.,:\';()#% symbols are allowed."
                },
                "companyTitle": {
                    "regex": /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/,
                    "alertText": "- Only letters, numbers, spaces and -_.,\'&() symbols are allowed."
                },
                "validUserName": {
                    "regex": /^[a-zA-Z][a-zA-Z '-]{1,}$/,
                    "alertText": "- Only latin letters, spaces or \' and - characters are allowed."
                },
                "onlyLetterSp": {
                    "regex": /^$|^[a-zA-Z\ \']+$/,
                    "alertText": "- Letters only"
                },
                "onlyLetterNumber": {
                    "regex": /^[0-9a-zA-Z]+$/,
                    "alertText": "- No special characters allowed"
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
                "checkCompanyLink": {
                    "url": "validate_ajax_call/ajax_company_link_verify",
                    "extraDataDynamic": ['#index_name'],
                    "alertText": "- This link does not match the required characters or the domain link is already taken. Only letters, numbers and \'- _ .\' symbols are allowed. Characters should be more than 5 and less than 30. <br>Example: my-trade-company-ltd",
                    "alertTextOk": "This link is good.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkPassword": {
                    "url": "validate_ajax_call/ajax_check_password",
                    "extraDataDynamic": ['#password'],
                    "alertText": "- This password is not secure!",
                    "alertTextOk": "This is strong password.",
                    "alertTextLoad": "* Validating, please wait."
                },
                "checkEmail": {
                    "url": "validate_ajax_call/ajax_check_email",
                    "extraDataDynamic": ['#email'],
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
                    "alertText": "- Invalid Date"
                },
                //tls warning:homegrown not fielded
                "dateTimeFormat": {
                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
                    "alertText": "- Invalid Date or Date Format",
                    "alertText2": "Expected Format: ",
                    "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM or ",
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
                "possible_duns": {
                    "func": function(field) {
                        var text = field.val() || '';
                        if ('' === text) {
                            return true;
                        }

                        return /^(((\d{9}|(\d{2}-(\d{7}|(\d{3}-\d{4}))))(-?\d{4})?)|\d{8}|\d{7}|\d{13})$/i.test(text);
                    },
                    "alertText": "- No a valid DUNS."
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
                    "alertText": "- Email address is not valid"
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
                    "alertText": "- Minimum value is ",
                    "alertTextDefault": "- Minimum value is "
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
                    "alertText": "- Maximum value is ",
                    "alertTextDefault": "- Maximum value is "
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
                    "alertText": "- Only letters, numbers, spaces and \'-_. characters are allowed."
                },
            },
            },
    fileUploader: {
                    en : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    cn : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    es : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    ru : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    fr : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    de : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    it : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    ro : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    hi : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    iw : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    vi : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    si : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    ta : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    sq : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    ar : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    pt : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
                    zh_tw : {
                error_exceeded_limit_text: "You cannot upload more than [AMOUNT] file(s).",
                error_format_not_allowed: "File type not allowed",
                error_category_required: "Please select or add a category first.",
                error_no_more_files: "You cannot upload more files.",
            },
            },
    general_i18n:{
                    en : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "Select city",
                form_placeholder_select2_state_first: "Select state / region first",
                form_placeholder_select2_state: "Select state / region",
                form_placeholder_select2_state_only_first_state: "Select state first",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "You should be log in to perform this operation.",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
                    cn : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "选择城市",
                form_placeholder_select2_state_first: "选择州",
                form_placeholder_select2_state: "选择州/地区",
                form_placeholder_select2_state_only_first_state: "选择城市",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "请登陆后再进行操作",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
                    es : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "Seleccione la Ciudad",
                form_placeholder_select2_state_first: "Seleccione primero el Estado",
                form_placeholder_select2_state: "Seleccione el Estado/la Región",
                form_placeholder_select2_state_only_first_state: "Seleccione primero el Estado",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "You should be log in to perform this operation.",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
                    ru : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "Выберите город",
                form_placeholder_select2_state_first: "Сначало выберите штат / регион",
                form_placeholder_select2_state: "Выберите штат / регион",
                form_placeholder_select2_state_only_first_state: "Сначало выберите штат",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "Вы должны войти в систему, чтобы произвести данную операцию.",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Подписаться на пользователя",
                seller_home_page_sidebar_menu_dropdown_favorite: "Избранные",
                seller_home_page_sidebar_menu_dropdown_favorited: "Избранные",
                pre_registration_input_placeholder_country_code: "Выберите Код страны",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Отправка сообщения...",
                sending_file_form_loader: "Sending files...",
            },
                    fr : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "Select city",
                form_placeholder_select2_state_first: "Select state / region first",
                form_placeholder_select2_state: "Select state / region",
                form_placeholder_select2_state_only_first_state: "Sélectionnez d\'abord l\'état",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "You should be log in to perform this operation.",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
                    de : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "Stadt  auswählen",
                form_placeholder_select2_state_first: "Zuerst Staat / Region auswählen",
                form_placeholder_select2_state: "Staat / Region auswählen",
                form_placeholder_select2_state_only_first_state: "Wählen Sie zuerst den Bundesstaat",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "Sie sollten angemeldet sein, um dieses Prozess auszuführen.",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
                    it : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "Select city",
                form_placeholder_select2_state_first: "Select state / region first",
                form_placeholder_select2_state: "Select state / region",
                form_placeholder_select2_state_only_first_state: "Select state first",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "You should be log in to perform this operation.",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
                    ro : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "Select city",
                form_placeholder_select2_state_first: "Select state / region first",
                form_placeholder_select2_state: "Select state / region",
                form_placeholder_select2_state_only_first_state: "Select state first",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "You should be log in to perform this operation.",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
                    hi : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "Select city",
                form_placeholder_select2_state_first: "Select state / region first",
                form_placeholder_select2_state: "Select state / region",
                form_placeholder_select2_state_only_first_state: "Select state first",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "You should be log in to perform this operation.",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
                    iw : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "Select city",
                form_placeholder_select2_state_first: "Select state / region first",
                form_placeholder_select2_state: "Select state / region",
                form_placeholder_select2_state_only_first_state: "Select state first",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "You should be log in to perform this operation.",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
                    vi : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "Chọn thành phố",
                form_placeholder_select2_state_first: "Chọn bang trước",
                form_placeholder_select2_state: "Chọn bang/ khu vực",
                form_placeholder_select2_state_only_first_state: "Chọn bang trước",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "Bạn cần phải đăng nhập để thực hiện thao tác này",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
                    si : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "Select city",
                form_placeholder_select2_state_first: "Select state / region first",
                form_placeholder_select2_state: "Select state / region",
                form_placeholder_select2_state_only_first_state: "Select state first",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "You should be log in to perform this operation.",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
                    ta : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "Select city",
                form_placeholder_select2_state_first: "Select state / region first",
                form_placeholder_select2_state: "Select state / region",
                form_placeholder_select2_state_only_first_state: "Select state first",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "You should be log in to perform this operation.",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
                    sq : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "Select city",
                form_placeholder_select2_state_first: "Select state / region first",
                form_placeholder_select2_state: "Select state / region",
                form_placeholder_select2_state_only_first_state: "Select state first",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "You should be log in to perform this operation.",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
                    ar : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "اختر المدينة",
                form_placeholder_select2_state_first: "اختر الولاية أولا",
                form_placeholder_select2_state: "اختر الولاية/الإقليم",
                form_placeholder_select2_state_only_first_state: "اختر الولاية أولا",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "You should be log in to perform this operation.",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
                    pt : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "Selecionar a cidade",
                form_placeholder_select2_state_first: "Selecionar o estado primeiro",
                form_placeholder_select2_state: "Selecionar o estado / região",
                form_placeholder_select2_state_only_first_state: "Selecionar o estado primeiro",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "You should be log in to perform this operation.",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
                    zh_tw : {
                form_button_delete_file_text: "Delete",
                form_button_delete_file_title: "Delete",
                form_button_delete_file_message: "Are you sure you want to delete this file?",
                form_placeholder_select2_city: "選擇城市",
                form_placeholder_select2_state_first: "首先選擇州/區",
                form_placeholder_select2_state: "首先選擇國家",
                form_placeholder_select2_state_only_first_state: "Select state first",
                system_message_changes_will_come_soon: "Changes will come into effect soon.",
                system_message_server_error_text: "Undefined service error",
                system_message_client_error_text: "Undefined client error",
                systmess_error_should_be_logout: "You should be log out to perform this operation.",
                systmess_error_should_be_logged_in: "You should be log in to perform this operation.",
                validate_error_message: "Some errors appeared during form completion. Please make sure all the required fields are listed.",
                multiple_select_count_selected_industries_placeholder: "Selected {{COUNT}} industries",
                multiple_select_select_industries_and_categories_count_placeholder: "Selected {{COUNT_C}} categories from {{COUNT_I}} industry",
                multiple_select_max_industries: "You can select max. [COUNT] industries.",
                multiple_select_industry_without_categories_msg: "This industry does not have categories",
                seller_home_page_sidebar_menu_dropdown_follow_user: "Follow user",
                seller_home_page_sidebar_menu_dropdown_favorite: "Favorite",
                seller_home_page_sidebar_menu_dropdown_favorited: "Favorited",
                pre_registration_input_placeholder_country_code: "Select a country code",
                item_card_remove_from_favorites_tag_title: "Delete from Favorites",
                item_card_add_to_favorites_tag_title: "Add to Favorites",
                item_card_label_favorite: "Favorite",
                item_card_label_favorited: "Favorited",
                item_card_label_compare: "Compare",
                item_card_label_in_compare: "In compare",
                sending_message_form_loader: "Sending message...",
                sending_file_form_loader: "Sending files...",
            },
            }
};
