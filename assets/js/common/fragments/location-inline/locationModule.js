import $ from "jquery";
import EventHub from "@src/event-hub";
import Platform from "@src/epl/platform";

const locationModule = (dialog = false, extended = false, searchUrl, selectors, texts) => {
    const data = { dialog, extended, searchUrl, selectors, texts };
    let locationModuleInit = false;

    EventHub.on("lazy-loading:location-module", async () => {
        if (!locationModuleInit) {
            await import("@src/common/fragments/location-inline/location").then(({ default: LocationInlineModule }) => LocationInlineModule(data));
            // $(selectors.country)
            locationModuleInit = true;
        }
        const countrySelectRegistration = $("#js-register-input-country");
        countrySelectRegistration.val($("#js-country-code option:selected").data("country"));
        countrySelectRegistration.trigger("change");
    });

    if (Platform.eplPage) {
        EventHub.trigger("lazy-loading:location-module");
    }
};

export default locationModule;
