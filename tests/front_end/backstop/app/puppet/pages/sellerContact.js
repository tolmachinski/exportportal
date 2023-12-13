module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const data = {
        headquarters: {
            address: variables.lorem(50),
        },
        contacts: {
            title: variables.lorem(66),
            description: variables.lorem(500),
        },
        branches: {
            title: variables.lorem(66),
            address: variables.lorem(50),
        },
    }
    await require("../modules/sellerSidebar")(page);


    if (scenario.navTab) {
        await page.waitForFunction(openNavTab, {}, scenario.navTab);
        await page.waitForTimeout(500);

        switch(scenario.navTab) {
            case "headquarters":
                await page.waitForFunction(replaceHeadquarters, {}, data.headquarters)
                break;
            case "contacts":
                await page.waitForFunction(replaceContacts, {}, data.contacts)
                break;
            case "branches":
                await page.waitForFunction(replaceBranches, {}, data.branches)
                break;
            default: break;
        }
    }

    await page.waitForFunction(openAllNavButtons);
    await require('../modules/replaceUserStatus')(page);
}

function openAllNavButtons() {
    return (async () => {
        document.querySelectorAll(`[atas="seller__sidebar_nav_btn_menu"]`).forEach(btn => btn.click());
        document.querySelectorAll(`[atas="seller__sidebar_nav_items-counter"]`).forEach(e => e.textContent = 999);
        document.querySelector(`[atas="seller__sidebar_more_actions_menu"]`).click();

        return true;
    })();
}

function openNavTab (type) {
    return (async () => {
        document.querySelector(`[atas="seller-contact__nav-${type}"]`).click();

        return true;
    })()
}

function replaceHeadquarters(data) {
    return (async () => {
        document.querySelectorAll(`[atas="seller-contact__headquarters_address"]`).forEach(address => {
            address.textContent = data.address;
        });

        return true;
    })()
}

function replaceContacts(data) {
    return (async () => {
        const textElements = {
            title: document.querySelectorAll(`[atas="seller-contact__contacts_title"]`),
            description: document.querySelectorAll(`[atas="seller-contact__contacts_description"]`),
        };

        Object.keys(textElements).forEach(key => {
            textElements[key].forEach(e => {
                e.textContent = data[key];
            });
        })

        return true;
    })()
}

function replaceBranches(data) {
    return (async () => {
        const textElements = {
            title: document.querySelectorAll(`[atas="seller-contact__branches_title"]`),
            address: document.querySelectorAll(`[atas="seller-contact__branches_address"]`),
        };

        Object.keys(textElements).forEach(key => {
            textElements[key].forEach(e => {
                e.textContent = data[key];
            });
        })

        return true;
    })()
}
