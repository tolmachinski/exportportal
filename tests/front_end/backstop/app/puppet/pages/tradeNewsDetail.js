module.exports = async (page, scenario) => {
    const variables = require('../variables/variables');
    // Trade news main info
    await require('../modules/tradeNewsCard')(page);
    // Trade news description
    await require('../modules/changeData')(page, [
        {
            selectorAll: `[atas="trade-news__description"] img`,
            value: variables.img(1200, 675),
            attr: "src",
            type: "image"
        },
        {
            selectorAll: `[atas="trade-news__description"] p`,
            value: variables.lorem(1000),
            isTinymce: true
        }
    ])
    // Comments
    await require('../modules/commentsCommon')(page, 1000);
}
