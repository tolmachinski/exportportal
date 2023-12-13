module.exports = async (page, scenario) => {
    if (scenario.howItWork) {
        await page.waitForFunction(howItWorks);
        await page.waitForSelector(".bootstrap-dialog");
    }

    if (scenario.contactUs) {
        await page.waitForFunction(contactUs);
        await page.waitForSelector(".fancybox-overlay");
    }

    // Additional timeout for loading popup
    await page.waitForTimeout(1000);
};

function howItWorks() {
    return (async () => {
        document.querySelector(`[atas="payments__how-it-works"]`).click();

        return true;
    })()
};

function contactUs() {
    return (async () => {
        document.querySelector(`[atas="questions__contact-us"]`).click();

        return true;
    })()
};
