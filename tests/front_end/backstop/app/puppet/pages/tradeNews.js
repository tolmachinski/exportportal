module.exports = async (page, scenario) => {
    // Trade news main card info
    await require('../modules/tradeNewsCard')(page);
}
