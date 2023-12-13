module.exports = async page => {
    const variables = require("../variables/variables");

    // Text info
    await require("../modules/changeData")(page, [
        // Question Title
        {
            selectorAll: `[atas="global__question-title"]`,
            value: variables.lorem(255),
        },
        // Question Text
        {
            selectorAll: `[atas="global__question-text"]`,
            value: variables.lorem(500),
        },
        // Question Text
        {
            selectorAll: `[atas="global__question-date"]`,
            value: variables.dateFormat.withoutTime,
        },
        // Question User Name
        {
            selectorAll: `[atas="global__question-name"]`,
            value: variables.name.medium,
        },
        // Question User Avatar
        {
            selectorAll: `[atas="global__question-image"]`,
            value: variables.img(80, 80),
            attr: "src",
            type: "image",
        },
        // Question Counters
        {
            selectorAll: `[atas="global__question-counter"]`,
            value: 9999,
        },
        // Question Replies
        {
            selectorAll: `[atas="global__question-replies"]`,
            value: "9999 replies",
        },
        // Question Category
        {
            selectorAll: `[atas="global__question-type"]`,
            value: "Export/import taxes with backstop testing",
        },
        // Question Country Name
        {
            selectorAll: `[atas="global__question-country-name"]`,
            value: variables.country.name,
        },
        // Question Country Flag
        {
            selectorAll: `[atas="global__question-country-flag"]`,
            value: variables.country.flag,
            attr: "src",
            type: "image",
        },
    ]);
};
