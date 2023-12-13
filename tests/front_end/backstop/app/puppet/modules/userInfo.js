module.exports = async page => {
    const variables = require("../variables/variables");

    await require("../modules/changeData")(page, [
        // User name
        {
            selectorAll: `[atas="global__user-info_name"]`,
            value: variables.name.long,
        },
        // User group
        {
            selectorAll: `[atas="global__user-info_group"]`,
            value: variables.userGroups.certified.text.distributor,
        },
        // User company
        {
            selectorAll: `[atas="global__user-info_company"]`,
            value: variables.companyName,
        },
        // User avatar
        {
            selectorAll: `[atas="global__user-info_avatar"]`,
            value: variables.img(100, 100),
            attr: "src",
            type: "image",
        },
    ]);
};
