import $ from "jquery";
import initSelectCity from "@src/util/common/init-select-city";
import findElementsFromSelectors from "@src/util/common/find-elements-from-selectors";
import selectCountry from "@src/util/common/select-country";

import { LANG } from "@src/common/constants";

// #region Declarations
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
// #endregion Declarations

// #region Variables
/**
 * @type {CustomElements}
 */
const defaultElements = { country: null, region: null, city: null, input: null };

/**
 * @type {Selectors}
 */
const defaultSelectors = { country: null, region: null, city: null, input: null };

/**
 * @type {Texts}
 */
const defaultTexts = {
    cityPlaceholder: "Select your country",
    regionPlaceholder: "Select your state or region",
    optionPlaceholder: "Write your location",
};

const SEARCHABLE_LIST_CLASS = ".select2";
const FORMFIELD_WRAPPER_SELECTOR = ".form-group";
const SEARCHABLE_REGIONS_WRAPPER_CLASS = "wr-select2-h50";
const SEARCHABLE_LIST_PALCEHOLDER_CLASS = ".select2-selection__placeholder";
const SEARCHABLE_LIST_MESSAGE_LOAD_MORE_CLASS = ".select2-results__option--load-more";
const SEARCHABLE_LIST_CUSTOM_FIELD_CLASS_NAME = "select2-results__message--show-custom-fields";
const SEARCHABLE_LIST_CUSTOM_FIELD_CLASS = ".select2-results__message--show-custom-fields";
const SEARCHABLE_LIST_MESSAGE_CLASS = ".select2-results__message";
const SEARCHABLE_LIST_THEME_CLASS = "default ep-select2-h30";
const REGIONS_WRAPPER_TYPE = "select2Region";
const CITIES_WRAPPER_TYPE = "select2City";
const REGIONS_WRAPPER_ID = "select-regions--formfield--tags-container";
const CITIES_WRAPPER_ID = "select-city--formfield--tags-container";

/**
 * @type {any}
 */
const defaultSearchOptions = {
    width: "100%",
    theme: SEARCHABLE_LIST_THEME_CLASS,
    // eslint-disable-next-line no-underscore-dangle
    language: LANG || "en",
    placeholder: null,
    minimumInputLength: 2,
    dropdownParent: $("#js-select2-state-wr"),
    escapeMarkup(markup) {
        return markup;
    },
    templateResult(entry) {
        if (entry.loading) {
            return entry.text;
        }

        return entry.name || null;
    },
    templateSelection(entry) {
        return entry.name || entry.text;
    },
};
// #endregion Variables

// #region Functions
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

    const getCountry = function () {
        if (country instanceof $ || country instanceof HTMLElement) {
            return $(country).val() || null;
        }

        return null;
    };

    const options = {
        ...defaultSearchOptions,
        ajax: {
            url,
            type: "POST",
            delay: 250,
            dataType: "json",
            data(params) {
                return {
                    page: params.page,
                    search: params.term,
                    country: getCountry(),
                };
            },
            processResults(data, params) {
                const page = params.page || 1;
                const total = data.total || 1;
                const perPage = data.perPage || 10;
                const entries = data.items || [];

                return {
                    results: entries,
                    pagination: {
                        more: page * perPage < total,
                    },
                };
            },
        },
        placeholder: texts.regionPlaceholder,
    };
    const resultMessage = function () {
        // eslint-disable-next-line no-underscore-dangle
        this.dropdown._positionDropdown();
    };

    region.select2(options);
    region.data("select2").on("results:message", resultMessage);

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
    const shiftAttention = function (handler) {
        /** @type {JQuery} message */
        const message = handler.$results.find(SEARCHABLE_LIST_MESSAGE_CLASS).last();
        if (message.length) {
            message.addClass("txt-gray");
        }
    };
    const makeOption = function (handler) {
        const newOption = $(handler.results.option({ id: -1, name: texts.optionPlaceholder, disabled: false, attr: { style: null } }));
        newOption.addClass(SEARCHABLE_LIST_CUSTOM_FIELD_CLASS_NAME).show();
        handler.$results.append(newOption);
        // eslint-disable-next-line no-underscore-dangle
        handler.dropdown._positionDropdown();
        // eslint-disable-next-line no-underscore-dangle
        handler.dropdown._resizeDropdown();
    };

    const returnFn = function () {
        if (this.results.loading || this.$results.find(SEARCHABLE_LIST_MESSAGE_LOAD_MORE_CLASS).length !== 0) {
            return;
        }

        const messageElement = this.$results.find(SEARCHABLE_LIST_CUSTOM_FIELD_CLASS);
        if (messageElement.length !== 0) {
            this.$results.append(messageElement);
        } else {
            makeOption(this);
        }
        shiftAttention(this);
    };

    return returnFn;
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
        get() {
            return list.val() || [];
        },
        set(el, val) {
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
    if (regions === null) {
        return Promise.resolve();
    }

    const isSearchable = regions.data("isSearchable") || false;
    const hasCountry = Boolean(~~country.val() || null);
    regions.prop("disabled", true);

    return new Promise(resolve => {
        if (!isSearchable) {
            selectCountry(country, regions, "register").done(() => {
                resolve();
            });
        } else {
            regions.empty().trigger("change");
            resolve();
        }
    }).then(() => {
        regions.prop("disabled", !hasCountry);
    });
}

