import $ from "jquery";
import { SITE_URL } from "@src/common/constants";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

const getCompanyBranch = function () {
    $("#js-b2b-request-company-branch-select").on("change", async function onChange() {
        const cb = $(this).val();

        try {
            const { mess_type: messageType, plug } = await postRequest(`${SITE_URL}b2b/ajax_category_operation/get_industries_by_item_new`, {
                // eslint-disable-next-line camelcase
                company_branch: cb,
            });

            if (messageType === "success") {
                $("#js-b2b-dynamic-industries").html(plug);
            }
        } catch (error) {
            handleRequestError(error);
        }
    });
};

export default getCompanyBranch;
