import { SUBDOMAIN_URL } from "@src/common/constants";
import postRequest from "@src/util/http/post-request";

const sentPopupViewed = (popup, viewedType = "") => {
    const params = { popup };

    if (viewedType) {
        // eslint-disable-next-line camelcase
        Object.assign(params, { viewed_type: viewedType });
    }
    postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/viewed`, params);
};

export default sentPopupViewed;
