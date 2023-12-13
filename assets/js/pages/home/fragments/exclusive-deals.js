import $ from "jquery";

import lazyLoadingInstance from "@src/plugins/lazy/index";
import postRequest from "@src/util/http/post-request";
import { SITE_URL } from "@src/common/constants";

let wasCalled = false;
const exclusiveDealsSection = async () => {
    const sectionsNode = ".js-exclusive-deals";
    const section = $(sectionsNode);
    const loader = section.closest("section").find(".ajax-loader");

    if (!section.hasClass("loading") || wasCalled) {
        return;
    }
    wasCalled = true;

    try {
        const { itemsCompilations } = await postRequest(`${SITE_URL}items/ajax_get_items_compilations`);

        if (itemsCompilations && !Array.isArray(itemsCompilations)) {
            section.append(itemsCompilations);
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

export default exclusiveDealsSection;
