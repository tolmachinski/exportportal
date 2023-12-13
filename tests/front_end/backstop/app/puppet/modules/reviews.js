module.exports = async page => {
    const variables = require("../variables/variables");

    // Text info
    await require("../modules/changeData")(page, [
        // Reviews Title
        {
            selectorAll: `[atas="global__reviews-title"]`,
            value: variables.lorem(255),
        },
        // Reviews Text
        {
            selectorAll: `[atas="global__reviews-text"]`,
            value: variables.lorem(500),
        },
        // Reviews Date
        {
            selectorAll: `[atas="global__reviews-date"]`,
            value: variables.dateFormat.withoutTime,
        },
        // Reviews User Name
        {
            selectorAll: `[atas="global__reviews-name"]`,
            value: variables.name.medium,
        },
        // Reviews User Avatar
        {
            selectorAll: `[atas="global__reviews-image"]`,
            value: variables.img(80, 80),
            attr: "src",
            type: "image",
        },
        // Reviews Counters
        {
            selectorAll: `[atas="global__reviews-counter"]`,
            value: 9999,
        },
        // Reviews Country Name
        {
            selectorAll: `[atas="global__reviews-country-name"]`,
            value: variables.country.name,
        },
        // Reviews Country Flag
        {
            selectorAll: `[atas="global__reviews-country-flag"]`,
            value: variables.country.flag,
            attr: "src",
            type: "image",
        },
        // Reviews User Pictures
        {
            selectorAll: `[atas="global__reviews__image-reviews"]`,
            value: variables.img(135, 108),
            attr: "src",
            type: "image",
        },
        // Reviews Item Name
        {
            selectorAll: `[atas="global__reviews__item-name"]`,
            value: variables.lorem(100),
        },
    ]);
};
