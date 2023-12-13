module.exports = async (page, scenario) => {
    await require("../modules/sellerSidebar")(page);
    // Change data for Questions
    await require("../modules/question")(page);
};
