import $ from "jquery";
import mix from "@src/util/common/mix";
import PhoneMask from "@src/common/phone-mask/PhoneMask";
import EventHub from "@src/event-hub";

export default params => {
    const phoneMask = new PhoneMask(params);

    // Lazy loading Select 2
    const lazyLoadingSelect2Fn = async (button, openDropdown = true) => {
        await import("select2").then(() => {
            EventHub.off("lazy-loading:select2", lazyLoadingSelect2Fn);
            phoneMask.initSelect2();
            button.remove();
        });

        if (openDropdown) {
            phoneMask.selectCountryCode.select2("open");
        }
    };

    EventHub.on("lazy-loading:select2", (e, btn) => {
        lazyLoadingSelect2Fn(btn, true);
    });

    // Lazy loading Input Mask
    const lazyLoadingInputMask = async () => {
        phoneMask.phoneNumber.off("click focus", lazyLoadingInputMask);
        phoneMask.initCountryCodeSelect();
        phoneMask.maskIsComplete = true;
    };
    phoneMask.phoneNumber.on("click focus", lazyLoadingInputMask);

    mix(
        globalThis,
        {
            checkPhoneMask: phoneMask.onCheckPhoneMask.bind(phoneMask),
        },
        false
    );

    $(() => {
        if (phoneMask.selectedPhone) {
            lazyLoadingSelect2Fn($(phoneMask.lazyLoaderBtnSelector), false);
            phoneMask.phoneNumber.trigger("click");
        } else {
            setTimeout(() => {
                phoneMask.selectCountryCode.find("option[data-country-flag]").first().prop("selected", true);
                phoneMask.selectCountryCode.trigger("change");
            }, 100);
        }
    });
};
