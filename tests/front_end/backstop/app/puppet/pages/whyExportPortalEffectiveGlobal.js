module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require('../functions/click'),
    };

    // Replace banner
    await page.waitForFunction(require('../functions/picture'), {}, {
        selectorAll: `[atas="global__banner-picture"]`,
        src: variables.img(1170, 200),
        media: {
            tablet: {
                attr: "(max-width:991px)",
                src: variables.img(738, 190),
            },
            mobile: {
                attr: "(max-width:575px)",
                src: variables.img(290, 500),
            }
        }
    });

    // Replace reviews
    await require("../modules/reviewsSlider")(page, scenario);

    // Open menu mobile
    if(scenario.openMenu){
        await functions.click(page, `[atas="global__about-mobile-buttons-menu"]`);
    }

    // Open write review popup
    if (scenario.openWriteReviewPopup) {
        await functions.click(page, `[atas="global__reviews-slider_write-review-btn"]`, true);
        await page.waitForSelector(".modal-dialog", { visible: true });
    }

    // Open Shedule Demo popup
    if (scenario.openScheduleDemoPopup) {
        await require("../modules/scheduleDemoPopup")(
            page,
            scenario,
            scenario.type
        );
    }
}
