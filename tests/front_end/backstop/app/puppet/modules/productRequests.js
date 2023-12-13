module.exports = async (page, scenario) => {
    const functions = {
        click: require("../functions/click"),
    };

    if (scenario.openRequestProducts) {
        await functions.click(page, `[atas="category__request-products"]`);
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
        await functions.click(page, `[atas="modal__request-product-toggle-additional-info"]`);
        await page.waitForTimeout(1000);
    }
};
