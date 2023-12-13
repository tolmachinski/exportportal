module.exports = async (page, scenario) => {
    // B2b cards
    await require('../modules/b2b')(page);
    // Sidebar
    await page.waitForFunction(require('../functions/text'), {}, {
        selectorAll: `[atas="global__sidebar-country"], [atas="global__sidebar-industry"], [atas="global__sidebar-title"]`,
        text: `Backstop category with big name for test`
    });
    await page.waitForFunction(require('../functions/counter'), {}, { selectorAll: `[atas="global__sidebar-counter"]`, value: 99999 })
}
