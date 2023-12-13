module.exports = async (page, scenario) => {
    const recursiveCallOnCatch = require("../functions/recursive-try-catcher");

    await recursiveCallOnCatch(
        async () => {
            await page.waitForSelector(`[atas="page__handmade__latest-items-slider"].slick-initialized`, { visible: true });
        },
        async () => {
            await page.waitForSelector(`[atas="page__handmade__most-popular-slider"]`);
        },
        120000
    );


    await require('../modules/productCard')(page);
};
