/** eslint-disable */
(function() {
	"use strict";

    window.userPhoneMask = ({
        init: function (params) {
            userPhoneMask.self = this;

            userPhoneMask.textErorCountryCode = '- ' + params.textErorCountryCode;
            userPhoneMask.textErorPhoneMask = '- ' + params.textErorPhoneMask;
            userPhoneMask.dropdownParent = params.dropdownParent;

            //region phone
            userPhoneMask.selectedPhone = params.selectedPhone;
            userPhoneMask.maskIsSelected = false;
            userPhoneMask.maskIsComplete = false;
            userPhoneMask.$selectCcode;

            userPhoneMask.$selectCountryCode = $(params.selectorPhoneCod);
            userPhoneMask.$phoneNumber = $(params.selectorPhoneNumber);
            //endregion phone

            //region fax
            userPhoneMask.selectedFax = params.selectedFax;
            userPhoneMask.maskIsSelectedFax = false;
            userPhoneMask.maskIsCompleteFax = false;
            userPhoneMask.$selectCcodeFax;

            userPhoneMask.$selectFaxCode = params.selectorFaxCod? $(params.selectorFaxCod) : null;
            userPhoneMask.$faxNumber = params.selectorFaxNumber? $(params.selectorFaxNumber) : null;
            //endregion fax

            userPhoneMask.self.initPlug();
            userPhoneMask.self.initListiners();
        },
        initPlug: function(){
            userPhoneMask.self.initCountryCodeSelect();

            setTimeout(function(){
                if(userPhoneMask.selectedPhone === 0){
                    userPhoneMask.$selectCountryCode.find('option[data-country-flag]').first().prop('selected', true);
                    userPhoneMask.$selectCountryCode.trigger("change");
                }else{
                    userPhoneMask.self.initCountryCodeMask(userPhoneMask.$selectCountryCode);
                }

                if (userPhoneMask.$selectFaxCode) {
                    if(userPhoneMask.selectedFax === 0){
                        userPhoneMask.$selectFaxCode.find('option[data-country-flag]').first().prop('selected', true);
                        userPhoneMask.$selectFaxCode.trigger("change");
                    }else{
                        userPhoneMask.self.initFaxCodeMask(userPhoneMask.$selectFaxCode);
                    }
                }
            }, 100);

            jQuery(window).on('resizestop', function () {
                var openedCountryCode = $(".select-country-code-group .select2-container--open");
                if (openedCountryCode.length) {
                    openedCountryCode.prev("select").select2("close").select2("open");
                }
            });
        },
        initListiners: function(){
            mix(
                window,
                {
                    checkPhoneMask: userPhoneMask.self.onCheckPhoneMask,
                    checkFaxMask: userPhoneMask.self.onCheckFaxMask,
                },
                false
            );

            userPhoneMask.$selectCountryCode.change(function(){

            });
        },
        initCountryCodeMask: function($select){
            var $selected = $select.find('option:selected');
            var phoneMask = $selected.data('phoneMask') || null;


            if ($selected.length) {
                userPhoneMask.maskIsSelected = true;
            } else {
                userPhoneMask.maskIsSelected = false;
            }

            if (userPhoneMask.$phoneNumber.hasClass('validengine-border')) {
                userPhoneMask.$phoneNumber
                    .removeClass('validengine-border')
                    .prev('.formError')
                    .remove();
            }

            if (null !== phoneMask) {
                userPhoneMask.$phoneNumber.inputmask(
                    {
                        mask: phoneMask.replace(/\_/g, '9').replace(/\*/g, 'a'), // Replacing original mask syntax with inputmask-defined syntax, _ - is digit, * - is alphabetic
                        keepStatic: true,
                        oncomplete: function () { userPhoneMask.maskIsComplete = true; },
                        onincomplete: function () { userPhoneMask.maskIsComplete = false; }
                    }
                );

                if(
                    userPhoneMask.$phoneNumber.val() != ""
                    && userPhoneMask.$phoneNumber.val().search(/_/g) == -1
                ){
                    userPhoneMask.maskIsComplete = true;
                }else{
                    userPhoneMask.maskIsComplete = false;
                }

                userPhoneMask.$phoneNumber.on('paste', function () {
                    if (userPhoneMask.$phoneNumber.inputmask("isComplete")){
                        userPhoneMask.maskIsComplete = true;
                    }else{
                        userPhoneMask.maskIsComplete = false;
                    }
                });
            }
        },
        initFaxCodeMask: function($select){
            var $selected = $select.find('option:selected');
            var phoneMask = $selected.data('phoneMask') || null;

            // console.log($selected.length);
            if ($selected.length) {
                userPhoneMask.maskIsSelectedFax = true;
            } else {
                userPhoneMask.maskIsSelectedFax = false;
            }

            if (userPhoneMask.$faxNumber.hasClass('validengine-border')) {
                userPhoneMask.$faxNumber
                    .removeClass('validengine-border')
                    .prev('.formError')
                    .remove();
            }
            // console.log(phoneMask);
            if (null !== phoneMask) {
                userPhoneMask.$faxNumber.inputmask(
                    {
                        mask: phoneMask.replace(/\_/g, '9').replace(/\*/g, 'a'), // Replacing original mask syntax with inputmask-defined syntax, _ - is digit, * - is alphabetic
                        keepStatic: true,
                        oncomplete: function () { userPhoneMask.maskIsCompleteFax = true; },
                        onincomplete: function () { userPhoneMask.maskIsCompleteFax = false; }
                    }
                );

                if(
                    userPhoneMask.$faxNumber.val() != ""
                    && userPhoneMask.$faxNumber.val().search(/_/g) == -1
                ){
                    userPhoneMask.maskIsCompleteFax = true;
                }else{
                    userPhoneMask.maskIsCompleteFax = false;
                }

                userPhoneMask.$faxNumber.on('paste', function () {
                    if (userPhoneMask.$faxNumber.inputmask("isComplete")){
                        userPhoneMask.maskIsCompleteFax = true;
                    }else{
                        userPhoneMask.maskIsCompleteFax = false;
                    }
                });
            }
        },
        initCountryCodeSelect: function(){
            //region phone
            userPhoneMask.$selectCountryCode.change(function(){
                userPhoneMask.self.initCountryCodeMask($(this));
            });

            userPhoneMask.$selectCcode = userPhoneMask.$selectCountryCode.select2({
                placeholder: translate_js({ plug: 'general_i18n', text: 'register_label_country_code_placeholder' }),
                allowClear: false,
                language: __site_lang,
                templateResult: formatCcodeReg,
                templateSelection: formatCcodeRegSelection,
                width: 'auto',
                dropdownAutoWidth : true,
                dropdownParent: userPhoneMask.dropdownParent
            });

            userPhoneMask.$selectCcode.data('select2').$container.attr('id', 'country-code--formfield--code-container')
                .addClass('validate[required]')
                .setValHookType('selectCcode');

            $.valHooks.selectCcode = {
                get: function (el) {
                    return userPhoneMask.$selectCcode.val() || [];
                },
                set: function (el, val) {
                    userPhoneMask.$selectCcode.val(val);
                }
            };
            //endregion phone

            //region fax
            if (userPhoneMask.$selectFaxCode) {
                userPhoneMask.$selectFaxCode.change(function(){
                    userPhoneMask.self.initFaxCodeMask($(this));
                });

                userPhoneMask.$selectCcodeFax = userPhoneMask.$selectFaxCode.select2({
                    placeholder: translate_js({ plug: 'general_i18n', text: 'register_label_country_code_placeholder' }),
                    allowClear: false,
                    language: __site_lang,
                    templateResult: formatCcodeReg,
                    templateSelection: formatCcodeRegSelection,
                    width: 'auto',
                    dropdownAutoWidth : true
                });

                userPhoneMask.$selectCcodeFax.data('select2').$container.attr('id', 'country-code-fax--formfield--code-container')
                    .addClass('validate[required]')
                    .setValHookType('selectCcodeFax');

                $.valHooks.selectCcodeFax = {
                    get: function (el) {
                        return userPhoneMask.$selectCcodeFax.val() || [];
                    },
                    set: function (el, val) {
                        userPhoneMask.$selectCcodeFax.val(val);
                    }
                };
            }
            //endregion fax


            function formatCcodeRegSelection(cCode) {
                if (!cCode.id) {
                    return cCode.text;
                }

                var data = cCode.element.dataset || {};

                return $('<img class="select-country-flag" width="32" height="32" src="' + (data.countryFlag || null) + '" alt="' + (data.countryName || '') + '"/>' +
                        '<span>' + (data.code || '') + '</span>');
            }

            function formatCcodeReg(cCode) {
                if (cCode.loading){
                    return cCode.text;
                }

                var element = cCode.element;
                var data = element.dataset || {};

                return $(
                    '<span class="flex-display flex-ai--c notranslate">' +
                        '<img class="w-16 h-16 mr-10" src="' + (data.countryFlag || null) + '" alt="' + (data.countryName || '') + '"/>' +
                        '<span>' + (element.innerText || element.textContent || data.countryName || '') + '</span>' +
                    '</span>'
                );
            }
        },
        onCheckPhoneMask: function(field, rules, i, options){
            if(userPhoneMask.maskIsSelected == false){
                return userPhoneMask.textErorCountryCode;
            }

            if(userPhoneMask.maskIsComplete == false){
                return userPhoneMask.textErorPhoneMask;
            }
        },
        onCheckFaxMask: function(field, rules, i, options){
            if(
                userPhoneMask.maskIsSelectedFax == false
            ){
                return userPhoneMask.textErorCountryCode;
            }

            if(
                userPhoneMask.maskIsCompleteFax == false
                && userPhoneMask.$faxNumber.val() != ""
            ){
                return userPhoneMask.textErorPhoneMask;
            }else{
                userPhoneMask.$faxNumber.removeClass('validengine-border');
            }
        }
    });

}());
