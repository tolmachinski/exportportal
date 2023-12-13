const resposeCalendar = calendar => {
    if (globalThis.matchMedia("(min-width: 991px)").matches) {
        calendar.setTheme({
            month: {
                gridCell: {
                    headerHeight: 34,
                },
            },
        });
    }

    if (globalThis.matchMedia("(max-width: 991px)").matches && globalThis.matchMedia("(min-width: 769px)").matches) {
        calendar.setTheme({
            month: {
                gridCell: {
                    headerHeight: 21,
                },
            },
        });
    }

    if (globalThis.matchMedia("(max-width: 768px)").matches) {
        calendar.setTheme({
            month: {
                gridCell: {
                    headerHeight: 10,
                    footerHeight: 10,
                },
            },
        });
    }
};

export default resposeCalendar;
