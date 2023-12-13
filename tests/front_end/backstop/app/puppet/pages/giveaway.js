module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: await require("../functions/click"),
    };

    // Open video popup
    if (scenario.openVideo) {
        await functions.click(page, `[atas="landing__giveaway-video-block"]`);
        await require("../modules/onLoadIframe")(page, `.bootstrap-dialog .js-popup-video-iframe`);
    }

    // Open contact us popup
    if (scenario.openContactUsPopup) {
        await functions.click(page, `[atas="landing__giveaway-contact-us-btn"]`);
        await page.waitForNetworkIdle();
        await page.waitForSelector(`[atas="global__contact-us_submit-btn_popup"]`);

        // Submit empty form in contact us popup
        if (scenario.submitContactUsPopupError) {
            await functions.click(page, `[atas="global__contact-us_submit-btn_popup"]`);
        }

        // Submit success contact us popup
        if (scenario.submitContactUsPopupSuccess) {
            await require('../modules/contactUs')(page);
            await page.waitForTimeout(500);
            await functions.click(page, `[atas="global__contact-us_submit-btn_popup"]`);
            await page.waitForNetworkIdle();
            await page.waitForSelector(variables.systMessCardClass, { visible: true });
        }
    }

    // Open share popup
    if (scenario.openSharePopup) {
        await functions.click(page, `[atas="landing__giveaway-share-btn"]`);
        await page.waitForSelector(".bootstrap-dialog");
    }
};
