module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
        counter: require("../functions/counter"),
    };

    const data = {
        name: variables.name.xLong,
        companyName: variables.companyName,
        userImage: variables.img(280,280),
        img: variables.img(76, 76),
        certifiedLogo: variables.img(90, 96),
        date: variables.dateFormat.withoutTime,
        userGroup: variables.userGroups.certified.text.seller,
        follower: `follower ${variables.dateFormat.withoutTime}`,
        countryName: variables.country.name,
        countryFlag: variables.country.flag,
    };

    await require("../modules/changeData")(page, [
        // User Name
        {
            selectorAll: `[atas="page__user-info__name"]`,
            value: data.name,
        },
        // User Company Name
        {
            selectorAll: `[atas="page__user-info__company-name"]`,
            value: data.companyName,
        },
        // User Image
        {
            selectorAll: `[atas="page__user-info__image"]`,
            value: data.userImage,
            attr: "src",
            type: "image",
        },
        // User Certified Logo
        {
            selectorAll: `[atas="page__user-info__certified-image"]`,
            value: data.certifiedLogo,
            attr: "src",
            type: "image",
        },
        // User Registration date
        {
            selectorAll: `[atas="page__user-info__registration-date"]`,
            value: data.date,
        },
        // User Last Activity
        {
            selectorAll: `[atas="page__user-info__last-activity"]`,
            value: data.date,
        },
    ]);
    await require("./../modules/followersItems")(page, scenario);
    await require("../modules/additionalInfo")(page);
    await page.waitForFunction(functions.counter, {}, { selectorAll: `[atas="page__user-info__counter"]`, value: 99999 });
    await require("../modules/ratingBootstrap")(page);
    await require('../modules/reviews')(page);

    if(scenario.written) {
        await functions.click(page, `[atas="page__user-info__feedback_written"]`);
    }

    if(scenario.questions) {
        await functions.click(page, `[atas="page__user-info__on-item_questions"]`);
    }
}
