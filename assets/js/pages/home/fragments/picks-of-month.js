import $ from "jquery";

import lazyLoadingInstance from "@src/plugins/lazy/index";
import postRequest from "@src/util/http/post-request";
import { BACKSTOP_TEST_MODE, SITE_URL } from "@src/common/constants";

let wasCalled = false;
const getPicksOfMonth = async () => {
    const sectionsNode = ".js-picks-of-month";
    const section = $(sectionsNode);
    const loader = section.closest("section").find(".ajax-loader");

    if (!section.hasClass("loading") || wasCalled) {
        return;
    }
    wasCalled = true;

    try {
        let url = `${SITE_URL}default/ajax_get_picks_of_month`;

        if (BACKSTOP_TEST_MODE) {
            url += "?backstop=1";
        }

        const { picksOfMonth } = await postRequest(url);

        if (picksOfMonth && !Array.isArray(picksOfMonth)) {
            section.prepend(picksOfMonth);
        } else {
            section.closest("section").remove();

            return;
        }
    } catch (error) {
        console.error(error);
        section.closest("section").remove();

        return;
    }

    lazyLoadingInstance(`${sectionsNode} .js-lazy`);

    loader.fadeOut(200, () => {
        loader.remove();
        section.removeClass("loading");
    });
};

export default getPicksOfMonth;
