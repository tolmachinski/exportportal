module.exports = async page => {
    const functions = {
        click: await require("../functions/click"),
    };
    const variables = require("../variables/variables");

    await require("../modules/changeData")(page, [
        {
            selectorAll: `[atas="page__faq__list-title"]`,
            value: variables.lorem(110),
        },
        {
            selectorAll: `[atas="page__faq__list-tag"]`,
            value: variables.lorem(25),
        },
        {
            selectorAll: `[atas="page__faq__other-tage_name"]`,
            value: `${variables.name.short}, ${variables.name.medium}, ${variables.name.long}`,
        },
        {
            selectorAll: `[atas="page__faq__other-tage_count"]`,
            value: 99,
        },
        {
            selectorAll: `[atas="page__faq__list-text"]`,
            value: variables.lorem(250),
        },
    ]);

    await page.waitForFunction(showMore);
};

function showMore() {
    return (async () => {
        const delay = ms => new Promise(res => setTimeout(res, ms));
        const showMoreBtn = document.querySelector(`[atas="page__faq__list_more-btn"]`);

        if (showMoreBtn) {
            showMoreBtn.click();
            await delay(500);
        }

        return true;
    })();
}
