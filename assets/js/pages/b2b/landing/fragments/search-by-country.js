import $ from "jquery";

import lazyLoadingInstance from "@src/plugins/lazy/index";
import getRequest from "@src/util/http/get-request";
import { SITE_URL } from "@src/common/constants";
import handleRequestError from "@src/util/http/handle-request-error";

let wasCalled = false;

const fillCountryWithColor = countries => {
    countries.forEach(item => {
        const country = document.getElementById(`${item.dataset.countryName.replace(/(, |'| |,)/g, "_")}`);
        if (!country) return;
        country.style.fill = "#2181F8";
    });
};
const getSearchByCountry = async () => {
    const sectionsNode = ".js-search-by-country";
    const section = $(sectionsNode);
    const loader = section.closest("section").find(".ajax-loader");
    if (!section.hasClass("loading") || wasCalled) {
        return;
    }
    wasCalled = true;

    try {
        const countryMap = await getRequest(`${SITE_URL}b2b/ajax_get_map`, "html");

        if (countryMap) {
            section.find(".js-country-map").append(countryMap);
            fillCountryWithColor(document.querySelectorAll(".js-country-name"));
        } else {
            section.closest("section").remove();
            return;
        }
    } catch (error) {
        handleRequestError(error);
        section.closest("section").remove();

        return;
    }

    lazyLoadingInstance(`${sectionsNode} .js-lazy`);

    loader.fadeOut(200, () => {
        loader.remove();
        section.removeClass("loading");
    });
};

export default getSearchByCountry;
