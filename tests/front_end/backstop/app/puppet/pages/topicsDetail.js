module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        text: require('../functions/text'),
        click: require("../functions/click"),
    }

    const data = {
        title: variables.name.xLong,
        text: variables.lorem(8000),
    }

    await page.waitForFunction(functions.text, {}, {
        selectorAll: `[atas="page__topics-detail__popular-topics_link"]`,
        text: data.title,
    });

    await page.waitForFunction(functions.text, {}, {
        selectorAll: `[atas="page__topics-detail__section_header"]`,
        text: data.title,
    });

    await page.waitForFunction(functions.text, {}, {
        selectorAll: `[atas="page__topics-detail__section_text"]`,
        text: data.text,
    });
};

