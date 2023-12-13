var LocationInlineModule = (function (global, $) {
    "use strict";

    //#region Declarations
    /**
     * @typedef {Object} ModuleParameters
     * @property {boolean} [dialog]
     * @property {boolean} [extended]
     * @property {any} [selectors]
     */

    /**
     * @typedef {Object} CustomElements
     * @property {JQuery} country
     * @property {JQuery} [region]
     * @property {JQuery} [input]
     * @property {JQuery} [city]
     */

    /**
     * @typedef {Object} Selectors
     * @property {string} country
     * @property {string} [region]
     * @property {string} [city]
     */

    /**
     * @typedef {Object} Texts
     * @property {string} cityPlaceholder
     * @property {string} regionPlaceholder
     * @property {string} optionPlaceholder
     */
    //#endregion Declarations

    //#region Variables
    /**
     * @type {CustomElements}
     */
    var defaultElements = { country: null, region: null, city: null, input: null };

    /**
     * @type {Selectors}
     */
    var defaultSelectors = { country: null, region: null, city: null, input: null };

    /**
     * @type {Texts}
     */
    var defaultTexts = {
        cityPlaceholder: "Select your city",
        regionPlaceholder: "Select your state or region",
        optionPlaceholder: "Write your location",
    };

    /**
     * @type {any}
     */
    var defaultSearchOptions = {
        width: "100%",
        theme: SEARCHABLE_LIST_THEME_CLASS,
        language: global.__site_lang || "en",
        placeholder: null,
        minimumInputLength: 2,
        escapeMarkup: function (markup) {
            return markup;
        },
        templateResult: function (entry) {
            if (entry.loading) {
                return entry.text;
            }

            return entry.name || null;
        },
        templateSelection: function (entry) {
            return entry.name || entry.text;
        },
    };

    var SEARCHABLE_LIST_CLASS = ".select2";
    var FORMFIELD_WRAPPER_SELECTOR = ".form-group";
    var SEARCHABLE_REGIONS_WRAPPER_CLASS = "wr-select2-h50";
    var SEARCHABLE_LIST_PALCEHOLDER_CLASS = ".select2-selection__placeholder";
    var SEARCHABLE_LIST_MESSAGE_LOAD_MORE_CLASS = ".select2-results__option--load-more";
    var SEARCHABLE_LIST_CUSTOM_FIELD_CLASS_NAME = "select2-results__message--show-custom-fields";
    var SEARCHABLE_LIST_CUSTOM_FIELD_CLASS = ".select2-results__message--show-custom-fields";
    var SEARCHABLE_LIST_MESSAGE_CLASS = ".select2-results__message";
    var SEARCHABLE_LIST_THEME_CLASS = "default ep-select2-h30";
    var REGIONS_WRAPPER_TYPE = "select2Region";
    var CITIES_WRAPPER_TYPE = "select2City";
    var REGIONS_WRAPPER_ID = "select-regions--formfield--tags-container";
    var CITIES_WRAPPER_ID = "select-city--formfield--tags-container";
    //#endregion Variables

    //#region Functions
    /**
     * Makes advanced regions list.
     *
     * @param {JQuery} region
     * @param {string} url
     * @param {JQuery} country
     * @param {Texts} texts
     */
    function makeAdvancedRegions(region, url, country, texts) {
        if (typeof url !== "string") {
            throw new TypeError("The url must be of the string type.");
        }

        var getCountry = function () {
            if (country instanceof jQuery || country instanceof HTMLElement) {
                return $(country).val() || null;
            }

            return null;
        };

        var options = Object.assign({}, defaultSearchOptions, {
            ajax: {
                url: url,
                type: "POST",
                delay: 250,
                dataType: "json",
                data: function (params) {
                    return {
                        page: params.page,
                        search: params.term,
                        country: getCountry(),
                    };
                },
                processResults: function (data, params) {
                    var page = params.page || 1;
                    var total = data.total || 1;
                    var perPage = data.perPage || 10;
                    var entries = data.items || [];

                    return {
                        results: entries,
                        pagination: {
                            more: page * perPage < total,
                        },
                    };
                },
            },
            placeholder: texts.regionPlaceholder,
        });

        region.select2(options);
        region.data("select2").on("results:message", function (e) {
            this.dropdown._positionDropdown();
        });

        if (region.find("option").length < 2) {
            region.prop("disabled", true);
        }
    }

    /**
     * Makes advanced cities list.
     *
     * @param {JQuery} city
     * @param {JQuery} region
     * @param {Texts} texts
     */
    function makeAdvancedCities(city, region, texts) {
        initSelectCity(city, texts.cityPlaceholder, region);
    }

    /**
     * Appends location option to the select2 list.
     *
     * @param {Texts} texts
     */
    function appendLocationOption(texts) {
        var shiftAttention = function (handler) {
            /** @type {JQuery} message */
            var message = handler.$results.find(SEARCHABLE_LIST_MESSAGE_CLASS).last();
            if (message.length) {
                message.addClass("txt-gray");
            }
        };
        var makeOption = function (handler) {
            var newOption = $(handler.results.option({ id: -1, name: texts.optionPlaceholder, disabled: false, attr: { style: null } }));
            newOption.addClass(SEARCHABLE_LIST_CUSTOM_FIELD_CLASS_NAME).show();
            handler.$results.append(newOption);
            handler.dropdown._positionDropdown();
            handler.dropdown._resizeDropdown();
        };

        return function () {
            if (this.results.loading || 0 !== this.$results.find(SEARCHABLE_LIST_MESSAGE_LOAD_MORE_CLASS).length) {
                return;
            }

            var messageElement = this.$results.find(SEARCHABLE_LIST_CUSTOM_FIELD_CLASS);
            if (0 != messageElement.length) {
                this.$results.append(messageElement);
            } else {
                makeOption(this);
            }
            shiftAttention(this);
        };
    }

    /**
     * Makes select2 list aware about custom locations list.
     *
     * @param {JQuery} list
     * @param {Texts} texts
     */
    function makeListLocationAware(list, texts) {
        list.closest(FORMFIELD_WRAPPER_SELECTOR).addClass(SEARCHABLE_REGIONS_WRAPPER_CLASS);
        list.data("select2").on("results:all", appendLocationOption(texts));
        list.data("select2").on("results:append", appendLocationOption(texts));
        list.data("isSearchable", true);
    }

    /**
     * Adds validation to select2 list.
     *
     * @param {JQuery} list
     * @param {string} id
     * @param {string} type
     */
    function addValidationToList(list, id, type) {
        list.data("select2")
            .$container.attr("id", id)
            .addClass(list.data("validationTemplate") || null)
            .setValHookType(type);

        $.valHooks[type] = {
            get: function () {
                return list.val() || [];
            },
            set: function (el, val) {
                list.val(val);
            },
        };
    }

    /**
     * Updates regions list.
     *
     * @param {JQuery} regions
     * @param {JQuery} country
     */
    function updateRegions(regions, country) {
        if (null === regions) {
            return Promise.resolve();
        }

        var isSearchable = regions.data("isSearchable") || false;
        var hasCountry = Boolean(~~country.val() || null);
        regions.prop("disabled", true);

        return new Promise(function (resolve) {
            if (!isSearchable) {
                selectCountry(country, regions, "register").done(function () {
                    resolve();
                });
            } else {
                regions.empty().trigger("change");
                resolve();
            }
        }).then(function () {
            regions.prop("disabled", !hasCountry);
        });
    }

    //#region Handlers
    /**
     * Handles the country override.
     *
     * @param {string|number} country
     * @param {JQuery} node
     * @param {JQuery} elements
     */
    function onCountryOverride(country, node, elements) {
        node.find('option[value="' + country + '"]').prop("selected", true);

        onChangeCountry(node, elements);
    }

    /**
     * Handles the country change events.
     *
     * @param {JQuery} node
     * @param {CustomElements} elements
     */
    function onChangeCountry(node, elements) {
        updateRegions(elements.region, node);
        if (elements.city) {
            elements.city.empty().trigger("change").prop("disabled", true);
        }

        $(global).trigger("locations:inline:change-coutry", { country: node.val() || null });
    }

    /**
     * Handles the region change events.
     *
     * @param {JQuery} node
     * @param {CustomElements} elements
     * @param {Texts} texts
     */
    function onChangeRegion(node, elements, texts) {
        var region = node.val() || null;
        var hasRegion = Boolean(~~region);
        var placeholder = region ? texts.cityPlaceholder : texts.regionPlaceholder;
        var isSearchable = node.data("isSearchable") || false;
        /**
         * Update city element.
         *
         * @param {JQuery} city
         */
        var updateCity = function (city) {
            city.closest(FORMFIELD_WRAPPER_SELECTOR).show();
            city.siblings(SEARCHABLE_LIST_CLASS).find(SEARCHABLE_LIST_PALCEHOLDER_CLASS).text(placeholder);
            city.empty().trigger("change").prop("disabled", !hasRegion);
        };
        /**
         * Processes searchable regions.
         *
         * @param {any} region
         * @param {JQuery} [city]
         * @param {JQuery} [input]
         */
        var processSearchableRegion = function (region, city, input) {
            if (-1 === parseInt(region, 10)) {
                city && city.closest(FORMFIELD_WRAPPER_SELECTOR).hide();
                input && input.closest(FORMFIELD_WRAPPER_SELECTOR).show().find("input").prop("disabled", false);
            } else {
                city && updateCity(city);
                input && input.closest(FORMFIELD_WRAPPER_SELECTOR).hide().find("input").prop("disabled", true);
            }
        };

        if (isSearchable) {
            processSearchableRegion(region, elements.city, elements.input);
        } else {
            updateCity(elements.city);
        }

        $(global).trigger("locations:inline:change-region", { region: region });
    }

    /**
     * Handles the city change events.
     *
     * @param {JQuery} node
     * @param {CustomElements} elements
     */
    function onChangeCity(node, elements) {
        var city = node.val() || null;
        var isSearchable = node.data("isSearchable") || false;
        /**
         * Processes searchable cities.
         *
         * @param {any} region
         * @param {JQuery} [city]
         * @param {JQuery} [input]
         */
        var processSearchableCity = function (id, city, input) {
            if (-1 === parseInt(id, 10)) {
                city && city.closest(FORMFIELD_WRAPPER_SELECTOR).hide();
                input && input.closest(FORMFIELD_WRAPPER_SELECTOR).show().find("input").prop("disabled", false);
            } else {
                city && city.closest(FORMFIELD_WRAPPER_SELECTOR).show();
                input && input.closest(FORMFIELD_WRAPPER_SELECTOR).hide().find("input").prop("disabled", true);
            }
        };

        if (isSearchable) {
            processSearchableCity(city, elements.city, elements.input);
        }

        $(global).trigger("locations:inline:change-city", { city: city });
    }
    //#endregion Handlers
    //#endregion Functions

    /**
     * @param {ModuleParameters} params
     */
    function entrypoint(params) {
        /** @type {Texts} texts */
        var texts = Object.assign({}, defaultTexts, params.texts || {});
        /** @type {boolean} extended */
        var extended = Boolean(~~params.extended);
        /** @type {string} searchUrl */
        var searchUrl = params.searchUrl || null;
        /** @type {Selectors} selectors */
        var selectors = Object.assign({}, defaultSelectors, params.selectors || {});
        /** @type {CustomElements} elements */
        var elements = Object.assign({}, defaultElements, findElementsFromSelectors(selectors, Object.keys(defaultElements)));
        if (null === selectors.country || null === elements.country) {
            throw new TypeError("The country is required");
        }

        if (elements.city) {
            makeAdvancedCities(elements.city, elements.region, texts);
            if (extended) {
                makeListLocationAware(elements.city, texts);
                addValidationToList(elements.city, CITIES_WRAPPER_ID, CITIES_WRAPPER_TYPE);
            }
        }

        if (elements.region) {
            if (extended) {
                makeAdvancedRegions(elements.region, searchUrl, elements.country, texts);
                makeListLocationAware(elements.region, texts);
                addValidationToList(elements.region, REGIONS_WRAPPER_ID, REGIONS_WRAPPER_TYPE);
            }
        }

        //#region Listeners
        elements.country.on("change", onChangeCountry.bind(null, elements.country, elements));
        if (elements.region) {
            elements.region.on("change", onChangeRegion.bind(null, elements.region, elements, texts));
        }
        if (elements.city) {
            elements.city.on("change", onChangeCity.bind(null, elements.city, elements));
        }

        $(global).on("locations:inline:override-coutry", function (e, data) {
            onCountryOverride(data.country || null, elements.country, elements);
        });
        //#endregion Listeners
    }

    return {
        default: entrypoint,
    };
})(globalThis, jQuery);
