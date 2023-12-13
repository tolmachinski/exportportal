module.exports = async (page, scenario) => {
    await require("../modules/sellerSidebar")(page);
     // Reviews
     await require("../modules/ratingBootstrap")(page);
     await require('../modules/reviews')(page);
}


