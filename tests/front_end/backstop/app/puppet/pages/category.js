module.exports = async (page, scenario) => {
    const click = require('../functions/click');
    const variables = require('../variables/variables');

    await require('../modules/changeData')(page, [
        {
            selectorAll: `[atas="page__category__header_nav_item-name"]`,
            value: variables.lorem(30),
            isTinymce: true
        },
        {
            selectorAll: `[atas="page__category__header_nav_item-count"]`,
            value: 999
        }
    ]);

    // Products
    await require('../modules/productCard')(page);
    // Open modal
    if(scenario.openRequestProducts){
        await click(page, `[atas="category__request-products"]`);
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
        await click(page, `[atas="modal__request-product-toggle-additional-info"]`);
        await page.waitForTimeout(1000);
    }
    // Change all names in sidebar elements
    await page.waitForFunction(require('../functions/text'), {}, {
        selectorAll:`[atas="global__sidebar-subcategory"], [atas="global__sidebar-country"], [atas="global__sidebar-other-category"]`,
        text: "Backstop text for tests with really big name"
    });
    // Fix counters
    await page.waitForFunction(require('../functions/counter'), {}, {
        selectorAll: `[atas="global__search-counter"], [atas="global__sidebar-counter"]`,
        value: 99999
    });
}
