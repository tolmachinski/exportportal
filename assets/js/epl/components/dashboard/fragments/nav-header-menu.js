import $ from "jquery";

const navHeaderMenu = menu => {
    menu.forEach(item => {
        const icon = $(`<i class="ep-icon ep-icon_${item.icon}">`);
        const title = $(`<span>${item.title}</span>`);
        const link = $(`<a class="link" data-name="${item.name}" data-tab="${item.tab}">`).append([icon, title]);
        const dashboardLink = $(`<a class="link ${item.add_class ?? ""}" data-name="${item.name}" data-tab="${item.tab}"></a>`).append([
            icon.clone(),
            title.clone().addClass("txt-b"),
        ]);

        if (item.popup) {
            let linksData = { "data-src": item.link, "data-title": item.popup };
            if (item.popup_width) {
                linksData = { ...linksData, "data-mw": +item.popup_width + 60, "data-type": "ajax" };
            }
            link.addClass("js-fancybox").attr(linksData);
            dashboardLink.addClass("js-fancybox").attr(linksData);
        } else {
            link.attr({ href: item.link });
            dashboardLink.attr({ href: item.link });
        }

        if (item.target) {
            link.attr({ target: item.target });
            dashboardLink.attr({ target: item.target });
        }

        if (item.external_link) {
            link.attr({ href: item.external_link });
            dashboardLink.attr({ href: item.external_link });
        }

        const desktopNavs = $("#js-dashboard-nav .dashboard-nav-list");

        if (desktopNavs.length) {
            desktopNavs.find(`.col${item.col}-cell${item.cell}`).empty().append(dashboardLink);
        }
    });
};

export default (metadata = {}) => {
    navHeaderMenu(metadata);
};
