module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const data = {
        userGroupClass: variables.userGroups.certified.class,
    };
    // Products
    await require('../modules/productCard')(page);
    // Data
    await require("../modules/b2b")(page);
    await require("../modules/changeData")(page, [
        // Description test
        {
            selectorAll: `[atas="page__b2b__request-description"] p`,
            value: variables.lorem(3000),
        },
        // Partner's type
        {
            selectorAll: `[atas="page__b2b__partners-type"]`,
            value: variables.name.short,
        },
        // Bussiness Industry
        {
            selectorAll: `[atas="page__b2b__bussiness-industry"]`,
            value: `${variables.name.short}, ${variables.name.medium}, ${variables.name.long}`,
        },
        // Bussiness Category
        {
            selectorAll: `[atas="page__b2b__bussiness-category"]`,
            value: `${variables.name.short}, ${variables.name.medium}, ${variables.name.long}`,
        },
        // Additional images
        {
            selectorAll: `[atas="page__b2b__additional-pictures_img"]`,
            value: variables.img(133, 100),
            attr: "src",
            type: "img",
        },
        // Tips and Advice
        {
            selectorAll: `[atas="page__b2b__advices_text"]`,
            value: variables.lorem(1000),
        },
        // Dates
        {
            selectorAll: `[atas="page__b2b__partners_date-partnership"], [atas="page__b2b__advices_date"], [atas="page__b2b__followers_date-follow"]`,
            value: variables.dateFormat.withTime,
        },
        // Names
        {
            selectorAll: `[atas="page__b2b__partners_name"], [atas="page__b2b__advices_user-name"], [atas="page__b2b__followers_user-name"]`,
            value: variables.name.xLong,
        },
        // Follower account type
        {
            selectorAll: `[atas="page__b2b__followers_user-group"]`,
            value: variables.userGroups.certified.text.manufacturer,
        },
    ]);
    await page.waitForFunction(changeUserGroupColor, {}, data);
    await page.waitForFunction(
        require("../functions/picture"),
        {},
        {
            selectorAll: `[atas="page__b2b__followers_user-image"], [atas="page__b2b__partners_image"], [atas="page__b2b__advices_user-img"]`,
            src: variables.img(90, 90),
        }
    );
};

const changeUserGroupColor = data => {
    return (async function () {
        document.querySelectorAll(`[atas="page__b2b__followers_user-group"]`).forEach(item => item.classList.add(data.userGroupClass));

        return true;
    })();
};
