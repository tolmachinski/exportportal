var exit_explore_user = function () {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: __current_sub_domain_url + 'login/exit_explore_user',
        beforeSend: function () {},
        success: function (resp) {
            if (resp.mess_type == 'success') {
                if (globalThis.matrixLogoutEmitter) {
                    globalThis.dispatchEvent(
                        new CustomEvent("matrixLogout", {
                            detail: {
                                callback() {
                                    globalThis.location.href = resp.redirect;
                                },
                            },
                        })
                    );
                } else {
                    globalThis.location.href = resp.redirect;
                }
            } else {
                systemMessages(resp.message, resp.mess_type);
            }
        }
    });
    return false;
}
