module.exports = async (page, scenario) => {
    const functions = {
        click: require("../functions/click"),
        clickAll: require("../functions/clickAll"),
    };

    // Change data for Questions
    await require("../modules/question")(page);

    // Open search form
    if (scenario.showSearchForm) {
        await page.waitForTimeout(500);
        await functions.click(page, `[atas="page__community__header_search-question-btn"]`);
        await page.waitForNetworkIdle();
        await page.waitForSelector(`[atas="page__community__search-form_submit-btn"]`);
    }

    if(scenario.viewMore) {
        await functions.clickAll(page, `[atas="global__sidebar__view-more-btn"]`);
    }

    // Change text for found counter
    if (scenario.search) {
        await page.waitForFunction(require('../functions/counter'), {}, {
            selectorAll: `[atas="global__question-counter"]`,
            value: ` 99999`,
        });
    }
};
