import $ from "jquery";
import { GROUP_SITE_URL, SITE_URL } from "@src/common/constants";

import { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { translate } from "@src/i18n";

import "@scss/components/age-verification/index.scss";

/**
 * @param {Object|HTMLElement} object Can be html btn or object
 * @returns {Promise<void>}
 */
export const onOpenAgeVerificationModal = async object => {
    const redirect = object?.detail?.redirect ?? object[0]?.dataset?.redirect ?? "";
    const redirectClose = object?.detail?.redirectClose ?? object[0]?.dataset?.redirectClose ?? false;

    return openResultModal({
        title: "Age verification",
        subTitle: "Export Portal requires you to be 18 years or older to view this category. Please enter your date of birth to continue.",
        content: `${GROUP_SITE_URL}categories/age_verification/check_age`,
        type: "warning",
        isAjax: true,
        validate: true,
        closable: true,
        closeCallBack() {
            if (redirectClose === true) {
                window.location.href = SITE_URL;
            } else {
                $(".ep-header").css("filter", "unset");
                $(".ep-content").css("filter", "unset");
            }
        },
        openCallBack() {
            $(".ep-header").css("filter", "blur(10px)");
            $(".ep-content").css("filter", "blur(10px)");
        },
        buttons: [
            {
                label: translate({ plug: "general_i18n", text: "form_button_submit_text" }),
                cssClass: "btn btn-primary js-submit-form",
                action() {
                    if (redirect) {
                        $(this).data("redirect", redirect);
                    }
                    $("body").find("#js-age-verification").trigger("submit");
                },
            },
        ],
    });
};

/**
 *
 * @param detail Can be jQuery btn (if used on click to link) or object (if used on open page where is required age)
 * @returns {Promise<void>}
 */
export default async detail => {
    await onOpenAgeVerificationModal({ detail });
};
