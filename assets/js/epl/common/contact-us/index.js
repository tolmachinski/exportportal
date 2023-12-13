import $ from "jquery";
import { addCounter } from "@src/plugins/textcounter/index";
import Contact from "@src/epl/common/contact-us/Contact";
import EventHub from "@src/event-hub";

const loadingInputMask = obj => {
    obj.initCountryCodeSelect();
};

// Lazy loading Select 2
const lazyLoadingSelect2Fn = async (e, button, obj) => {
    await import("select2").then(() => {
        EventHub.off("lazy-loading:select2-phone-number", lazyLoadingSelect2Fn);
        obj.initSelect2();
        button.remove();
    });

    $("#js-country-code").select2("open");
};

export default showLoaderTranslate => {
    addCounter($(".js-textcounter-contact-message"));

    const contact = new Contact({ showLoaderTranslate });

    EventHub.on("lazy-loading:select2-phone-number", (e, button) => {
        lazyLoadingSelect2Fn(e, button, contact);
    });

    // Lazy loading Input Mask
    $("#js-epl-register-phone-number").one("click focus", loadingInputMask.bind(loadingInputMask, contact));

    $(() => {
        setTimeout(() => {
            contact.selectCountryCode.find("option[data-country-flag]").first().prop("selected", true);
            contact.selectCountryCode.trigger("change");
        }, 100);
    });
};
