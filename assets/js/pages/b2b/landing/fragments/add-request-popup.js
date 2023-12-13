import $ from "jquery";
import loadBootstrapDialog, { openHeaderImageModal } from "@src/plugins/bootstrap-dialog/index";
import { translate } from "@src/i18n";
import handleRequestError from "@src/util/http/handle-request-error";
import { SITE_URL } from "@src/common/constants";

const openAddRequestPopup = async btn => {
    const data = btn.data();
    try {
        await loadBootstrapDialog();
        await openHeaderImageModal({
            classes: "bootstrap-dialog--results-image-with-button",
            titleImage: data.image,
            title: data.title,
            subTitle: data.subTitle,
            titleUppercase: true,
            isAjax: false,
            buttons: [
                {
                    label: () => translate({ plug: "general_i18n", text: "js_b2b_popup_sign_in_button" }),
                    cssClass: "btn btn-outline-primary",
                    action(dialog) {
                        dialog.close();
                        $(".js-sign-in").trigger("click");
                    },
                },
                {
                    label: () => translate({ plug: "general_i18n", text: "js_b2b_popup_register_button" }),
                    cssClass: "btn btn-primary",
                    action() {
                        window.location.href = `${SITE_URL}register`;
                    },
                },
            ],
        });
    } catch (e) {
        handleRequestError(e);
    }
};

export default openAddRequestPopup;
