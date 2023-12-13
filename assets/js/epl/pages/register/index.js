import $ from "jquery";
import viewPassword from "@src/epl/common/view-password/index";
import lazyLoadingPswdStrength from "@src/epl/common/pswdStrength/index";

// eslint-disable-next-line import/no-unresolved
import "@scss/epl/pages/register/index.scss";

$(() => {
    $("#js-password").on("click focus", lazyLoadingPswdStrength);

    $(document).on("click", ".js-view-password-btn", e => {
        e.preventDefault();
        viewPassword($(e.currentTarget));
    });
});
