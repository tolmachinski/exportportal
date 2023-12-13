$(function() {
    globalThis.history.pushState({}, document.title, globalThis.location.origin + globalThis.location.pathname);

    open_result_modal({
        title: "Session Timeout",
        content: "You have been logged out due to inactivity. Please sign in again to continue using Export Portal.",
        isAjax: false,
        closable: true,
        type: "info",
        buttons: [
            {
                label: translate_js({ plug: "BootstrapDialog", text: "close" }),
                cssClass: "btn btn-light",
                action(dialog) {
                    dialog.close();
                },
            },
            {
                label: "Sign in",
                cssClass: "btn btn-primary",
                action() {
                    window.location.href = __shipper_page ? `${__shipper_url}login` : `${__site_url}login`;
                },
            },
        ],
    });
});
