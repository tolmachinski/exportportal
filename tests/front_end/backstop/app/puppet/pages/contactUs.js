module.exports = async (page, scenario) => {
    const variables = require("../variables/variables")

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

    // Open Shedule Demo popup
    if (scenario.openScheduleDemoPopup) {
        await require("../modules/scheduleDemoPopup")(
            page,
            scenario,
            scenario.type
        );
    }
};
