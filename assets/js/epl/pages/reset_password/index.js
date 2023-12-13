import $ from "jquery";
import lazyLoadingPswdStrength from "@src/epl/common/pswdStrength/index";
import viewPassword from "@src/epl/common/view-password/index";

// @ts-ignore
import "@scss/epl/pages/reset_password/index.scss";

$(() => {
    $("#js-password").on("click focus", lazyLoadingPswdStrength);
    $(document).on("keyup keypress", ".js-view-password-btn", e => {
        if (e.which === 13) {
            e.preventDefault();
        }
    });

    $(document).on("click", ".js-view-password-btn", e => {
        e.preventDefault();
        viewPassword($(e.currentTarget));
    });
});
