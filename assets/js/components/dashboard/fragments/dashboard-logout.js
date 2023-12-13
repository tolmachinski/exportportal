const dashboardLogout = (e, btn) => {
    e.preventDefault();
    if (globalThis.matrixLogoutEmitter) {
        globalThis.dispatchEvent(
            new CustomEvent("matrixLogout", {
                detail: {
                    callback: () => {
                        globalThis.location.href = btn.attr("href");
                    },
                },
            })
        );
    } else {
        globalThis.location.href = btn.attr("href");
    }
};

export default dashboardLogout;
