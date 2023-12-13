module.exports = async (page, scenario) => {
    const functions = {
        click: require("../functions/click"),
    };

    if(scenario.openBulk) {
        await functions.click(page, `[atas="page__selling_bulk_upload_btn"]`);
        await page.waitForTimeout(500);
    }
}