// #region Handlers
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

    $(globalThis).trigger("locations:inline:change-coutry", { country: node.val() || null });
}

/**
 * Handles the country override.
 *
 * @param {string|number} country
 * @param {JQuery} node
 * @param {JQuery} elements
 */
function onCountryOverride(country, node, elements) {
    node.find(`option[value="${country}"]`).prop("selected", true);

    onChangeCountry(node, elements);
}

/**
 * Handles the region change events.
 *
 * @param {JQuery} node
 * @param {CustomElements} elements
 * @param {Texts} texts
 */
function onChangeRegion(node, elements, texts) {
    const region = node.val() || null;
    const hasRegion = Boolean(~~region);
    const placeholder = region ? texts.cityPlaceholder : texts.regionPlaceholder;
    const isSearchable = node.data("isSearchable") || false;
    /**
     * Update city element.
     *
     * @param {JQuery} city
     */
    const updateCity = function (city) {
        city.closest(FORMFIELD_WRAPPER_SELECTOR).show();
        city.siblings(SEARCHABLE_LIST_CLASS).find(SEARCHABLE_LIST_PALCEHOLDER_CLASS).text(placeholder);
        city.empty().trigger("change").prop("disabled", !hasRegion);
    };
    /**
     * Processes searchable regions.
     *
     * @param {any} sRegion
     * @param {JQuery} [sCity]
     * @param {JQuery} [input]
     */
    const processSearchableRegion = function (sRegion, sCity, input) {
        if (parseInt(sRegion, 10) === -1) {
            if (sCity) sCity.closest(FORMFIELD_WRAPPER_SELECTOR).hide();
            if (input) input.closest(FORMFIELD_WRAPPER_SELECTOR).show().find("input").prop("disabled", false);
        } else {
            if (sCity) updateCity(sCity);
            if (input) input.closest(FORMFIELD_WRAPPER_SELECTOR).hide().find("input").prop("disabled", true);
        }
    };

    if (isSearchable) {
        processSearchableRegion(region, elements.city, elements.input);
    } else {
        updateCity(elements.city);
    }

    $(globalThis).trigger("locations:inline:change-region", { region });
}

/**
 * Handles the city change events.
 *
 * @param {JQuery} node
 * @param {CustomElements} elements
 */
function onChangeCity(node, elements) {
    const city = node.val() || null;
    const isSearchable = node.data("isSearchable") || false;
    /**
     * Processes searchable cities.
     *
     * @param {any} id
     * @param {JQuery} [sCity]
     * @param {JQuery} [input]
     */
    const processSearchableCity = function (id, sCity, input) {
        if (parseInt(id, 10) === -1) {
            if (sCity) sCity.closest(FORMFIELD_WRAPPER_SELECTOR).hide();
            if (input) input.closest(FORMFIELD_WRAPPER_SELECTOR).show().find("input").prop("disabled", false);
        } else {
            if (sCity) sCity.closest(FORMFIELD_WRAPPER_SELECTOR).show();
            if (input) input.closest(FORMFIELD_WRAPPER_SELECTOR).hide().find("input").prop("disabled", true);
        }
    };

    if (isSearchable) {
        processSearchableCity(city, elements.city, elements.input);
    }

    $(globalThis).trigger("locations:inline:change-city", { city });
}
// #endregion Handlers
// #endregion Functions

/**
 * @param {ModuleParameters} params
 */
export default params => {
    /** @type {Texts} texts */
    const texts = { ...defaultTexts, ...(params.texts || {}) };
    /** @type {boolean} extended */
    const extended = Boolean(~~params.extended);
    /** @type {string} searchUrl */
    const searchUrl = params.searchUrl || null;
    /** @type {Selectors} selectors */
    const selectors = { ...defaultSelectors, ...(params.selectors || {}) };
    /** @type {CustomElements} elements */
    const elements = { ...defaultElements, ...findElementsFromSelectors(selectors, Object.keys(defaultElements)) };
    if (selectors.country === null || elements.country === null) {
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

    // #region Listeners
    elements.country.on("change", onChangeCountry.bind(null, elements.country, elements));
    if (elements.region) {
        elements.region.on("change", onChangeRegion.bind(null, elements.region, elements, texts));
    }
    if (elements.city) {
        elements.city.on("change", onChangeCity.bind(null, elements.city, elements));
    }

    $(globalThis).on("locations:inline:override-coutry", (e, data) => {
        onCountryOverride(data.country || null, elements.country, elements);
    });
    // #endregion Listeners
};
