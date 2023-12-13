import { translate } from "@src/i18n";
import makeCitiesList from "@src/components/location/cities-list";
import getElement from "@src/util/dom/get-element";
import postRequest from "@src/util/http/post-request";
import { SUBDOMAIN_URL } from "@src/common/constants";

/**
 * Get the list of states from the server.
 *
 * @param {number|string|string[]} country
 * @param {string} [placeholder]
 *
 * @returns {Promise<string>}
 */
async function getStates(country, placeholder = null) {
    if (!country) {
        return "";
    }

    try {
        const { states = "" } = await postRequest(`${SUBDOMAIN_URL}location/ajax_get_states`, { country, placeholder });

        return states;
    } catch (error) {
        return "";
    }
}

/**
 * Handle change country event.
 *
 * @param {JQuery} countries
 * @param {JQuery} states
 * @param {JQuery} cities
 */
async function onChangeCountry(countries, states, cities) {
    const country = countries.val() || null;
    const list = await getStates(country);
    if (list) {
        states.html(list);
    } else {
        states.children().first().siblings().remove();
    }
    cities.empty().trigger("change").prop("disabled", true);
}

/**
 * Handle change state event.
 *
 * @param {JQuery} select
 * @param {JQuery} cities
 */
function onChangeState(select, cities) {
    const state = select.val() || null;
    let text = translate({ plug: "general_i18n", text: "form_placeholder_select2_state_first" });
    if (!state) {
        text = translate({ plug: "general_i18n", text: "form_placeholder_select2_city" });
    }

    cities.empty().prop("disabled", false);
    cities.siblings(".select2").find(".select2-selection__placeholder").text(text);
}

/**
 * @param {JQuery} wrapper
 */

export default function initializeBlock(wrapper) {
    const countries = getElement(wrapper.data("countries"));
    const states = getElement(wrapper.data("states"));
    const cities = getElement(wrapper.data("cities"));

    makeCitiesList(cities, states, wrapper);

    countries.on("change", () => onChangeCountry(countries, states, cities));
    states.on("change", () => onChangeState(states, cities));

    return { countries, states, cities };
}
