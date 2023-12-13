module.exports = async page => {
    await require("../modules/sellerSidebar")(page);
     // Reviews
    await require("../modules/ratingBootstrap")(page);
    await require('../modules/reviews')(page);
}


