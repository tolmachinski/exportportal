module.exports = async (page, scenario) => {
    const variables = require("../variables/variables")

    await require("../modules/faqList")(page);

    // Replace banner sidebar
    await page.waitForFunction(require('../functions/picture'), {}, {
        selectorAll: `[atas="faq__banner-demo"] [atas="global__banner-picture"]`,
        src: variables.img(300, 500),
        media: {
            tablet: {
                attr: "(max-width:991px)",
                src: variables.img(250, 500),
            },
        }
    });

    // Replace banner sidebar tablet/mobile
    await page.waitForFunction(require('../functions/picture'), {}, {
        selectorAll: `[atas="faq__banner-demo-bottom"] [atas="global__banner-picture"]`,
        src: variables.img(738, 190),
        media: {
            mobile: {
                attr: "(max-width:575px)",
                src: variables.img(290, 500),
            }
        }
    });

    // Open Shedule Demo popup
    if (scenario.openScheduleDemoPopup) {
        await page.waitForSelector(`[atas="faq__banner-demo-bottom"] [atas="global__banner-picture"] img`)
        await require("../modules/scheduleDemoPopup")(
            page,
            scenario,
            scenario.type,
        );
    }
};
