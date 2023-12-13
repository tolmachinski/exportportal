var LocationPartialModule = (function (global) {
    /**
     * @typedef {{ addressRadios: ?string, savedAddress: ?string, overridedAddress: ?string, addressesWrapper: ?string, addressInput: ?string }} Selectors
     * @typedef {{ addressRadios: ?JQuery, savedAddress: ?JQuery, overridedAddress: : ?JQuery, addressesWrapper: ?JQuery, addressInput: ?JQuery }} CustomElements
     */

    /**
     * @type {JQueryElements}
     */
    var defaultElements = { addressRadios: null, savedAddress: null, overridedAddress: null, addressesWrapper: null, addressInput: null };

    /**
     * @type {Selectors}
     */
    var defaultSelectors = { addressRadios: null, savedAddress: null, overridedAddress: null, addressesWrapper: null, addressInput: null };

    /**
     * Handles the change of the address type.
     *
     * @param {JQuery} addressesWrapper
     * @param {JQuery} overridedAddress
     * @param {string} addressSelector
     */
    function onAddressTypeCheck(addressesWrapper, overridedAddress, addressSelector) {
        if (null === addressesWrapper) {
            return;
        }

        var self = $(this);
        var addressInput = $(addressesWrapper).find("input")[2];
        var validationClass = addressInput.data("validation-template") || "";
        if (self.get(0) === addressesWrapper.find('input')[0]) {
            addressInput.prev(".formError").remove();
            addressInput.removeClass(validationClass).removeClass("validengine-border");
        } else {
            addressInput.addClass(validationClass);
        }

        if (self.hasClass("validengine-border")) {
            self.removeClass("validengine-border");
        }
    }

    /**
     * Handles the click on custom address input.
     *
     * @param {JQuery} overridedAddress
     * @param {any} currentLocation
     * @param {any} initializationParams
     * @param {string} locationTemplate
     */
    function onClickCustomAddress(overridedAddress, currentLocation, initializationParams, locationTemplate) {
        var self = $(this);
        var address = locationTemplate;

        showLoader(self.closest("form"), 'Sending...', 'fixed');
        openLocationPopup(initializationParams, currentLocation, function () {
            hideLoader(self.closest("form"));
        })
            .then(function (location) {
                // Update current location
                Object.assign(currentLocation, location);

                for (var key in location) {
                    if (location.hasOwnProperty(key)) {
                        address = address.replace(new RegExp("{{" + key + "}}", "g"), location[key].name);
                    }
                }

                self.val(address);
                self.removeClass("validengine-border").prev(".formError").remove();
                // using JQuery as Event Hub
                $(global).trigger("locations:override-location", {
                    location: currentLocation,
                    serialized: Object.keys(currentLocation)
                        .map(function (key) {
                            if (/^.*_show$/i.test(key)) {
                                return null;
                            }

                            return { name: key, value: currentLocation[key] ? currentLocation[key].value || null : null };
                        })
                        .filter(function (f) {
                            return f;
                        }),
                });
            })
            .catch(function (error) {
                if (error && __debug_mode) {
                    console.error(error);
                }
            });

        if (overridedAddress && $.fn.icheck) {
            overridedAddress.icheck("checked", function (node) {
                var radio = $(node);
                var validationClass = radio.data("validationTemplate") || "";

                self.addClass(validationClass);
            });
        }
    }

    function entrypoint(params) {
        /** @type Selectors selectors */
        var selectors = Object.assign({}, defaultSelectors, params.selectors || {});
        /** @type CustomElements elements */
        var elements = Object.assign({}, defaultElements, findElementsFromSelectors(selectors, Object.keys(defaultElements)));
        var locationTemplate = params.locationTemplate || "{{address}}, {{country}}, {{state}}, {{city}}, {{postal_code}}";
        var currentLocation = params.location || {};
        var locationConfig = params.locationConfig || {};

        if (elements.addressesWrapper) {
            elements.addressesWrapper.on(
                "click",
                selectors.addressInput,
                preventDefault(function () {
                    onClickCustomAddress.call(this, elements.overridedAddress, currentLocation, locationConfig, locationTemplate);
                })
            );
        }

        if (elements.addressRadios) {
            elements.addressRadios.on(
                "change",
                preventDefault(function () {
                    onAddressTypeCheck.call(this, elements.addressesWrapper, selectors.addressInput);
                })
            );
        }
    }

    return {
        default: entrypoint,
    };
})(globalThis);
