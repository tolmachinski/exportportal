module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        text: require('../functions/text'),
        click: require("../functions/click"),
    }

    const data = {
        mail: variables.mail,
    }

    await require("../modules/topicsList")(page);

    await page.waitForFunction(require('../functions/counter'), {}, {
        selectorAll: `[atas="page__topics__search_counter"]`,
        value: 99999,
    });

    if(scenario.subscribe) {
        await functions.click(page, `[atas="global__sidebar__subscribe-btn"]`);
        await page.waitForNetworkIdle();
    }

    if(scenario.sendSubscribe) {
        await page.waitForFunction(subscribe, {}, data);
        await functions.click(page, `[atas="global__sidebar__subscribe-btn"]`);
        await page.waitForNetworkIdle();
    }
};

const subscribe = data => {
    return (async function () {
        document.querySelector('[atas="global__sidebar__subscribe-input"]').value = data.mail;
        document.querySelector('[atas="global__sidebar__subscribe-checkbox"]').checked = true;
        return true;
    })();
};

