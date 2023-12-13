module.exports = async (page, scenario) => {
    await require("../modules/sellerSidebar")(page);
    await require("../modules/followers")(page, scenario);
};
