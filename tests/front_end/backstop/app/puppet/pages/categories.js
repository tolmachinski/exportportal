module.exports = async (page, scenario) => {
    const functions = {
        click: require('../functions/click'),
        counter: require('../functions/counter')
    }
    // Category
    if(scenario.category || scenario.subcategory || scenario.property){
        await functions.click(page, `[atas="categories__select-category"]`);
    }
    // Subcategory
    if(scenario.subcategory || scenario.property){
        await functions.click(page, `[atas="categories__select-subcategory"] a`);
    }
    // Cards
    if(scenario.category || scenario.subcategory){
        await page.waitForTimeout(1000);
    }
    await require('../modules/productCard')(page);
    // Property
    if(scenario.property){
        await functions.click(page, `[atas="categories__select-property"] a`);
    }
    // Counters
    if(scenario.subcategory || scenario.property){
        await page.waitForTimeout(1500);
        await page.waitForFunction(functions.counter, {}, { selectorAll: `[atas="categories__counter"]`, value: 99999 })
    }
}
