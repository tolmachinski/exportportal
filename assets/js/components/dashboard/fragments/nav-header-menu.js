import $ from "jquery";

const navHeaderMenu = function (menu) {
    $.each(menu, (_i, item) => {
        const icon = $(`<i class="ep-icon ep-icon_${item.icon}">`);
        const title = $(`<div>${item.title}${item.new ? '<span class="dashboard-nav__item-new">NEW</span>' : ""}</div>`);
        const link = $(`<a class="link" data-name="${item.name}" data-tab="${item.tab}">`).append([icon, title]);
        const dashboardLink = $(`<a class="link ${item.add_class ?? ""}" data-name="${item.name}" data-tab="${item.tab}"></a>`).append([
            icon.clone(),
            title.clone().addClass("txt-b"),
        ]);

        if (item.popup) {
            let linksData = { "data-fancybox-href": item.link, "data-title": item.popup };
            if (item.popup_width) {
                linksData = { ...linksData, "data-mw": item.popup_width };
            }
            link.addClass("fancybox.ajax fancyboxValidateModal").attr(linksData);
            dashboardLink.addClass("fancybox.ajax fancyboxValidateModal").attr(linksData);
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

        const mepNavs = $("#js-mep-user-nav-quick .mep-user-nav__links");
        const desktopNavs = $("#js-dashboard-nav .dashboard-nav");

        if (mepNavs.length) {
            mepNavs.find(`.col${item.col}-cell${item.cell}`).empty().append(link);
        }

        if (desktopNavs.length) {
            desktopNavs.find(`.col${item.col}-cell${item.cell}`).empty().append(dashboardLink);
        }
    });
};

export default navHeaderMenu;
