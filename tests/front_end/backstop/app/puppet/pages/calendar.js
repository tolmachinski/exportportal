const click = require("../functions/click");
module.exports = async (page, scenario) => {

    // Open modal event detail
    if(scenario.eventDetail){
        await page.waitForSelector(`[atas="calendar__event_detail"]`);
        await click(page, `[atas="calendar__event_detail"]`);
        await page.waitForNetworkIdle();

        // Open modal delete from calendar
        if(scenario.eventDelete){
            await click(page, `[atas="ep-events-calendar__popup__delete_btn"]`);
            await page.waitForNetworkIdle();
        }
    }

    // Open modal show more events
    if(scenario.showMore){
        await click(page, ".toastui-calendar-grid-cell-more-events");
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
    }
}
