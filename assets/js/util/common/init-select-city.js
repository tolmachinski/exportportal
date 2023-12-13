import $ from "jquery";
import "select2";
import { SUBDOMAIN_URL, LANG } from "@src/common/constants";
import { translate } from "@src/i18n";

const initSelectCity = function ($selectCity, placeholder, regionElement) {
    const placeholderText = placeholder || translate({ plug: "general_i18n", text: "form_placeholder_select2_state_first" });
    const getState = function () {
        if (typeof globalThis.selectState !== "undefined" && globalThis.selectState) {
            return globalThis.selectState;
        }

        if (regionElement instanceof $ || regionElement instanceof HTMLElement) {
            return $(regionElement).val() || null;
        }

        return null;
    };

    const onResultsMessage = function () {
        // eslint-disable-next-line no-underscore-dangle
        this.dropdown._positionDropdown();
    };

    $selectCity
        .select2({
            ajax: {
                type: "POST",
                // eslint-disable-next-line no-underscore-dangle
                url: `${SUBDOMAIN_URL}location/ajax_get_cities`,
                dataType: "json",
                delay: 250,
                data(params) {
                    return {
                        page: params.page,
                        search: params.term,
                        state: getState(),
                    };
                },
                // beforeSend(xhr, opts) {},
                processResults(data, params) {
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page || 1) * data.per_p < data.total_count,
                        },
                    };
                },
            },
            // eslint-disable-next-line no-underscore-dangle
            language: LANG,
            theme: "default ep-select2-h30",
            width: "100%",
            placeholder: placeholderText,
            minimumInputLength: 2,
            dropdownParent: $("#js-select2-city-wr"),
            escapeMarkup(markup) {
                return markup;
            },
            templateResult: repo => {
                return repo.loading ? repo.text : repo.name;
            },
            templateSelection: repo => {
                return repo.name || repo.text;
            },
        })
        .data("select2")
        .on("results:message", onResultsMessage);

    if ($selectCity.find("option").length < 2) {
        $selectCity.prop("disabled", true);
    }
};

export default initSelectCity;
