var InitPhoneMaskLegacyFragment = (function () {
    function entrypoint (params) {

        userPhoneMask.init({
            selectedFax: params.selectedFax || 0,
            selectedPhone: params.selectedPhone || 0,
            selectorPhoneCod: params.countryCodeSelector,
            selectorPhoneNumber: params.phoneNumberSelector,
            textErorCountryCode: params.textErorCountryCode,
            textErorPhoneMask: params.textErorPhoneMask,
            dropdownParent: params.dropdownParentSelector ? $(params.dropdownParentSelector) : $("body"),
        });
    }

    return {
        default: entrypoint,
    };
})();
