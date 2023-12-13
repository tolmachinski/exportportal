import $ from "jquery";

import { openModalPopup } from "@src/plugins/bootstrap-dialog/index";

const callPreferencesModal = function (button) {
    openModalPopup({
        classes: "bootstrap-dialog--preferences",
        btn: button,
        title: $(button).attr("title"),
        content: $("#popup-preferences-content").html(),
        buttons: [],
        validate: true,
        btnSubmitCallBack: $(button).data("callback"),
    });
};

export default callPreferencesModal;
