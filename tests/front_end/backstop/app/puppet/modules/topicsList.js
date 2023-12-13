module.exports = async page => {
    const variables = require("../variables/variables");

    await require("../modules/changeData")(page, [
        {
            selectorAll: `[atas="page__topics__topic-item_title"]`,
            value: variables.lorem(110),
        },
        {
            selectorAll: `[atas="page__topics__topic-item_text"]`,
            value: variables.lorem(250),
        },
    ]);
};
