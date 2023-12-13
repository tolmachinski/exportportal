import $ from "jquery";
import "select2";

import { translate } from "@src/i18n";
import { LANG, SUBDOMAIN_URL } from "@src/common/constants";

const onResultsMessage = function () {
    // eslint-disable-next-line no-underscore-dangle
    this.dropdown._positionDropdown();
};

const formatSelection = selection => {
    return selection.name || selection.text;
};

const formatResult = result => {
    return result.loading ? result.text : result.name;
};

export default function initializeList(citiesSelector, regionsSelector, parentSelector, placeholder = null) {
    const regionsElement = $(regionsSelector);
    const citiesElements = $(citiesSelector);
    const parentElement = $(parentSelector);
    const placeholderText = placeholder || translate({ plug: "general_i18n", text: "form_placeholder_select2_state_first" });

    citiesElements.select2({
        ajax: {
            type: "POST",
            url: `${SUBDOMAIN_URL}location/ajax_get_cities`,
            dataType: "json",
            delay: 250,
            data(params) {
                return {
                    page: params.page,
                    search: params.term,
                    state: regionsElement.val() || null,
                };
            },
            processResults(data, params) {
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page || 1) * data.per_p < data.total_count,
                    },
                };
            },
        },
        language: LANG,
        theme: "default ep-select2-h30",
        width: "100%",
        minimumInputLength: 2,
        placeholder: placeholderText,
        dropdownParent: parentElement,
        templateResult: formatResult,
        templateSelection: formatSelection,
        escapeMarkup(markup) {
            return markup;
        },
    });

    citiesElements
        .data("select2")
        .$container.attr("id", "country-code--formfield--code-container")
        .addClass("validate[required]")
        .setValHookType("selectCcode");

    $.valHooks.selectCcode = {
        get() {
            return citiesElements.val() || [];
        },
        set(el, val) {
            citiesElements.val(val);
        },
    };

    // @ts-ignore
    citiesElements.data("select2").on("results:message", onResultsMessage);
    if (citiesElements.find("option").length < 2 && !regionsElement.val()) {
        citiesElements.prop("disabled", true);
    }
}
