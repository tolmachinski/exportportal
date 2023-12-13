module.exports = async (page) => {
    const variables = require('../variables/variables');

    require("../modules/changeData")(page, [
        {
            selectorAll: `[atas="page__blog__card_title"]`,
            value: variables.lorem(250)
        },
        {
            selectorAll: `[atas="page__blog__card_date"]`,
            value: variables.dateFormat.withoutTime
        },
        {
            selectorAll: `[atas="page__blog__card_image"]`,
            value: variables.img(800, 348),
            attr: "src",
            type: "image"
        },
        {
            selectorAll: `[atas="page__blog__card_category"]`,
            value: variables.lorem(50)
        },
        {
            selectorAll: `[atas="page__blog__card_short-description"]`,
            value: variables.lorem(250)
        }
    ]);

    await page.waitForTimeout(500);
}
