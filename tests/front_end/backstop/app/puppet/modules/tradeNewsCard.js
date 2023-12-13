module.exports = async (page) => {
    const variables = require('../variables/variables');
    // Pictures
    await page.waitForFunction(require('../functions/picture'), {}, {
        selectorAll: `[atas="trade-news__picture"]`,
        src: variables.img(855, 225),
        media: {
            mobile: {
                attr: "(max-width: 574px)",
                src: variables.img(580, 153)
            }
        }
    })
    // Text info
    require('../modules/changeData')(page, [
        {
            selectorAll: `[atas="trade-news__title"]`,
            value: variables.lorem(75),
        },
        {
            selectorAll: `[atas="trade-news__text"]`,
            value: variables.lorem(200),
        },
        {
            selectorAll: `[atas="trade-news__date"]`,
            value: variables.dateFormat.withoutTime,
        }
    ])
}
