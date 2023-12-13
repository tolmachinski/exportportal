module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
    };

    const data = {
        name: variables.name.xLong,
        title: variables.lorem(150),
        userGroup: variables.userGroups.certified.text.seller,
        img: variables.img(100, 100),
        country: variables.country.name,
        flag: variables.country.flag,
        address: "str. Burebista, 3",
        mail: variables.mail,
        phone: variables.phone,
        date: variables.dateFormat.withTime,
        filterPanel: {
            partnershipFrom: "01/01/2020",
            partnershipTo: "01/31/2030",
            country: 139,
            search: "backstop",
        },
    };

    if (scenario.filterPopup) {
        await functions.click(page, `[atas="global__dashboard_filter-btn"]`);
        await page.waitForNetworkIdle();

        if (scenario.fillFilterPopup) {
            await page.waitForFunction(fillFilterForm, {}, data);
            await page.waitForNetworkIdle();

            if (scenario.activeFilter) {
                await functions.click(page, `[atas="global__dashboard_filter-panel_active-filters-btn"]`);
            }
        }
    }

    if (scenario.dropdown) {
        await functions.click(page, `[atas="b2b-my-partners__table_dropdown-btn"]`);

        if (scenario.deletePartner) {
            await functions.click(page, `[atas="b2b-my-partners__table_dropdown_delete-partner-btn"]`);
            await page.waitForNetworkIdle();
        }
    }

    await page.waitForNetworkIdle();
    await require("../modules/changeData")(page, [
        // Partner name
        {
            selectorAll: `[atas="b2b-my-partners__table_partner-name"]`,
            value: data.name,
        },
        // Partner Avatar
        {
            selectorAll: `[atas="b2b-my-partners__table_partner-image"]`,
            value: data.img,
            attr: "src",
            type: "image",
        },
        // Partner group
        {
            selectorAll: `[atas="b2b-my-partners__table_partner-group"]`,
            value: data.userGroup,
        },
        // Partner of
        {
            selectorAll: `[atas="b2b-my-partners__table_partner-company"]`,
            value: data.name,
        },
        // Partner Address Flag
        {
            selectorAll: `[atas="b2b-my-partners__table_partner-flag-image"]`,
            value: data.flag,
            attr: "src",
            type: "image",
        },
        // Partner Country
        {
            selectorAll: `[atas="b2b-my-partners__table_partner-country"]`,
            value: data.country,
        },
        // Partner Address
        {
            selectorAll: `[atas="b2b-my-partners__table_partner-address"]`,
            value: data.address,
        },
        // Partner Mail
        {
            selectorAll: `[atas="b2b-my-partners__table_partner-email"]`,
            value: data.mail,
        },
        // Partner Phone
        {
            selectorAll: `[atas="b2b-my-partners__table_partner-phone"]`,
            value: data.phone,
        },
    ]);

    await require("../modules/dashboardShowingEntries")(page);
    await page.waitForFunction(changeUserGroup);
    await page.waitForFunction(changePartnerShipDate, {}, data);
};

const fillFilterForm = data => {
    return (async function () {
        const textNodes = {
            partnershipFrom: document.querySelector(`[atas="b2b-my-partners__filter-panel_partnership-from-input"]`),
            partnershipTo: document.querySelector(`[atas="b2b-my-partners__filter-panel_partnership-to-input"]`),
            country: document.querySelector(`[atas="b2b-my-partners__filter-panel_country-select"]`),
            search: document.querySelector(`[atas="b2b-my-partners__filter-panel_search-input"]`),
        };

        Object.keys(textNodes).forEach(keyNodes => {
            textNodes[keyNodes].value = data.filterPanel[keyNodes];
            textNodes[keyNodes].dispatchEvent(new Event("change", { bubbles: true }));
        });

        return true;
    })();
};

const changeUserGroup = () => {
    return (async function () {
        document.querySelectorAll(`[atas="b2b-my-partners__table_partner-group"]`).forEach(item => (item.className = "companies__group txt-orange"));

        return true;
    })();
};

const changePartnerShipDate = data => {
    return (async function () {
        const table = document.querySelector(`[atas="page__b2b-my-partners_table"]`);
        for (selector of table.querySelectorAll(`td.sorting_1`)) {
            selector.textContent = data.date
        };

        return true;
    })();
};
