module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
        text: require("../functions/text"),
    };
    await require("../modules/massMedia")(page, scenario);

    if (scenario.news) {
        await functions.click(page, `[atas="page__news-and-media__tab_news-btn"]`);
        await require("../modules/newsItem")(page, scenario);
    }

    if(scenario.updates) {
        await functions.click(page, `[atas="page__news-and-media__tab_updates-btn"]`);
        await require("../modules/epUpdates")(page);
    }

    if(scenario.newsletterArchive) {
        await functions.click(page, `[atas="page__news-and-media__tab_newsletter-archive-btn"]`);
        await require("../modules/newsItem")(page, scenario);
    }

}
