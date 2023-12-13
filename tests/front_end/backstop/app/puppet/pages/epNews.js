module.exports = async (page, scenario) => {
    await require("../modules/newsItem")(page, scenario);
}
