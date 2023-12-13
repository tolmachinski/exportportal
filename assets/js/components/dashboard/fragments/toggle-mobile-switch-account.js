import $ from "jquery";

const toggleMobileSwitchAccount = button => {
    button.toggleClass("txt-blue2");
    $(".js-mep-header-user-menu-wr, .js-mep-header-switch-account-wr").toggle();
};

export default toggleMobileSwitchAccount;
