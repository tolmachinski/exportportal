module.exports = async (page, scenario) => {
    await require("../modules/epUpdates")(page);

    const clientWidth = await page.evaluate(() => {
        return document.documentElement.clientWidth;
    });

    const functions = {
        counter: require('../functions/counter'),
        text: require('../functions/text'),
        click: require('../functions/click')
    }

    if(scenario.keywordExist){
        if (clientWidth < 575) {
            await functions.click(page, `[atas="page__updates__sidebar_search-btn"]`);
            await page.waitForTimeout(500);
        }
        await page.waitForSelector(`[atas="global__sidebar__search-input"]`);
        await page.waitForFunction(initSearch, {}, scenario);
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
    }
}

function initSearch(data) {
    return (async function(){
        const input = document.querySelector(`[atas="global__sidebar__search-input"]`);

        if (input) {
            input.value = data.keyword;
            document.querySelector(`[atas="global__sidebar__form_search-btn"]`).click();
        }

        return true
    })()
}
